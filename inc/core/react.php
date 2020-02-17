<?php

namespace Builders_Plugin\Inc\Core\React;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hook into an earlier hook to do defensive redirects for users who shouldn't have access
 */
add_action('template_redirect', __NAMESPACE__ . '\defensive_redirects');
function defensive_redirects($query)
{
    $dashboard_page = get_option('react-dashboard');
    if ($dashboard_page && is_page($dashboard_page)) {
        if (!is_user_logged_in()) {
            wp_redirect(home_url());
            exit;
        }

        if (!current_user_can('list_gym_members')) {
            wp_redirect(home_url());
            exit;
        }
    }
}


add_action('wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_react_scripts');
function enqueue_react_scripts()
{

    $dashboard_page = get_option('react-dashboard');
    //only enqueue when using the appropriate dashboard page
    if ($dashboard_page && is_page($dashboard_page)) {
        if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
            //CRA apps
            // wp_enqueue_script('react-main-script1', 'http://localhost:3000/static/js/bundle.js', array(), null, true);
            // wp_enqueue_script('react-main-script3', 'http://localhost:3000/static/js/0.chunk.js', array(), null, true);
            // wp_enqueue_script('react-main-script4', 'http://localhost:3000/static/js/1.chunk.js', array(), null, true);
            // wp_enqueue_script('react-main-script2', 'http://localhost:3000/static/js/main.chunk.js', array(), null, true);
            wp_enqueue_script('react-dev-script', 'http://localhost:8080/builders/main.bundle.js', array(), null, true);
            wp_enqueue_script('react-dev-script0', 'http://localhost:8080/builders/0.bundle.js', array(), null, true);
            wp_enqueue_script('react-dev-script1', 'http://localhost:8080/builders/1.bundle.js', array(), null, true);
            wp_enqueue_script('react-dev-script2', 'http://localhost:8080/builders/2.bundle.js', array(), null, true);
        } else {
            //PRODUCTION
            //parse assets_manifest
            $asset_manifest = json_decode(file_get_contents(BUILDERS_PLUGIN_APP_MANIFEST), true)['files'];

            //enqueue our main css
            if (isset($asset_manifest['main.css'])) {
                wp_enqueue_style('react-main-style', BUILDERS_PLUGIN_URL . 'build/' . $asset_manifest['main.css']);
            }

            //always enqueue our runtime and main js files
            wp_enqueue_script('react-runtime', BUILDERS_PLUGIN_URL . 'build/' . $asset_manifest['runtime-main.js'], array(), null, true);
            wp_enqueue_script('react-main-script', BUILDERS_PLUGIN_URL . 'build/' . $asset_manifest['main.js'], array('react-runtime'), null, true);

            //Localize some vars
            wp_localize_script('react-main-script', 'revoltReact', array(
                'nonce'     => wp_create_nonce('wp_rest'),
            ));

            //enqueue js and css chunks
            foreach ($asset_manifest as $key => $value) {
                //FOR CREATE REACT APPS
                if (preg_match('@static/js/(.*)\.chunk\.js@', $key, $matches)) {
                    if ($matches && is_array($matches) && count($matches) === 2) {
                        $name = "react-" . preg_replace('/[^A-Za-z0-9_]/', '-', $matches[1]);
                        wp_enqueue_script($name, BUILDERS_PLUGIN_URL . 'build/' . $value, array('react-main-script'), null, true);
                    }
                }
                //FOR CREATE REACT APPS
                if (preg_match('@static/css/(.*)\.chunk\.css@', $key, $matches)) {
                    if ($matches && is_array($matches) && count($matches) == 2) {
                        $name = "react-" . preg_replace('/[^A-Za-z0-9_]/', '-', $matches[1]);
                        wp_enqueue_style($name, BUILDERS_PLUGIN_URL . 'build/' . $value, array('react-main-style'), null);
                    }
                }
                //WEBPACK REACT APPS
                if (preg_match('(.*)\.chunk\.js@', $key, $matches)) {
                    if ($matches && is_array($matches) && count($matches) === 2) {
                        $name = "react-" . preg_replace('/[^A-Za-z0-9_]/', '-', $matches[1]);
                        wp_enqueue_script($name, BUILDERS_PLUGIN_URL . 'build/' . $value, array('react-main-script'), null, true);
                    }
                }
                //WEBPACK REACT APPS
                if (preg_match('(.*)\.chunk\.css@', $key, $matches)) {
                    if ($matches && is_array($matches) && count($matches) == 2) {
                        $name = "react-" . preg_replace('/[^A-Za-z0-9_]/', '-', $matches[1]);
                        wp_enqueue_style($name, BUILDERS_PLUGIN_URL . 'build/' . $value, array('react-main-style'), null);
                    }
                }
            }
        }
    } // end check if page dashboard
}
