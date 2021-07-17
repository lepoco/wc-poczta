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
      $parentPath = $this->bootstrap->getPluginPath(Bootstrap::SHIPPING_PATH);

      if (!is_file($parentPath)) {
        Bootstrap::log('Class creating the shipping method could not be found.', ['method' => 'ShippingRegistrar::wooInitMethod', 'shipping' => $this->methodName, 'pathSearched' => $parentPath]);

        return;
      }

      require_once $parentPath;
    }

    $methodPath = $this->bootstrap->getPluginPath(Bootstrap::COMPONENTS_PATH . $this->methodName . '.php');

    if (!is_file($methodPath)) {
      Bootstrap::log('Shipping method class could not be found.', ['method' => 'ShippingRegistrar::wooInitMethod', 'shipping' => $this->methodName, 'pathSearched' => $methodPath]);

      return;
    }

    require_once $this->bootstrap->getPluginPath(Bootstrap::COMPONENTS_PATH . $this->methodName . '.php');
  }

  public function wooAddMethod(?array $methods): array
  {
    if (class_exists($this->methodClassName)) {
      $methods[$this->methodId] = $this->methodClassName;
    } else {
      Bootstrap::log('Shipping method could not be added.', ['method' => 'ShippingRegistrar::wooAddMethod', 'shippingId' => $this->methodId, 'shippingClass' => $this->methodName]);
    }

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
    $methodPath = $this->bootstrap->getPluginPath(Bootstrap::COMPONENTS_PATH . $name . '.php');

    if (!is_file($methodPath)) {
      Bootstrap::log('Shipping method could not be registered.', ['method' => 'ShippingRegistrar::registerMethod', 'shippingName' => $name, 'pathSearched' => $methodPath]);

      return false;
    }

    $this->methodName = $name;
    $this->methodClassName = Bootstrap::COMPONENTS_NAMESPACE . $name;

    add_action('woocommerce_shipping_init', [$this, 'wooInitMethod']);
    add_filter('woocommerce_shipping_methods', [$this, 'wooAddMethod']);

    return true;
  }
}
