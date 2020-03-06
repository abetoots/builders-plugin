<?php

namespace Builders_Plugin;

use WP_Query;

/**
 * Plugin Name: Builders Plugin
 * Plugin URI:  https://example.com/plugins/the-basics/
 * Description: Plugin necessary for Builder's membership portal.
 * Version:     1.0.3
 * Author:      Abe Suni M. Caymo
 * Author URI:  https://abecaymo.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: builders-plugin
 * Domain Path: /languages
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly

define('BUILDERS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BUILDERS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BUILDERS_PLUGIN_APP_MANIFEST', plugin_dir_url(__FILE__) . '/build/asset-manifest.json');

define('BUILDERS_ACF_DIR', plugin_dir_path(__FILE__) . '/libraries/advanced-custom-fields-pro/');
define('BUILDERS_ACF_URL', plugin_dir_path(__FILE__) . 'libraries/advanced-custom-fields-pro/');

final class Builders_Plugin
{

    /**
     * Plugin Version
     *
     * @since 1.0.0
     * @var string The plugin version.
     */
    const VERSION = '1.0.0';

    /**
     * Minimum PHP Version
     *
     * @since 1.0.0
     * @var string Minimum PHP version required to run the plugin.
     */
    const MINIMUM_PHP_VERSION = '7.0';

    /**
     * Minimum ACF Version
     *
     * @since 1.0.0
     * @var string Minimum ACF version required to run the plugin.
     */
    const MINIMUM_ACF_VERSION = '5.2.8';

    /**
     * Minimum WPGraphQl Version
     *
     * @since 1.0.3
     * @var string Minimum ACF version required to run the plugin.
     */
    const MINIMUM_WPGRAPHQL_VERSION = '0.7.0';

    /**
     * Minimum WPGraphQl Version
     *
     * @since 1.0.3
     * @var string Minimum ACF version required to run the plugin.
     */
    const MINIMUM_WPGRAPHIQL_VERSION = '1.0.1';

    /**
     * Constructor
     *
     * @since 1.0.0
     * @access public
     */
    public function __construct()
    {
        // Load translation
        add_action('init', array($this, 'i18n'));

        // Init Plugin
        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * Load Textdomain
     *
     * Load plugin localization files.
     * Fired by `init` action hook.
     *
     * @since 1.0.0
     * @access public
     */
    public function i18n()
    {
        load_plugin_textdomain('builders-plugin');
    }

    /**
     * Initialize the plugin
     *
     * Validates that Elementor is already loaded.
     * Checks for basic plugin requirements, if one check fails, don't continue, otherwise
     * if all checks have passed, include the plugin class.
     *
     * Fired by `plugins_loaded` action hook.
     *
     * @since 1.0.0
     * @access public
     */
    public function init()
    {
        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', array($this, 'admin_notice_minimum_php_version'));
            return;
        }

        //Check if WPGraphQl is installed and activated
        if (!in_array('wp-graphql-' . self::MINIMUM_WPGRAPHQL_VERSION . '/wp-graphql.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            add_action('admin_notices', array($this, 'admin_notice_required_wp_graphql'));
            return;
        }

        //Check if WPGrapihQl is installed and activated
        if (!in_array('wp-graphiql-' . self::MINIMUM_WPGRAPHQL_VERSION . '/wp-graphiql.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            add_action('admin_notices', array($this, 'admin_notice_required_wp_grapihql'));
            return;
        }

        //Check if WPGrapihQl is installed and activated
        if (!in_array('wp-graphql-jwt-authentication/wp-graphql-jwt-authentication.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            add_action('admin_notices', array($this, 'admin_notice_required_wp_graphql_jwt_authentication'));
            return;
        }

        // Once we get here, We have passed all validation checks so we can safely include our plugin
        require_once('plugin.php');
    }

    /**
     * Admin notice - PHP Version
     *
     * Warning when the site doesn't have a minimum required PHP version.
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_minimum_php_version()
    {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'builders-plugin'),
            '<strong>' . esc_html__('Builders Plugin', 'builders-plugin') . '</strong>',
            '<strong>' . esc_html__('PHP', 'builders-plugin') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice - WPGraphQL
     *
     * Warning when the site doesn't have WPGraphQL installed and activated
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_required_wp_graphql()
    {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: WPGraphQL PHP 3: WPGraphQL url */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater. "%4$s"', 'builders-plugin'),
            '<strong>' . esc_html__('Builders Plugin', 'builders-plugin') . '</strong>',
            '<strong>' . esc_html__('WPGraphQL', 'builders-plugin') . '</strong>',
            self::MINIMUM_WPGRAPHQL_VERSION,
            '<a href="https://github.com/wp-graphql/wp-graphql/releases">Go to plugin</a>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice - WPGrapihQL
     *
     * Warning when the site doesn't have WPGraphiQL installed and activated
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_required_wp_graphiql()
    {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater. "%4$s"', 'builders-plugin'),
            '<strong>' . esc_html__('Builders Plugin', 'builders-plugin') . '</strong>',
            '<strong>' . esc_html__('   WPGraphiQL', 'builders-plugin') . '</strong>',
            self::MINIMUM_WPGRAPHIQL_VERSION,
            '<a href="https://github.com/wp-graphql/wp-graphiql">Go to plugin</a>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice - WPGraphQL JWT Authentication
     *
     * Warning when the site doesn't have WPGraphQL JWT Authentication installed and activated
     *
     * @since 1.0.3
     * @access public
     */
    public function admin_notice_required_wp_graphql_jwt_authentication()
    {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__('"%1$s" requires "%2$s" to be installed and activated. "%3$s"', 'builders-plugin'),
            '<strong>' . esc_html__('Builders Plugin', 'builders-plugin') . '</strong>',
            '<strong>' . esc_html__('   WPGraphiQL', 'builders-plugin') . '</strong>',
            '<a href="https://github.com/wp-graphql/wp-graphql-jwt-authentication">Go to plugin</a>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
}
// Instantiate Builders Plugin
new Builders_Plugin();

function rewrite_flush_on_activation()
{

    // Information needed for creating the plugin's pages
    $page_definitions = array(
        'dashboard'      => array(
            'title'     => __('Dashboard', 'builders-plugin'),
            'content'   => '',
            'template'  => 'react-app.php'
        ),
        'sign-in' => array(
            'title' => __('Sign In', 'builders-plugin'),
            'content'   => '',
            'template'  => ''
        ),
        'registration'  => array(
            'title' => __('Register', 'builders-plugin'),
            'content'   => '',
            'template'  => ''
        )
    );

    insert_pages($page_definitions);
    flush_rewrite_rules();
}

/**
 *
 * Creates the pages defined passed in as an array
 * 
 * @param array $page_definitions The array to loop over
 * 
 * @since 1.0.0
 * @access private
 * 
 * @uses wp_insert_post();
 * @uses update_post_meta();
 */

function insert_pages($page_definitions)
{

    if (!is_array($page_definitions)) {
        return;
    }

    foreach ($page_definitions as $slug => $page) {
        // Check that the page doesn't exist already
        $query = new WP_Query('pagename=' . $slug);
        $template = $page['template'] ? $page['template'] : '';
        //Assign a page template, defaults to empty if no page_template is set above
        if (!$query->have_posts()) {
            // Add the page using the data from the array above
            $id = wp_insert_post(
                array(
                    'post_content'   => $page['content'],
                    'post_name'      => $slug,
                    'post_title'     => wp_strip_all_tags($page['title']),
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'ping_status'    => 'closed',
                    'comment_status' => 'closed',
                    'meta_input'     => array(
                        'inserted'   => 'builders-plugin' //used for adding post states in utilities.php
                    )
                )
            );

            //Specify which pages are react apps
            if ($slug === 'dashboard') {
                update_option('react-dashboard', $id);
            }

            // For some reason, post_template is not working. We update it manually.
            if ($template !== '') {
                update_post_meta($id, '_wp_page_template', $template);
            }

            //Handle inserting of child page if specified
            if (array_key_exists('child', $page)) {
                $childId = wp_insert_post(
                    array(
                        'post_content'   => $page['child']['content'],
                        'post_name'      => $page['child']['slug'],
                        'post_title'     => wp_strip_all_tags($page['child']['title']),
                        'post_status'    => 'publish',
                        'post_type'      => 'page',
                        'post_parent'    => $id,
                        'ping_status'    => 'closed',
                        'comment_status' => 'closed',
                        'meta_input'     => array(
                            'inserted'   => 'builders-plugin' // used for adding post states in utilities.php
                        )
                    )
                );
                if ($page['child']['template'] !== '') {
                    update_post_meta($childId, '_wp_page_template', $page['child']['template']);
                }
            }
        }
    }
}


register_deactivation_hook(__FILE__, __NAMESPACE__ . '\on_deactivation');
function on_deactivation()
{
    //Remove custom roles
    if (get_role('gym_member')) {
        remove_role('gym_member');
    }
    if (get_role('gym_trainer')) {
        remove_role('gym_trainer');
    }
    if (get_role('gym_admin')) {
        remove_role('gym_admin');
    }
};
