<?php
/**
 * Template Name: Submit Resource
 * Description: Template for user-submitted resources.
 */

$errors = [];
$success = false;

// Default values to repopulate form fields in case of errors
$form_data = [
    'resource_name'        => '',
    'resource_email'       => '',
    'resource_link'        => '',
    'resource_category'    => 0,
    'resource_description' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_resource'])) {
    
    // 1. CSRF Nonce Verification (Security check)
    if (!isset($_POST['resource_nonce']) || !wp_verify_nonce($_POST['resource_nonce'], 'submit_resource_action')) {
        $errors['security'] = __('Unauthorized access or session expired. Please reload and try again.', 'web-dev-tree');
    }

    // 2. Honeypot Spam Protection (If field is filled, it's a bot)
    if (!empty($_POST['website_url'])) {
        $errors['bot'] = __('Spam detected. Submission cancelled.', 'web-dev-tree');
    }

    // 3. Time-lock Spam Protection (Bots fill forms instantly. Rejects submissions faster than 3 seconds)
    $form_load_time = isset($_POST['form_load_time']) ? intval($_POST['form_load_time']) : 0;
    if (time() - $form_load_time < 3) {
        $errors['speed'] = __('You submitted the form too quickly. Are you a robot?', 'web-dev-tree');
    }

    // 4. Sanitize Input Data
    $form_data['resource_name']        = sanitize_text_field($_POST['resource_name'] ?? '');
    $form_data['resource_email']       = sanitize_email($_POST['resource_email'] ?? '');
    $form_data['resource_link']        = esc_url_raw($_POST['resource_link'] ?? '');
    $form_data['resource_category']    = intval($_POST['resource_category'] ?? 0);
    $form_data['resource_description'] = sanitize_textarea_field($_POST['resource_description'] ?? '');

    // 5. Validation Logic
    
    // Name validation
    if (empty($form_data['resource_name'])) {
        $errors['name'] = __('The resource name is required.', 'web-dev-tree');
    } elseif (strlen($form_data['resource_name']) < 2) {
        $errors['name'] = __('The name must be at least 2 characters long.', 'web-dev-tree');
    }

    // Email validation
    if (empty($form_data['resource_email'])) {
        $errors['email'] = __('The email address is required.', 'web-dev-tree');
    } elseif (!is_email($form_data['resource_email'])) {
        $errors['email'] = __('Please enter a valid email address.', 'web-dev-tree');
    }

    // Link/URL validation
    if (empty($form_data['resource_link'])) {
        $errors['link'] = __('The resource link is required.', 'web-dev-tree');
    } elseif (!filter_var($form_data['resource_link'], FILTER_VALIDATE_URL)) {
        $errors['link'] = __('Please enter a valid URL (e.g. https://example.com).', 'web-dev-tree');
    }

    // Category validation
    if (empty($form_data['resource_category'])) {
        $errors['category'] = __('You must select a category.', 'web-dev-tree');
    } else {
        $term = get_term($form_data['resource_category'], 'category_resource');
        if (is_wp_error($term) || !$term) {
            $errors['category'] = __('The selected category is invalid.', 'web-dev-tree');
        }
    }

    // Description validation
    if (empty($form_data['resource_description'])) {
        $errors['description'] = __('The description is required.', 'web-dev-tree');
    } elseif (strlen($form_data['resource_description']) < 15) {
        $errors['description'] = __('The description must be at least 15 characters long.', 'web-dev-tree');
    } elseif (strlen($form_data['resource_description']) > 300) {
        $errors['description'] = __('The description cannot exceed 300 characters.', 'web-dev-tree');
    }

    // 6. Process Submission if no errors
    if (empty($errors)) {
        // Create post structure for Custom Post Type "resource"
        $new_post = [
            'post_title'   => $form_data['resource_name'],
            'post_content' => $form_data['resource_description'],
            'post_excerpt' => $form_data['resource_description'],
            'post_status'  => 'pending', // Send to moderation
            'post_type'    => 'resource',
            'meta_input'   => [
                '_external_link'   => $form_data['resource_link'],
                '_submitter_email' => $form_data['resource_email'],
                '_submitter_name'  => $form_data['resource_name']
            ]
        ];

        $post_id = wp_insert_post($new_post);

        if (is_wp_error($post_id)) {
            $errors['system'] = __('System error while submitting resource: ', 'web-dev-tree') . $post_id->get_error_message();
        } else {
            // Link CPT Post to Selected Taxonomy Term
            wp_set_object_terms($post_id, $form_data['resource_category'], 'category_resource');

            $success = true;
            // Clear form data for a clean visual slate
            $form_data = [
                'resource_name'        => '',
                'resource_email'       => '',
                'resource_link'        => '',
                'resource_category'    => 0,
                'resource_description' => ''
            ];
        }
    }
}

get_header();
?>

