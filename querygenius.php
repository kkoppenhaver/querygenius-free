<?php

/*
 * Plugin Name:       querygenius
 * Plugin URI:        https://querygenius.ai
 * Description:       A WordPress plugin that helps answer questions about your site using AI.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Keanan Koppenhaver
 * Author URI:        https://keanankoppenhaver.com/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       querygenius
 * Domain Path:       /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'QUERYGENIUS_VERSION', '1.0.0' );
define( 'QUERYGENIUS_URL', plugin_dir_url( __FILE__ ) );
define( 'QUERYGENIUS_PATH', plugin_dir_path( __FILE__ ) );
define( 'QUERYGENIUS_API_BASE', 'https://querygenius.ai/wp-json/querygenius/v1');

/**
 * Require the classes necessary for the plugin to work.
 */
require plugin_dir_path( __FILE__ ) . 'classes/class-admin.php';
require plugin_dir_path( __FILE__ ) . 'classes/class-rest-api.php';
require plugin_dir_path( __FILE__ ) . 'classes/class-utilities.php';

new Admin();
new Rest_Api();
new Utilities();