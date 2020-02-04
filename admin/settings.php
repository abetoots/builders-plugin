<?php

namespace Builders_Plugin\Admin\Settings;

if (!defined('ABSPATH')) exit;

/**
 * Add pages to admin dashboard
 * @uses add_submenu_page
 */
add_action('admin_menu', __NAMESPACE__ . '\add_admin_menu_pages');
function add_admin_menu_pages()
{
    add_submenu_page(
        'options-general.php',
        'Builders Plugin Settings',
        'Builders Plugin Settings  <span class="dashicons dashicons-admin-generic"></span>',
        is_admin(),
        'builders_plugin_do_settings_section',
        __NAMESPACE__ . '\generate_page'
    );

    //Activate custom settings
    add_action('admin_init', __NAMESPACE__ . '\init_custom_settings');
}

/**
 * Register custom settings in the DB
 * @uses register_setting()
 */
function init_custom_settings()
{
    //Used with do_settings_section( the page where our settings reside )
    add_settings_section(
        'builders-plugin-features-section',
        'Toggle Features',
        __NAMESPACE__ . '\render_features_section',
        'builders_plugin_do_settings_section'
    );
    add_settings_section(
        'builders-plugin-recaptcha-section',
        'reCAPTCHA Settings ',
        __NAMESPACE__ . '\render_recaptcha_section',
        'builders_plugin_do_settings_section'
    );

    //Toggle if new members can register, bypasses users_can_register by WordPress core
    add_settings_field(
        'builders-plugin-allow-portal-registration',
        'New users can register?',
        __NAMESPACE__ . '\render_registration_toggle',
        'builders_plugin_do_settings_section',
        'builders-plugin-features-section'
    );

    //Recaptcha Fields
    add_settings_field(
        'builders-plugin-recaptcha-site-key',
        'reCAPTCHA site key ',
        __NAMESPACE__ . '\render_recaptcha_site_key',
        'builders_plugin_do_settings_section',
        'builders-plugin-recaptcha-section'
    );
    add_settings_field(
        'builders-plugin-recaptcha-secret-key',
        'reCAPTCHA secret key ',
        __NAMESPACE__ . '\render_recaptcha_secret_key',
        'builders_plugin_do_settings_section',
        'builders-plugin-recaptcha-section'
    );

    //Features
    register_setting('builders-plugin-settings', 'allow_portal_registration');

    //reCaptcha
    register_setting('builders-plugin-settings', 'builders_plugin_recaptcha_site_key');
    register_setting('builders-plugin-settings', 'builders_plugin_recaptcha_secret_key');
}
//Features Section
function render_features_section()
{
    echo '<h4><span role="img" aria-label="prevent-spam">⚙️</span> Enable/disable features</h4>';
}

//Toggle Allow Registration
function render_registration_toggle()
{
    $canRegister =  esc_attr(get_option('allow_portal_registration'));
?>
    <label for="allow-portal-registration" class="switch">
        <input type="checkbox" id="allow-portal-registration" class="switch__input" name="allow_portal_registration" value="1" <?php checked($canRegister, 1); ?>>
        <span class="switch__slider"></span>
    </label>
<?php
}

//Recaptcha Section
function render_recaptcha_section()
{
    echo '<h4 style="font-style: italic;"><span role="img" aria-label="prevent-spam">🛑</span> Prevent spam registration</h4>';
}

function render_recaptcha_site_key()
{
    $siteKey = esc_attr(get_option('builders_plugin_recaptcha_site_key'));
    echo '<input type="text" name="builders_plugin_recaptcha_site_key" value="' . $siteKey . '">';
}

function render_recaptcha_secret_key()
{
    $secretKey = esc_attr(get_option('builders_plugin_recaptcha_secret_key'));
    echo '<input type="text" name="builders_plugin_recaptcha_secret_key" value="' . $secretKey . '">';
}

/**
 * Generate the page
 */
function generate_page()
{
    require_once BUILDERS_PLUGIN_DIR . 'admin/admin-pages.php';
}
