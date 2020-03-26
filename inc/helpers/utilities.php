<?php

namespace Builders_Plugin\Inc\Helpers;

use const Builders_Plugin\Constants\PLUGIN_PREFIX;

/**
 * Checks if the user ID exists
 */
function user_id_exists($user_id)
{
    global $wpdb;
    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM $wpdb->users WHERE ID = %d", $user_id));
    return empty($count) || 1 > $count ? false : true;
}

/**
 * Renders the contents of the given template to a string and returns it.
 *
 * @param string $template_name The name of the template to render (without .php)
 * @param array  $attributes    The PHP variables for the template
 *
 * @return string               The contents of the template.
 */
function get_template_html($template_name, $attributes = null)
{
    if (!$attributes) {
        $attributes = array();
    }

    /**
     * Notes:
     * The output buffer collects everything that is printed between 
     * ob_start and ob_end_clean so that it can then be retrieved as a string using ob_get_contents.
     * 
     * Notes: the do actions are called by add action, gives chance to other devs to add further customizations
     */
    ob_start();

    do_action('' . PLUGIN_PREFIX . '_customize_html_template_before_' . $template_name);

    require(BUILDERS_PLUGIN_DIR . 'frontend/html-templates/' . $template_name . '.php');

    do_action('' . PLUGIN_PREFIX . '_customize_html_template_after_' . $template_name);

    $html = ob_get_contents();
    ob_end_clean();

    return $html;
}
