<?php

/**
 * @package    WordPress
 * @subpackage WC Poczta - Self Pickup with WooCommerce
 *
 * @copyright  Copyright (c) 2020-2021, Leszek Pomianowski
 * @link       https://lepo.co/
 * @license    GPL-3.0 https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @wc-poczta
 * Plugin Name: Self Pickup with WooCommerce - InPost Paczkomaty, Polish Post, Żabka and Ruch
 * Plugin URI: http://wordpress.org/plugins/wc-poczta/
 * Description: Integration of self pickup in the Inpost Paczkomaty, Polish Post offices, Orlen, Żabka, Ruch stores and others.
 * Author: lepo.co
 * Author URI: https://lepo.co/
 * License: GPL-3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Version: 1.3.4
 * Text Domain: wc_poczta
 * Domain Path: /languages
 * 
 * WC requires at least: 5.0.0
 * WC tested up to: 5.5.1
 */

namespace WCPoczta;

defined('ABSPATH') or die('No script kiddies please!');

if (version_compare(PHP_VERSION, '7.4.1') >= 0) {
  $pluginPath = plugin_dir_path(__FILE__);

  if (defined('WP_DEBUG') && WP_DEBUG && is_file($pluginPath . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    require_once $pluginPath . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
  }

  $corePath = 'code' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR;

  require_once $pluginPath . $corePath . 'Helpers.php';
  require_once $pluginPath . $corePath . 'Bootstrap.php';
  require_once $pluginPath . $corePath . 'Actions.php';
  require_once $pluginPath . $corePath . 'ShippingRegistrar.php';

  \WCPoczta\Code\Core\Bootstrap::init($pluginPath, plugin_dir_url(__FILE__), '1.3.4');
} else {
  add_action('admin_notices', function () {
    echo '<div class="notice notice-error"><p><strong>WC Poczta</strong><br>WC Poczta requires a minimum PHP version of 7.4.1. Your site uses ' . PHP_VERSION . '. You need to update your server if you want to use this plugin.<br/><a target="_blank" rel="noopener" href="https://wordpress.org/support/update-php/">Get a faster, more secure website: update your PHP today</a></p></div>';
  }, 20);
}
