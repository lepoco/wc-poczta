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

use \WC_Shipping_Method;
use WCPoczta\Code\Core\Bootstrap;

abstract class ShippingMethod extends WC_Shipping_Method
{
  public const ROOT_DEFAULT_PRICE = 11.99;

  public const CONTACT_NAME = 'rdev.cc/contact';

  public const CONTACT_ADDRESS = 'https://rdev.cc/contact';

  private $settingsKeys = ['enabled', 'title', 'info'];

  public function __construct($instanceId = 0)
  {
    $this->setId($instanceId);

    if (method_exists($this, 'initialize')) {
      $this->{'initialize'}();
    }

    $this->addSetting('wppoczta_tip', [
      'type' => 'hidden',
      'description' => $this->getPluginTip(),
      'default' => __('All in all, I\'d like more features', Bootstrap::DOMAIN),
      'hidden' => 'hidden',
      'disabled' => true
    ]);

    $this->init_settings();

    $this->enabled = $this->get_option('enabled', 'no');
    $this->title   = $this->get_option('title', $this->method_title);
    $this->info    = $this->get_option('info', $this->method_description);
  }

  public function calculate_shipping($package = []): void
  {
    if (!method_exists($this, 'calculateShipping')) {
      $this->add_rate(
        [
          'id' => $this->id,
          'label' => $this->title,
          'calc_tax'  => 'per_order',
          'cost' => $this->get_option('cost', self::ROOT_DEFAULT_PRICE)
        ]
      );

      return;
    }

    $this->{'calculateShipping'}();
  }

  public function getSettings(): array
  {
    $settings = [];

    foreach ($this->settingsKeys as $key) {
      $settings[$key] = $this->get_option($key);
    }

    return $settings;
  }

  public function getId(): string
  {
    return $this->instance_id;
  }

  protected function setTitle($title): void
  {
    $this->method_title = $title;
  }

  protected function setDescription($description): void
  {
    $this->method_description = $description;
  }

  protected function setSupports($supports): void
  {
    $this->supports = $supports;
  }

  protected function addSetting($title, $args): void
  {
    $this->instance_form_fields[$title] = $args;
  }

  protected function setRequirements($requirements): void
  {
    if (!is_array($requirements)) {
      return;
    }

    if (isset($requirements['phone']) && $requirements['phone']) {
      add_filter('woocommerce_billing_fields', function ($fields) {
        $fields['billing_phone']['required'] = true;
        return $fields;
      }, 10, 1);
    }
  }

  protected function setId($instanceId): void
  {
    $this->instance_id = absint($instanceId);
    $this->id = Bootstrap::PREFIX . Bootstrap::camelToSnake($this->getClassName());
  }

  protected function getClassName()
  {
    $path = explode('\\', get_class($this));
    return array_pop($path);
  }

  protected function getCartTotal()
  {
    $cartContents = WC()->cart->cart_contents_total;
    if (WC()->cart->prices_include_tax) {
      $cartContents += WC()->cart->tax_total;
    }

    return $cartContents;
  }

  protected function getCartCount()
  {
    return WC()->cart->get_cart_contents_count();
  }

  protected function getCartWeight()
  {
    $totalWeight = (float) 0;
    $items = WC()->cart->get_cart();

    foreach ($items as $item) {
      if (isset($item['data'])) {
        $productWeight = $item['data']->get_weight();

        if (!empty($productWeight)) {
          $totalWeight += floatval($productWeight);
        }
      }
    }

    return $totalWeight;
  }

  protected function getPluginTip(): string
  {
    $tipHtml = '<strong>WC POCZTA</strong><br><br>';
    $tipHtml .= __('This plugin is free, but it takes time to add new features and fix bugs.', Bootstrap::DOMAIN);
    $tipHtml .= '<br>';
    $tipHtml .= __('If you want to support us and speed up the development of the plugin, contact us.', Bootstrap::DOMAIN);
    $tipHtml .= '<br><p><a class="button button-primary button-large" target="_blank" rel="noopener" href="' . self::CONTACT_ADDRESS . '">' . self::CONTACT_NAME . '</a>';
    $tipHtml .= ' <a class="button button-primary button-large" target="_blank" rel="noopener" href="https://www.paypal.com/paypalme/devcc">paypal.me/devcc</a></p>';

    return $tipHtml;
  }
}
