<?php
/**
 * PROJECT_DESCRIPTION
 *
 * @package WP_MinCat
 * @author Michael Novotny <manovotny@gmail.com>
 * @license GPL-3.0+
 * @link https://github.com/manovotny/wp-mincat
 * @copyright 2014 Michael Novotny
 *
 * @wordpress-plugin
 * Plugin Name: WP MinCat
 * Plugin URI: https://github.com/manovotny/wp-mincat
 * Description: PROJECT_DESCRIPTION
 * Version: 0.0.0
 * Author: Michael Novotny
 * Author URI: http://manovotny.com
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /lang
 * Text Domain: wp-mincat
 * GitHub Plugin URI: https://github.com/manovotny/wp-mincat
 */

/* Access
---------------------------------------------------------------------------------- */

if ( ! defined( 'WPINC' ) ) {

    die;

}

/* Classes
---------------------------------------------------------------------------------- */

if ( ! class_exists( 'WP_MinCat' ) ) {

    require_once __DIR__ . '/classes/class-wp-mincat.php';

}

if ( ! class_exists( 'WP_MinCat_Scripts' ) ) {

    require_once __DIR__ . '/classes/class-wp-mincat-scripts.php';

    WP_MinCat_Scripts::get_instance();

}

if ( ! class_exists( 'WP_MinCat_Styles' ) ) {

    require_once __DIR__ . '/classes/class-wp-mincat-styles.php';

    WP_MinCat_Styles::get_instance();

}