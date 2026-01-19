<?php
/**
 * Template Name: Login
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

// If already logged in, redirect to dashboard
if (is_user_logged_in()) {
    wp_redirect(home_url('/'));
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .cdc-login-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
            padding: 40px;
        }

        .cdc-login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .cdc-login-header h1 {
            color: #2c3338;
            font-size: 28px;
            margin: 0 0 10px 0;
        }

        .cdc-login-header p {
            color: #646970;
            font-size: 14px;
            margin: 0;
        }

        .cdc-login-form {
            margin-top: 30px;
        }

        .cdc-form-group {
            margin-bottom: 20px;
        }

        .cdc-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3338;
        }

        .cdc-form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #dcdcde;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.2s;
        }

        .cdc-form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .cdc-login-button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .cdc-login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .cdc-login-button:active {
            transform: translateY(0);
        }

        .cdc-login-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .cdc-login-error {
            background: #fee;
            border-left: 4px solid #d63638;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #721c24;
            display: none;
        }

        .cdc-login-loading {
            text-align: center;
            color: #646970;
            display: none;
        }

        .cdc-login-loading .spinner {
            border: 3px solid #f0f0f1;
            border-top-color: #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            margin: 0 auto 10px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .cdc-login-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 13px;
            color: #646970;
        }
    </style>
</head>
<body class="cdc-login-page">
    <div class="cdc-login-container">
        <div class="cdc-login-header">
            <h1>Casa de la Cultura</h1>
            <p>Sistema de Administración</p>
        </div>

        <div class="cdc-login-error" id="cdc-login-error"></div>
        <div class="cdc-login-loading" id="cdc-login-loading">
            <div class="spinner"></div>
            <p>Verificando credenciales...</p>
        </div>

        <form id="cdc-login-form" class="cdc-login-form">
            <div class="cdc-form-group">
                <label for="cdc-dni">DNI</label>
                <input
                    type="text"
                    id="cdc-dni"
                    name="dni"
                    placeholder="Ingrese su DNI (7-8 dígitos)"
                    maxlength="8"
                    pattern="\d{7,8}"
                    required
                    autofocus
                >
            </div>

            <button type="submit" class="cdc-login-button" id="cdc-login-submit">
                Ingresar
            </button>
        </form>

        <div class="cdc-login-footer">
            <p>Ingrese con su DNI para acceder al sistema</p>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
