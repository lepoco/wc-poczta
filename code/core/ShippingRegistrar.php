<?php

/**
 * @package    WordPress
 * @subpackage WC Poczta - Self Pickup with WooCommerce
 *
 * @copyright  Copyright (c) 2020-2021, Leszek Pomianowski
 * @link       https://rdev.cc/
 * @license    GPL-3.0 https://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace WCPoczta\Code\Core;

use WCPoczta\Code\Core\Bootstrap;

final class ShippingRegistrar
{
  private ?Bootstrap $bootstrap = null;

  private ?string $methodId = '';

  private ?string $methodName = '';

  private ?string $methodClassName = '';

  public static function register(?Bootstrap $bootstrap, ?string $id, ?string $method): self
  {
    $instance = new self();
    $instance->setBootstrap($bootstrap);
    $instance->setMethodId($id);
    $instance->registerMethod($method);

    return $instance;
  }

  public function wooInitMethod(): void
  {
    if (!class_exists(Bootstrap::SHIPPING_NAMESPACE)) {
      require_once $this->bootstrap->getPluginPath(Bootstrap::SHIPPING_PATH);
    }

    require_once $this->bootstrap->getPluginPath(Bootstrap::COMPONENTS_PATH . $this->methodName . '.php');
  }

  public function wooAddMethod(?array $methods): array
  {
    $methods[$this->methodId] = $this->methodClassName;
    return $methods;
  }

  public function getMethodId(): ?string
  {
    return $this->methodId;
  }

  protected function setMethodId(string $id): void
  {
    $this->methodId = $id;
  }

  protected function setBootstrap(?Bootstrap $bootstrap): void
  {
    $this->bootstrap = $bootstrap;
  }

  protected function registerMethod(string $name): bool
  {
    if (!is_file($this->bootstrap->getPluginPath(Bootstrap::COMPONENTS_PATH . $name . '.php'))) {
      return false;
    }

    $this->methodName = $name;
    $this->methodClassName = Bootstrap::COMPONENTS_NAMESPACE . $name;

    add_action('woocommerce_shipping_init', [$this, 'wooInitMethod']);
    add_filter('woocommerce_shipping_methods', [$this, 'wooAddMethod']);

    return true;
  }
}
