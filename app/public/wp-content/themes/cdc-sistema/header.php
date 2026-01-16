<?php
/**
 * Header Template
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current persona data
$persona = cdc_get_current_persona();
$user_name = $persona ? $persona->nombre . ' ' . $persona->apellido : 'Usuario';

// Determine role based on persona type
$user_role = 'Usuario';
if ($persona) {
    switch ($persona->tipo) {
        case 'socio':
            $user_role = 'Socio';
            break;
        case 'cliente':
            $user_role = 'Cliente';
            break;
        case 'ambos':
            $user_role = 'Socio/Cliente';
            break;
    }
}
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
                <a href="<?php echo esc_url(wp_logout_url(home_url('/login'))); ?>" class="cdc-logout">
                    <span class="dashicons dashicons-exit"></span> Cerrar sesión
                </a>
            </div>
        </header>

        <main class="cdc-content">
