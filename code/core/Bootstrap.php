<?php

/**
 * @package    WordPress
 * @subpackage WC Poczta - Self Pickup with WooCommerce
 *
 * @copyright  Copyright (c) 2020-2021, Leszek Pomianowski
 * @link       https://lepo.co/
 * @license    GPL-3.0 https://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace WCPoczta\Code\Core;

final class Bootstrap
{
  public const SLUG = 'wc-poczta';

  public const DOMAIN = 'wc_poczta';

  public const PREFIX = 'wc_poczta_';

  public const SHIPPING_PATH = 'code/core/ShippingMethod.php';

  public const SHIPPING_NAMESPACE = '\\WCPoczta\\Code\\Core\\ShippingMethod';

  public const COMPONENTS_PATH = 'code/components/';

  public const COMPONENTS_NAMESPACE = '\\WCPoczta\\Code\\Components\\';

  public const CONTACT_NAME = 'lepo.co/contact';

  public const CONTACT_ADDRESS = 'https://lepo.co/contact';

  private $version = '';

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
  public function getPluginAsset(?string $assetPath = null): string
  {
    $pluginUrl = $this->getPluginUrl();
    $pluginUrl = rtrim($pluginUrl, '/') . '/';

    return $pluginUrl . 'assets/' . $assetPath;
  }

  /**
   * @return null|string|void
   */
  public function getPluginView(string $name, array $data = [], bool $obClean = false)
  {
    $path = Helpers::getAbsolutePath($this->pluginPath . 'code' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $name . '.php');

    if (!is_file($path)) {
      return;
    }

    if (!$obClean) {
      include $path;

      return;
    }

    //I know it sucks, but...
    $__HTML = ob_get_clean();
    $output = '';

    if (is_file($path)) {
      ob_start();

      include $path;

      $output = ob_get_clean();
    }

    ob_start();
    echo $__HTML;

    return $output;
  }

  public function getPluginPath(?string $subPath = null): string
  {
    $basePath = $this->pluginPath;

    if (null === $subPath) {
      return $basePath;
    }

    return Helpers::getAbsolutePath(rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $subPath);
  }

  public function getPluginUrl(): ?string
  {
    return $this->pluginUrl;
  }

  public function getVersion(): ?string
  {
    return $this->version;
  }

  public static function init(?string $pluginPath, ?string $pluginUrl, ?string $version = null): self
  {
    return (new self())->initialize($pluginPath, $pluginUrl, $version);
  }

  public static function isDebug(): bool
  {
    return defined('WP_DEBUG') && WP_DEBUG;
  }

  public static function camelToSnake(?string $input): string
  {
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
    $ret = $matches[0];

    foreach ($ret as &$match) {
      $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
    }

    return implode('_', $ret);
  }

  public static function log(string $message, array $data = []): void
  {
    if (function_exists('write_log')) {
      return;
    }

    $printData = '';
    $c = 0;

    foreach ($data as $key => $value) {
      if (!is_array($value)) {
        $printData .= ($c++ > 0 ? ', ' : '') . "'$key': '$value'";
      }
    }

    if (!empty($printData)) {
      $printData = ' [' . $printData . ']';
    }

    error_log('WC POCZTA: ' . $message . $printData);
  }

  private function initialize(?string $pluginPath, ?string $pluginUrl, ?string $version = null): self
  {
    $this->pluginPath = $pluginPath;
    $this->pluginUrl = $pluginUrl;

    if (null !== $version) {
      $this->version = $version;
    }

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
    $methodsPath = $this->getPluginPath(self::COMPONENTS_PATH);

    if (!is_dir($methodsPath)) {
      self::log('The shipping method directory could not be found.', ['method' => 'Bootstrap::setupMethods', 'pathSearched' => $methodsPath]);
      add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p><strong>WC Poczta</strong><br>' . __('The shipping method directory could not be found. The directory may not exist, or the error may be due to an incompatible server.', Bootstrap::DOMAIN) . '</p></div>';
      }, 20);
      return;
    }

    $methodsDir = scandir($this->getPluginPath(self::COMPONENTS_PATH));
    $methods = array_diff($methodsDir, ['.', '..']);

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
