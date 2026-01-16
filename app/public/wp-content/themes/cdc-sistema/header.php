<?php
/**
 * Header Template
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

// Phase 1: No login/auth - using dummy data
$user_name = cdc_get_current_user_name();
$user_role = cdc_get_user_role_display();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div class="cdc-app">
    <?php get_sidebar(); ?>

    <div class="cdc-main">
        <header class="cdc-header">
            <div class="cdc-header-left">
                <h1 class="cdc-page-title"><?php echo esc_html(get_the_title()); ?></h1>
            </div>
            <div class="cdc-header-right">
                <span class="cdc-user-role"><?php echo esc_html($user_role); ?></span>
                <span class="cdc-user-name"><?php echo esc_html($user_name); ?></span>
            </div>
        </header>

        <main class="cdc-content">
