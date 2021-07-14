<?php

/**
 * @package    WordPress
 * @subpackage WC Poczta - Self Pickup with WooCommerce
 *
 * @copyright  Copyright (c) 2020-2021, Leszek Pomianowski
 * @link       https://rdev.cc/
 * @license    MPL-2.0 https://opensource.org/licenses/MPL-2.0
 */

namespace WCPoczta\Code\Components;

use WCPoczta\Code\Core\Bootstrap;
use WCPoczta\Code\Core\ShippingMethod;

final class Poczta extends ShippingMethod
{
  public const DEFAULT_PRICE = 11.99;

  public const DEFAULT_FREE = 200;

  public const DEFAULT_WEIGHT = 20;

  public function initialize()
  {
    $this->setTitle(__('Self Pickup - Polish Post', Bootstrap::DOMAIN));
    $this->setDescription(__('Pickup at Żabka, Orlen, Biedronka, Ruch and Poczta Polska', Bootstrap::DOMAIN));

    $this->setSupports([
      'shipping-zones',
      'instance-settings',
      'instance-settings-modal'
    ]);

    $this->setRequirements([
      'phone' => true
    ]);

    $this->registerSettings();
  }

  private function registerSettings(): void
  {
    $this->addSetting('title', [
      'type' => 'text',
      'title' => __('Title', Bootstrap::DOMAIN),
      'description' => __('Title displayed when you select a shipping option.', Bootstrap::DOMAIN),
      'default' => __('Self Pickup - Polish Post', Bootstrap::DOMAIN)
    ]);

    $this->addSetting('cost', [
      'type' => 'number',
      'title' => __('Cost', Bootstrap::DOMAIN),
      'description' => sprintf(__('Primary cost (default is %s net)', Bootstrap::DOMAIN), wc_price(self::DEFAULT_FREE)),
      'default' => self::DEFAULT_PRICE
    ]);

    $this->addSetting('points', [
      'type' => 'multiselect',
      'title' => __('Pickup points', Bootstrap::DOMAIN),
      'description' => __('Select which pickup points should be available', Bootstrap::DOMAIN),
      'default' => 'poczta',
      'options' => [
        'poczta' => __('Poczta', Bootstrap::DOMAIN),
        'orlen' => __('Orlen petrol stations', Bootstrap::DOMAIN),
        'automat_pocztowy' => __('Post Pickup machine', Bootstrap::DOMAIN),
        'ruch' => __('Ruch store', Bootstrap::DOMAIN),
        'zabka' => __('Żabka store', Bootstrap::DOMAIN),
        'freshmarket' => __('Freshmarket store', Bootstrap::DOMAIN),
        'automat_biedronka' => __('Biedronka store Pickup machine', Bootstrap::DOMAIN),
        'automat_carrefour' => __('Carrefour store Pickup machine', Bootstrap::DOMAIN),
        'automat_placowka' => __('Post Office Pickup machine', Bootstrap::DOMAIN),
        'automat_spolem' => __('Społem store Pickup machine', Bootstrap::DOMAIN),
        'skrzynka_pocztowa' => __('Post mailbox', Bootstrap::DOMAIN),
        'automat_lewiatan' => __('Lewiatan store Pickup machine', Bootstrap::DOMAIN),
        'lewiatan' => __('Lewiatan store', Bootstrap::DOMAIN),
        'abc' => __('ABC store', Bootstrap::DOMAIN),
        'delikatesy_centrum' => __('Deli Center store', Bootstrap::DOMAIN),
        'biedronka' => __('Biedronka store', Bootstrap::DOMAIN),
        'kaufland' => __('Kaufland store', Bootstrap::DOMAIN),
      ]
    ]);

    $this->addSetting('free_enable', [
      'type' => 'checkbox',
      'title' => __('Allow free shipping', Bootstrap::DOMAIN),
      'description' => __('If checked, it allows free shipping on the gross price listed below.', Bootstrap::DOMAIN),
      'default' => 'yes'
    ]);

    $this->addSetting('free_above', [
      'type' => 'number',
      'title' => __('Free shipping for orders above', Bootstrap::DOMAIN),
      'description' => sprintf(__('Default %s', Bootstrap::DOMAIN), wc_price(self::DEFAULT_FREE)),
      'default' => self::DEFAULT_FREE
    ]);

    $this->addSetting('weight', [
      'type' => 'number',
      'title' => __('Weight (kg)', Bootstrap::DOMAIN),
      'description' => sprintf(__('Maximum allowed weight (default is %skg)', Bootstrap::DOMAIN), self::DEFAULT_WEIGHT),
      'default' => self::DEFAULT_WEIGHT
    ]);
  }

  public function calculateShipping()
  {
    $methodPrice = $this->get_option('cost', self::DEFAULT_PRICE);
    $totalWeight = $this->getCartWeight();

    if ('yes' === $this->get_option('free_enable', 'yes') && $this->getCartTotal() >= (float) $this->get_option('free_above', self::DEFAULT_FREE)) {
      $methodPrice = 0;
    }

    $this->add_rate(
      [
        'id' => $this->id,
        'label' => $this->title,
        'calc_tax'  => 'per_order',
        'cost' => $methodPrice
      ]
    );
  }
}
