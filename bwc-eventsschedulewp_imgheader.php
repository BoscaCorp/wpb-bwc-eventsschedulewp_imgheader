<?php
/*
Plugin Name: BWC Img Header (WPBakery + Events Schedule WP Plugin)
Description: En-tête image (hero) avec CTA dynamique selon l’événement (WPBakery).
Version: 1.1.2
Author: BWC
Author URI: https://github.com/beworldcorp
Text Domain: wpb-bwc-eventsschedulewp_imgheader
Domain Path: /languages
Requires at least: 6.0
Requires PHP: 8.0
GitHub Plugin URI: beworldcorp/wpb-bwc-eventsschedulewp_imgheader
Primary Branch: main
*/

if (!defined('ABSPATH')) exit;

define('BWC_SIH_VERSION', '1.1.2');
define('BWC_SIH_FILE', __FILE__);
define('BWC_SIH_PATH', plugin_dir_path(__FILE__));
define('BWC_SIH_URL', plugin_dir_url(__FILE__));

require_once BWC_SIH_PATH . 'includes/class-bwc-eventsschedulewp_imgheader.php';

add_action('plugins_loaded', function () {
  load_plugin_textdomain('wpb-bwc-eventsschedulewp_imgheader', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

add_action('init', function () {
  new BWC_Eventsschedulewp_imgheader_Plugin();
});
