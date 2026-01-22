<?php
/**
 * Create Editar Taller page
 */

require_once __DIR__ . '/wp-load.php';

// Check if page already exists
$existing_page = get_page_by_path('editar-taller');

if ($existing_page) {
    echo "Page 'Editar Taller' already exists with ID: " . $existing_page->ID . "\n";
    exit;
}

// Create the page
$page_data = array(
    'post_title'    => 'Editar Taller',
    'post_name'     => 'editar-taller',
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'post_content'  => '',
    'page_template' => 'page-editar-taller.php'
);

$page_id = wp_insert_post($page_data);

if ($page_id && !is_wp_error($page_id)) {
    echo "✓ Page 'Editar Taller' created successfully with ID: $page_id\n";
    echo "  URL: " . get_permalink($page_id) . "\n";
} else {
    echo "✗ Error creating page 'Editar Taller'\n";
    if (is_wp_error($page_id)) {
        echo "  Error: " . $page_id->get_error_message() . "\n";
    }
}
