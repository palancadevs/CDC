<?php
/**
 * Create Salas pages
 */

require_once __DIR__ . '/wp-load.php';

$pages = array(
    array(
        'title' => 'Salas',
        'slug' => 'salas',
        'template' => 'page-salas.php'
    ),
    array(
        'title' => 'Nueva Sala',
        'slug' => 'nueva-sala',
        'template' => 'page-nueva-sala.php'
    ),
    array(
        'title' => 'Sala',
        'slug' => 'sala',
        'template' => 'page-sala.php'
    )
);

foreach ($pages as $page_data) {
    $existing_page = get_page_by_path($page_data['slug']);

    if ($existing_page) {
        echo "✓ Page '{$page_data['title']}' already exists with ID: " . $existing_page->ID . "\n";
        continue;
    }

    $page_id = wp_insert_post(array(
        'post_title'    => $page_data['title'],
        'post_name'     => $page_data['slug'],
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_content'  => '',
        'page_template' => $page_data['template']
    ));

    if ($page_id && !is_wp_error($page_id)) {
        echo "✓ Page '{$page_data['title']}' created successfully with ID: $page_id\n";
        echo "  URL: " . get_permalink($page_id) . "\n";
    } else {
        echo "✗ Error creating page '{$page_data['title']}'\n";
        if (is_wp_error($page_id)) {
            echo "  Error: " . $page_id->get_error_message() . "\n";
        }
    }
}
