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

final class EasyPack extends ShippingMethod
{
  public const DEFAULT_PRICE = 11.99;

  public const DEFAULT_FREE = 200;

  public function initialize(): void
  {
    $this->setTitle(__('Self Pickup - InPost Paczkomaty', Bootstrap::DOMAIN));
    $this->setDescription(__('Pickup at InPost Paczkomaty', Bootstrap::DOMAIN));

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
      'default' => __('Self Pickup - InPost Paczkomaty', Bootstrap::DOMAIN)
    ]);

    $this->addSetting('cost', [
      'type' => 'number',
      'title' => sprintf(__('Cost (Dimensions %s)', Bootstrap::DOMAIN), 'A'),
      'description' => sprintf(__('Primary cost (default is %s net)', Bootstrap::DOMAIN), wc_price( self::DEFAULT_FREE )),
      'default' => self::DEFAULT_PRICE
    ]);

    $this->addSetting('points', [
      'type' => 'multiselect',
      'title' => __('Pickup points', Bootstrap::DOMAIN),
      'description' => __('Select which pickup points should be available', Bootstrap::DOMAIN),
      'default' => 'pop',
      'options' => [
        'pop' => __('Parcel collection point (POP)', Bootstrap::DOMAIN),
        'parcel_locker' => __('Parcel Locker', Bootstrap::DOMAIN)
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
      'description' => sprintf(__('Default %s', Bootstrap::DOMAIN), wc_price( self::DEFAULT_FREE )),
      'default' => self::DEFAULT_FREE
    ]);

    $this->addSetting('geolocation_enabled', [
      'type' => 'checkbox',
      'title' => __('Enable geolocation', Bootstrap::DOMAIN),
      'description' => __('If you enable this option, the pickup point will be proposed based on your geolocation.', Bootstrap::DOMAIN),
      'default' => 'no'
    ]);
  }

  public function calculateShipping()
  {
    $methodPrice = $this->get_option('cost', self::DEFAULT_PRICE);

    $totalPrice = $this->getCartTotal();
    $totalWeight = $this->getCartWeight();;

    if('yes' === $this->get_option('free_enable', 'yes') && $totalPrice >= (float) $this->get_option('free_above', self::DEFAULT_FREE)) {
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

  // public function calculate_shipping($package = array())
  // {
  //   $weight = 0;

  //   $cost = -1;

  //   foreach ($package['contents'] as $item_id => $values) {
  //     $_product = $values['data'];
  //     $weight = $weight + $_product->get_weight() * $values['quantity'];
  //   }

  //   if ($weight <= $this->get_option('weight_a'))
  //     $cost = $this->get_option('cost');
  //   else if ($weight <= $this->get_option('weight_b'))
  //     $cost = $this->get_option('cost_b');
  //   else if ($weight < $this->get_option('weight_c'))
  //     $cost = $this->get_option('cost_c');
  //   else
  //     $cost = -1; //too high

  //   $this->add_rate(
  //     array(
  //       'id'        => $this->get_rate_id(),
  //       'label'     => $this->title,
  //       'calc_tax'  => 'per_order',
  //       'cost'      => $this->get_option('cost')
  //     )
  //   );
  // }
}
