<?php
/*
Plugin Name: BWC Simple Img Header (WPBakery)
Description: Image hero + titres + CTA événement (sélection dynamique via autocomplete sur le CPT "class").
Version: 1.1.0
Author: BWC
Author URI: https://github.com/beworldcorp
Text Domain: wpb-bwc-eventsschedulewp_imgheader
Domain Path: /languages
Requires at least: 6.0
Requires PHP: 8.0
GitHub Plugin URI: beworldcorp/wpb-bwc-eventsschedulewp-imgheader
Primary Branch: main
*/

if (!defined('ABSPATH')) exit;

define('BWC_ESH_VERSION', '1.1.0');
define('BWC_ESH_FILE', __FILE__);
define('BWC_ESH_PATH', plugin_dir_path(__FILE__));
define('BWC_ESH_URL', plugin_dir_url(__FILE__));

require_once BWC_ESH_PATH . 'includes/class-bwc-eventsschedulewp-imgheader.php';

add_action('plugins_loaded', function () {
  load_plugin_textdomain('wpb-bwc-eventsschedulewp_imgheader', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

add_action('init', function () {
  new BWC_Eventsschedulewp_ImgHeader_Plugin();
});
