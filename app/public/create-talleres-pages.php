<!DOCTYPE html>
<html>
<head><title>Create Talleres Pages</title></head>
<body>
<h1>Creating Talleres Pages...</h1>
<?php
require_once(dirname(__FILE__) . '/wp-load.php');

// 1. Talleres page
$talleres_page = get_page_by_path('talleres');
if ($talleres_page) {
    echo '<p>✓ Page "Talleres" already exists with ID: ' . $talleres_page->ID . '</p>';
} else {
    $talleres_id = wp_insert_post(array(
        'post_title' => 'Talleres',
        'post_name' => 'talleres',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_content' => '',
        'page_template' => 'page-talleres.php'
    ));

    if ($talleres_id) {
        echo '<p style="color: green;">✓ Page "Talleres" created successfully with ID: ' . $talleres_id . '</p>';
    } else {
        echo '<p style="color: red;">✗ Error creating Talleres page</p>';
    }
}

// 2. Nuevo Taller page
$nuevo_taller_page = get_page_by_path('nuevo-taller');
if ($nuevo_taller_page) {
    echo '<p>✓ Page "Nuevo Taller" already exists with ID: ' . $nuevo_taller_page->ID . '</p>';
} else {
    $nuevo_taller_id = wp_insert_post(array(
        'post_title' => 'Nuevo Taller',
        'post_name' => 'nuevo-taller',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_content' => '',
        'page_template' => 'page-nuevo-taller.php'
    ));

    if ($nuevo_taller_id) {
        echo '<p style="color: green;">✓ Page "Nuevo Taller" created successfully with ID: ' . $nuevo_taller_id . '</p>';
    } else {
        echo '<p style="color: red;">✗ Error creating Nuevo Taller page</p>';
    }
}

echo '<p><a href="/">Go to Home</a></p>';
?>
</body>
</html>
