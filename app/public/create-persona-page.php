<!DOCTYPE html>
<html>
<head><title>Create Persona Page</title></head>
<body>
<h1>Creating Persona Page...</h1>
<?php
require_once(dirname(__FILE__) . '/wp-load.php');

// Check if page exists
$page = get_page_by_path('persona');

if ($page) {
    echo '<p>✓ Page "Persona" already exists with ID: ' . $page->ID . '</p>';
} else {
    // Create the page
    $page_id = wp_insert_post(array(
        'post_title' => 'Persona',
        'post_name' => 'persona',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_content' => '',
        'page_template' => 'page-persona.php'
    ));

    if ($page_id) {
        echo '<p style="color: green;">✓ Page "Persona" created successfully with ID: ' . $page_id . '</p>';
    } else {
        echo '<p style="color: red;">✗ Error creating page</p>';
    }
}

echo '<p><a href="/">Go to Home</a></p>';
?>
</body>
</html>
