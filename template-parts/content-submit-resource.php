<?php
/**
 * Template part: content-submit-resource.php
 * Renders the Submit Resource form and handles display of success / error messages.
 */

// Retrieve state passed from the controller.
$errors       = get_query_var( 'submit_errors', [] );
$success      = get_query_var( 'submit_success', false );
$form_data    = get_query_var( 'submit_form_data', [] );

// Ensure defaults for form fields if not set.
$form_data = wp_parse_args( $form_data, [
    'resource_name'        => '',
    'resource_email'       => '',
    'resource_link'        => '',
    'resource_category'    => 0,
    'resource_description' => '',
] );
?>

<main class="main-container">
    <section class="page-section submit-resource-section">
        <h2><?php the_title(); ?></h2>
        <p class="section-desc">
            <?php esc_html_e( 'Have you found or created an interesting resource? Share it with the community. An administrator will review your suggestion before publishing.', 'web-dev-tree' ); ?>
        </p>

        <?php if ( $success ) : ?>
            <div class="submit-success-card">
                <span class="success-icon" aria-hidden="true">✔</span>
                <h3><?php esc_html_e( 'Thank you very much!', 'web-dev-tree' ); ?></h3>
                <p><?php esc_html_e( 'We have successfully received your resource suggestion. We will review it as soon as possible to ensure the quality of the list.', 'web-dev-tree' ); ?></p>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="resource-button">
                    <?php esc_html_e( 'Back to home', 'web-dev-tree' ); ?>
                </a>
            </div>
        <?php else : ?>

            <?php if ( ! empty( $errors ) ) : ?>
                <div class="form-alert form-alert-error" role="alert">
                    <strong><?php esc_html_e( 'Please correct the following errors:', 'web-dev-tree' ); ?></strong>
                    <ul>
                        <?php foreach ( $errors as $error ) : ?>
                            <li><?php echo esc_html( $error ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="resource-form" novalidate>
                <?php wp_nonce_field( 'submit_resource_action', 'resource_nonce' ); ?>
                <input type="hidden" name="form_load_time" value="<?php echo esc_attr( time() ); ?>" />
                <!-- Honeypot (invisible) -->
                <div class="hp-wrapper" aria-hidden="true" style="position:absolute; left:-9999px;">
                    <label for="website_url"><?php esc_html_e( 'Leave blank', 'web-dev-tree' ); ?></label>
                    <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off" />
                </div>

                <div class="form-group">
                    <label for="resource_name"><?php esc_html_e( 'Resource Name', 'web-dev-tree' ); ?></label>
                    <input type="text" id="resource_name" name="resource_name" required minlength="2"
                        value="<?php echo esc_attr( $form_data['resource_name'] ); ?>"
                        placeholder="<?php esc_attr_e( 'e.g. CSS Generators Pro', 'web-dev-tree' ); ?>"
                        aria-describedby="name-hint" />
                    <span id="name-hint" class="form-hint" style="display:none;"><?php esc_html_e( 'Minimum 2 characters.', 'web-dev-tree' ); ?></span>
                    <span class="form-error" id="name-error"><?php esc_html_e( '❌ Name is required (minimum 2 characters).', 'web-dev-tree' ); ?></span>
                </div>

                <div class="form-group">
                    <label for="resource_email"><?php esc_html_e( 'Your Email Address', 'web-dev-tree' ); ?></label>
                    <input type="email" id="resource_email" name="resource_email" required
                        value="<?php echo esc_attr( $form_data['resource_email'] ); ?>"
                        placeholder="<?php esc_attr_e( 'e.g. you@example.com', 'web-dev-tree' ); ?>"
                        autocomplete="email" />
                    <span class="form-error" id="email-error"><?php esc_html_e( '❌ Please enter a valid email address.', 'web-dev-tree' ); ?></span>
                </div>

                <div class="form-group">
                    <label for="resource_link"><?php esc_html_e( 'Resource Link (URL)', 'web-dev-tree' ); ?></label>
                    <input type="url" id="resource_link" name="resource_link" required
                        value="<?php echo esc_url( $form_data['resource_link'] ); ?>"
                        placeholder="<?php esc_attr_e( 'e.g. https://example.com', 'web-dev-tree' ); ?>" />
                    <span class="form-error" id="link-error"><?php esc_html_e( '❌ Please enter a valid URL.', 'web-dev-tree' ); ?></span>
                </div>

                <div class="form-group">
                    <label for="resource_category"><?php esc_html_e( 'Category', 'web-dev-tree' ); ?></label>
                    <?php
                    $categories = get_terms( [
                        'taxonomy'   => 'category_resource',
                        'hide_empty' => false,
                    ] );
                    ?>
                    <select id="resource_category" name="resource_category" required>
                        <option value="" disabled <?php selected( $form_data['resource_category'], 0 ); ?>>
                            <?php esc_html_e( 'Select a category...', 'web-dev-tree' ); ?>
                        </option>
                        <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                            <?php foreach ( $categories as $cat ) : ?>
                                <option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( $form_data['resource_category'], $cat->term_id ); ?> >
                                    <?php echo esc_html( $cat->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <span class="form-error" id="category-error"><?php esc_html_e( '❌ Please select a category for the resource.', 'web-dev-tree' ); ?></span>
                </div>

                <div class="form-group">
                    <label for="resource_description"><?php esc_html_e( 'Short Description', 'web-dev-tree' ); ?></label>
                    <textarea id="resource_description" name="resource_description" required minlength="15" maxlength="300"
                        placeholder="<?php esc_attr_e( 'Briefly describe what this resource is about (between 15 and 300 characters)...', 'web-dev-tree' ); ?>"><?php echo esc_textarea( $form_data['resource_description'] ); ?></textarea>
                    <span class="form-error" id="desc-error"><?php esc_html_e( '❌ Description is required (between 15 and 300 characters).', 'web-dev-tree' ); ?></span>
                </div>

                <button type="submit" name="submit_resource" class="resource-button" style="align-self:center;margin-top:1rem;border:none;cursor:pointer;">
                    <?php esc_html_e( 'Submit Suggestion', 'web-dev-tree' ); ?>
                </button>
            </form>
        <?php endif; ?>
    </section>
</main>

<script>
// Sync ARIA invalid attribute with :user-invalid pseudo‑class (modern guidance)
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
