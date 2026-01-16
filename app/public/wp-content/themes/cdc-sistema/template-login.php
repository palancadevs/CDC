<?php
/**
 * Template Name: Login CDC
 *
 * Página de login del sistema CDC
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

// Si ya está logueado, redirigir a dashboard
if (cdc_is_user_logged_in()) {
    wp_redirect(home_url('/dashboard'));
    exit;
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cdc_login_nonce'])) {
    if (wp_verify_nonce($_POST['cdc_login_nonce'], 'cdc_login')) {
        $dni = sanitize_text_field($_POST['dni']);
        $password = $_POST['password'];

        $result = cdc_authenticate_user($dni, $password);

        if (is_wp_error($result)) {
            $login_error = $result->get_error_message();
        } else {
            // Login exitoso
            wp_redirect(home_url('/dashboard'));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php _e('Iniciar Sesión - CDC Sistema', 'cdc-sistema'); ?></title>
    <?php wp_head(); ?>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            color: #333;
        }
        .login-header p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-login:hover {
            background: #5568d3;
        }
        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .help-text {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><?php _e('Casa de la Cultura', 'cdc-sistema'); ?></h1>
            <p><?php _e('Sistema de Administración', 'cdc-sistema'); ?></p>
        </div>

        <?php if (isset($login_error)): ?>
            <div class="error-message">
                <?php echo esc_html($login_error); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field('cdc_login', 'cdc_login_nonce'); ?>

            <div class="form-group">
                <label for="dni"><?php _e('DNI', 'cdc-sistema'); ?></label>
                <input
                    type="text"
                    id="dni"
                    name="dni"
                    placeholder="12345678"
                    required
                    autofocus
                    value="<?php echo isset($_POST['dni']) ? esc_attr($_POST['dni']) : ''; ?>"
                >
            </div>

            <div class="form-group">
                <label for="password"><?php _e('Contraseña', 'cdc-sistema'); ?></label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    required
                >
            </div>

            <button type="submit" class="btn-login">
                <?php _e('Iniciar Sesión', 'cdc-sistema'); ?>
            </button>
        </form>

        <div class="help-text">
            <?php _e('Acceso exclusivo para personal autorizado', 'cdc-sistema'); ?>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
