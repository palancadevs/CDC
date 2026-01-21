<?php
/**
 * Create Editar Persona page
 */

require_once __DIR__ . '/wp-load.php';

// Check if page already exists
$existing_page = get_page_by_path('editar-persona');

if ($existing_page) {
    echo "Page 'Editar Persona' already exists with ID: " . $existing_page->ID . "\n";
    exit;
}

// Create the page
$page_data = array(
    'post_title'    => 'Editar Persona',
    'post_name'     => 'editar-persona',
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'post_content'  => '',
    'page_template' => 'page-editar-persona.php'
);

$page_id = wp_insert_post($page_data);

if ($page_id && !is_wp_error($page_id)) {
    echo "✓ Page 'Editar Persona' created successfully with ID: $page_id\n";
    echo "  URL: " . get_permalink($page_id) . "\n";
} else {
    echo "✗ Error creating page 'Editar Persona'\n";
    if (is_wp_error($page_id)) {
        echo "  Error: " . $page_id->get_error_message() . "\n";
    }
}