<main class="main-container">
    <section class="page-section submit-resource-section">
        
        <h2><?php the_title(); ?></h2>
        <p class="section-desc">
            <?php esc_html_e('Have you found or created an interesting resource? Share it with the community. An administrator will review your suggestion before publishing.', 'web-dev-tree'); ?>
        </p>

        <?php if ($success) : ?>
            <div class="submit-success-card">
                <span class="success-icon" aria-hidden="true">✔</span>
                <h3><?php esc_html_e('Thank you very much!', 'web-dev-tree'); ?></h3>
                <p>
                    <?php esc_html_e('We have successfully received your resource suggestion. We will review it as soon as possible to ensure the quality of the list.', 'web-dev-tree'); ?>
                </p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="resource-button">
                    <?php esc_html_e('Back to home', 'web-dev-tree'); ?>
                </a>
            </div>
        <?php else : ?>

            <?php if (!empty($errors)) : ?>
                <div class="form-alert form-alert-error" role="alert">
                    <strong><?php esc_html_e('Please correct the following errors:', 'web-dev-tree'); ?></strong>
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="resource-form" novalidate>
                
                <!-- WordPress Nonce Security -->
                <?php wp_nonce_field('submit_resource_action', 'resource_nonce'); ?>
                
                <!-- Speed Lock (Security check) -->
                <input type="hidden" name="form_load_time" value="<?php echo esc_attr(time()); ?>" />
                
                <!-- Anti-Spam Honeypot Field (Should be empty, invisible to human users) -->
                <div class="hp-wrapper" aria-hidden="true" style="position: absolute; left: -9999px;">
                    <label for="website_url"><?php esc_html_e('Leave blank', 'web-dev-tree'); ?></label>
                    <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off" />
                </div>

                <!-- Input Name -->
                <div class="form-group">
                    <label for="resource_name"><?php esc_html_e('Resource Name', 'web-dev-tree'); ?></label>
                    <input 
                        type="text" 
                        id="resource_name" 
                        name="resource_name" 
                        required 
                        minlength="2"
                        value="<?php echo esc_attr($form_data['resource_name']); ?>" 
                        placeholder="<?php esc_attr_e('e.g. CSS Generators Pro', 'web-dev-tree'); ?>"
                        aria-describedby="name-hint"
                    />
                    <span id="name-hint" class="form-hint" style="display: none;"><?php esc_html_e('Minimum 2 characters.', 'web-dev-tree'); ?></span>
                    <span class="form-error" id="name-error"><?php esc_html_e('❌ Name is required (minimum 2 characters).', 'web-dev-tree'); ?></span>
                </div>

                <!-- Input Submitter Email -->
                <div class="form-group">
                    <label for="resource_email"><?php esc_html_e('Your Email Address', 'web-dev-tree'); ?></label>
                    <input 
                        type="email" 
                        id="resource_email" 
                        name="resource_email" 
                        required 
                        value="<?php echo esc_attr($form_data['resource_email']); ?>" 
                        placeholder="<?php esc_attr_e('e.g. you@example.com', 'web-dev-tree'); ?>"
                        autocomplete="email"
                    />
                    <span class="form-error" id="email-error"><?php esc_html_e('❌ Please enter a valid email address.', 'web-dev-tree'); ?></span>
                </div>

                <!-- Input URL -->
                <div class="form-group">
                    <label for="resource_link"><?php esc_html_e('Resource Link (URL)', 'web-dev-tree'); ?></label>
                    <input 
                        type="url" 
                        id="resource_link" 
                        name="resource_link" 
                        required 
                        value="<?php echo esc_url($form_data['resource_link']); ?>" 
                        placeholder="<?php esc_attr_e('e.g. https://example.com', 'web-dev-tree'); ?>"
                    />
                    <span class="form-error" id="link-error"><?php esc_html_e('❌ Please enter a valid URL.', 'web-dev-tree'); ?></span>
                </div>

                <!-- Input Category Select -->
                <div class="form-group">
                    <label for="resource_category"><?php esc_html_e('Category', 'web-dev-tree'); ?></label>
                    <?php
                    $categories = get_terms([
                        'taxonomy'   => 'category_resource',
                        'hide_empty' => false,
                    ]);
                    ?>
                    <select id="resource_category" name="resource_category" required>
                        <option value="" disabled <?php selected($form_data['resource_category'], 0); ?>>
                            <?php esc_html_e('Select a category...', 'web-dev-tree'); ?>
                        </option>
                        <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
                            <?php foreach ($categories as $cat) : ?>
                                <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($form_data['resource_category'], $cat->term_id); ?>>
                                    <?php echo esc_html($cat->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <span class="form-error" id="category-error"><?php esc_html_e('❌ Please select a category for the resource.', 'web-dev-tree'); ?></span>
                </div>

                <!-- Input Short Description -->
                <div class="form-group">
                    <label for="resource_description"><?php esc_html_e('Short Description', 'web-dev-tree'); ?></label>
                    <textarea 
                        id="resource_description" 
                        name="resource_description" 
                        required 
                        minlength="15" 
                        maxlength="300"
                        placeholder="<?php esc_attr_e('Briefly describe what this resource is about (between 15 and 300 characters)...', 'web-dev-tree'); ?>"
                    ><?php echo esc_textarea($form_data['resource_description']); ?></textarea>
                    <span class="form-error" id="desc-error"><?php esc_html_e('❌ Description is required (between 15 and 300 characters).', 'web-dev-tree'); ?></span>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="submit_resource" class="resource-button" style="align-self: center; margin-top: 1rem; border: none; cursor: pointer;">
                    <?php esc_html_e('Submit Suggestion', 'web-dev-tree'); ?>
                </button>

            </form>
        <?php endif; ?>

    </section>
</main>

<script>
// Keep ARIA attributes and validity states in sync, as per modern guidance
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('.resource-form input, .resource-form select, .resource-form textarea');
    
    const syncAria = (el) => {
        if (!el.setAttribute) return;
        el.setAttribute('aria-invalid', el.matches(':user-invalid') ? 'true' : 'false');
    };

    inputs.forEach(input => {
        input.addEventListener('blur', () => syncAria(input));
        input.addEventListener('input', () => {
            if (input.hasAttribute('aria-invalid')) syncAria(input);
        });
    });
});
</script>

<?php
get_footer();
