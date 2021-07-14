<?php

/**
 * @package    WordPress
 * @subpackage WC Poczta - Self Pickup with WooCommerce
 *
 * @copyright  Copyright (c) 2020-2021, Leszek Pomianowski
 * @link       https://rdev.cc/
 * @license    MPL-2.0 https://opensource.org/licenses/MPL-2.0
 *
 * @wc-poczta
 * Plugin Name: Self Pickup with WooCommerce - InPost Paczkomaty, Polish Post, Żabka and Ruch
 * Plugin URI: http://wordpress.org/plugins/wc-poczta/
 * Description: Integration of self pickup in the Inpost Paczkomaty, Polish Post offices, Orlen, Żabka, Ruch stores and others.
 * Author: Leszek Pomianowski
 * Author URI: https://rdev.cc/
 * License: MPL-2.0
 * License URI: https://opensource.org/licenses/MPL-2.0
 * Version: 1.2.0
 * Text Domain: wc_poczta
 * Domain Path: /languages
 */

namespace WCPoczta;

defined('ABSPATH') or die('No script kiddies please!');

$pluginPath = plugin_dir_path(__FILE__);

if(defined('WP_DEBUG') && WP_DEBUG && is_file($pluginPath . 'vendor/autoload.php')) {
  require_once $pluginPath . 'vendor/autoload.php';
}

require_once $pluginPath . 'code/core/' . 'Bootstrap.php';
require_once $pluginPath . 'code/core/' . 'Actions.php';
require_once $pluginPath . 'code/core/' . 'ShippingRegistrar.php';

\WCPoczta\Code\Core\Bootstrap::init($pluginPath, plugin_dir_url(__FILE__));
