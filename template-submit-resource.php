<?php
/**
 * Template Name: Submit Resource
 * Description: Page template controller for user-submitted resources.
 *
 * Handles all form processing logic (security, validation, DB insertion)
 * and passes data to the template part for rendering.
 */

$errors    = [];
$success   = false;
$form_data = [
    'resource_name'        => '',
    'your_name'            => '',
    'resource_email'       => '',
    'resource_link'        => '',
    'resource_category'    => 0,
    'resource_description' => '',
];

// Handle form submission
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['submit_resource'] ) ) {

    // 1. CSRF Nonce Verification
    if ( ! isset( $_POST['resource_nonce'] ) || ! wp_verify_nonce( $_POST['resource_nonce'], 'submit_resource_action' ) ) {
        $errors['security'] = __( 'Unauthorized access or session expired. Please reload and try again.', 'web-dev-tree' );
    }

    // 2. Honeypot — if filled, it's a bot
    if ( ! empty( $_POST['website_url'] ) ) {
        $errors['bot'] = __( 'Spam detected. Submission cancelled.', 'web-dev-tree' );
    }

    // 3. Time-lock — reject submissions faster than 3 seconds
    $form_load_time = isset( $_POST['form_load_time'] ) ? intval( $_POST['form_load_time'] ) : 0;
    if ( time() - $form_load_time < 3 ) {
        $errors['speed'] = __( 'You submitted the form too quickly. Are you a robot?', 'web-dev-tree' );
    }

    // 4. Sanitize inputs
    $form_data['resource_name']        = sanitize_text_field( $_POST['resource_name'] ?? '' );
    $form_data['your_name']            = sanitize_text_field( $_POST['your_name'] ?? '' );
    $form_data['resource_email']       = sanitize_email( $_POST['resource_email'] ?? '' );
    $form_data['resource_link']        = esc_url_raw( $_POST['resource_link'] ?? '' );
    $form_data['resource_category']    = intval( $_POST['resource_category'] ?? 0 );
    $form_data['resource_description'] = sanitize_textarea_field( $_POST['resource_description'] ?? '' );

    // 5. Validate fields
    if ( empty( $form_data['resource_name'] ) ) {
        $errors['name'] = __( 'The resource name is required.', 'web-dev-tree' );
    } elseif ( strlen( $form_data['resource_name'] ) < 2 ) {
        $errors['name'] = __( 'The name must be at least 2 characters long.', 'web-dev-tree' );
    }

    if ( empty( $form_data['your_name'] ) ) {
        $errors['your_name'] = __( 'Your name is required.', 'web-dev-tree' );
    } elseif ( strlen( $form_data['your_name'] ) < 2 ) {
        $errors['your_name'] = __( 'Your name must be at least 2 characters long.', 'web-dev-tree' );
    }

    if ( empty( $form_data['resource_email'] ) ) {
        $errors['email'] = __( 'The email address is required.', 'web-dev-tree' );
    } elseif ( ! is_email( $form_data['resource_email'] ) ) {
        $errors['email'] = __( 'Please enter a valid email address.', 'web-dev-tree' );
    }

    if ( empty( $form_data['resource_link'] ) ) {
        $errors['link'] = __( 'The resource link is required.', 'web-dev-tree' );
    } elseif ( ! filter_var( $form_data['resource_link'], FILTER_VALIDATE_URL ) ) {
        $errors['link'] = __( 'Please enter a valid URL (e.g. https://example.com).', 'web-dev-tree' );
    }

    if ( empty( $form_data['resource_category'] ) ) {
        $errors['category'] = __( 'You must select a category.', 'web-dev-tree' );
    } else {
        $term = get_term( $form_data['resource_category'], 'category_resource' );
        if ( is_wp_error( $term ) || ! $term ) {
            $errors['category'] = __( 'The selected category is invalid.', 'web-dev-tree' );
        }
    }

    if ( empty( $form_data['resource_description'] ) ) {
        $errors['description'] = __( 'The description is required.', 'web-dev-tree' );
    } elseif ( strlen( $form_data['resource_description'] ) < 15 ) {
        $errors['description'] = __( 'The description must be at least 15 characters long.', 'web-dev-tree' );
    } elseif ( strlen( $form_data['resource_description'] ) > 300 ) {
        $errors['description'] = __( 'The description cannot exceed 300 characters.', 'web-dev-tree' );
    }

    // 6. Insert post if no errors
    if ( empty( $errors ) ) {
        $post_id = wp_insert_post( [
            'post_title'   => $form_data['resource_name'],
            'post_content' => $form_data['resource_description'],
            'post_excerpt' => $form_data['resource_description'],
            'post_status'  => 'pending',
            'post_type'    => 'resource',
            'meta_input'   => [
                '_external_link'   => $form_data['resource_link'],
                '_submitter_email' => $form_data['resource_email'],
                '_submitter_name'  => $form_data['your_name'],
            ],
        ] );

        if ( is_wp_error( $post_id ) ) {
            $errors['system'] = __( 'System error while submitting resource: ', 'web-dev-tree' ) . $post_id->get_error_message();
        } else {
            wp_set_object_terms( $post_id, $form_data['resource_category'], 'category_resource' );
            $success   = true;
            $form_data = array_fill_keys( array_keys( $form_data ), '' );
            $form_data['resource_category'] = 0;
        }
    }
}

get_header();

// Pass state to the template part via set_query_var so no globals are needed
set_query_var( 'submit_errors',    $errors );
set_query_var( 'submit_success',   $success );
set_query_var( 'submit_form_data', $form_data );

get_template_part( 'template-parts/content', 'submit-resource' );

get_footer();
