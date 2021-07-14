<?php

/**
 * @package    WordPress
 * @subpackage WC Poczta - Self Pickup with WooCommerce
 *
 * @copyright  Copyright (c) 2020-2021, Leszek Pomianowski
 * @link       https://rdev.cc/
 * @license    MPL-2.0 https://opensource.org/licenses/MPL-2.0
 */

namespace WCPoczta\Code\Core;

final class Bootstrap
{
  public const DOMAIN = 'wc_poczta';

  public const PREFIX = 'wc_poczta_';

  public const VERSION = '1.2.0';

  public const SHIPPING_PATH = 'code\\core\\ShippingMethod.php';

  public const SHIPPING_NAMESPACE = '\\WCPoczta\\Code\\Core\\ShippingMethod';

  public const COMPONENTS_PATH = 'code\\components\\';

  public const COMPONENTS_NAMESPACE = '\\WCPoczta\\Code\\Components\\';

  private $pluginPath = '';

  private $pluginUrl = '';

  private $objects = [];

  public function getPluginBasename(): string
  {
    return basename($this->getPluginPath());
  }

  /**
   * @param ?string $assetPath Path to the searched file
   */
  public function getPluginAsset($assetPath = null): string
  {
    $pluginUrl = $this->getPluginUrl();

    if (strpos('/', $pluginUrl, 0) === 0) {
      $pluginUrl .= '/';
    }

    return $pluginUrl . $assetPath;
  }

  public function getPluginPath($subPath = null): string
  {
    $basePath = $this->pluginPath;

    if (null === $subPath) {
      return $basePath;
    }

    if (strpos('/', $basePath, 0) === 0) {
      $basePath .= '/';
    }

    return $basePath . $subPath;
  }

  public function getPluginUrl(): string
  {
    return $this->pluginUrl;
  }

  public static function init($pluginPath, $pluginUrl): self
  {
    return (new self())->initialize($pluginPath, $pluginUrl);
  }

  public static function isDebug(): bool
  {
    return defined('WP_DEBUG') && WP_DEBUG;
  }

  public static function camelToSnake($input): string
  {
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
    $ret = $matches[0];

    foreach ($ret as &$match) {
      $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
    }

    return implode('_', $ret);
  }

  private function initialize($pluginPath, $pluginUrl): self
  {
    $this->pluginPath = $pluginPath;
    $this->pluginUrl = $pluginUrl;

    $this->setupDomain();
    $this->setupMethods();
    $this->setupMeta();

    return $this;
  }

  private function setupDomain(): void
  {
    add_action('plugins_loaded', function () {
      load_plugin_textdomain(self::DOMAIN, false, $this->getPluginBasename() . '/languages/');
    });
  }

  private function setupMethods(): void
  {
    $methods = array_diff(scandir($this->getPluginPath(self::COMPONENTS_PATH)), ['.', '..']);

    foreach ($methods as $method) {
      if ('.php' === substr($method, -4)) {
        $method = substr($method, 0, -4);
        $this->objects[] = ShippingRegistrar::register(@$this, self::PREFIX . self::camelToSnake($method), $method);
      }
    }
  }

  private function setupMeta(): void
  {
    Actions::initialize(@$this, $this->objects);
  }
}
