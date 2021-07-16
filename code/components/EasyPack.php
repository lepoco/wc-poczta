<?php

/**
 * @package    WordPress
 * @subpackage WC Poczta - Self Pickup with WooCommerce
 *
 * @copyright  Copyright (c) 2020-2021, Leszek Pomianowski
 * @link       https://rdev.cc/
 * @license    GPL-3.0 https://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace WCPoczta\Code\Components;

use WCPoczta\Code\Core\Bootstrap;
use WCPoczta\Code\Core\ShippingMethod;

final class EasyPack extends ShippingMethod
{
  public const DEFAULT_PRICE_A = 9.39;

  public const DEFAULT_PRICE_B = 10.21;

  public const DEFAULT_PRICE_C = 11.43;

  public const DEFAULT_DIMENSIONS_A = 110; //cm3

  public const DEFAULT_DIMENSIONS_B = 121; //cm3

  public const DEFAULT_DIMENSIONS_C = 143; //cm3

  public const WEIGHT_LIMIT = 25;

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
      'title' => sprintf(__('Cost (Size %s)', Bootstrap::DOMAIN), 'A'),
      'description' => sprintf(__('Primary cost (default is %s net)', Bootstrap::DOMAIN), wc_price(self::DEFAULT_PRICE_A)),
      'default' => self::DEFAULT_PRICE_A
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

    $this->addSetting('weight_limit', [
      'type' => 'number',
      'title' => __('Weight limit', Bootstrap::DOMAIN),
      'description' => __('Total weight limit, above which the shipping option will not show up.', Bootstrap::DOMAIN),
      'default' => self::WEIGHT_LIMIT
    ]);

    $this->addSetting('free_enable', [
      'type' => 'checkbox',
      'title' => __('Allow free shipping', Bootstrap::DOMAIN),
      'description' => __('If checked, it allows free shipping on the gross price listed below.', Bootstrap::DOMAIN),
      'default' => 'no'
    ]);

    $this->addSetting('free_above', [
      'type' => 'number',
      'title' => __('Free shipping for orders above', Bootstrap::DOMAIN),
      'description' => sprintf(__('Default %s', Bootstrap::DOMAIN), wc_price(self::DEFAULT_FREE)),
      'default' => self::DEFAULT_FREE
    ]);

    $this->addSetting('geolocation_enabled', [
      'type' => 'checkbox',
      'title' => __('Enable geolocation', Bootstrap::DOMAIN),
      'description' => __('If you enable this option, the pickup point will be proposed based on your geolocation.', Bootstrap::DOMAIN),
      'default' => 'no'
    ]);

    //TODO
    $this->addSetting('dimensions_enabled', [
      'type' => 'checkbox',
      'title' => __('Enable calculation of the package size', Bootstrap::DOMAIN) . ' [The feature will be available... in the future]',
      'description' => __('If you enable this option, the plugin will try to determine the package size and then suggest a price based on it.', Bootstrap::DOMAIN),
      'default' => 'no',
      'disabled' => true
    ]);

    $this->addSetting('dimensions_a', [
      'type' => 'number',
      'title' => sprintf(__('Dimensions limit (Size %s)', Bootstrap::DOMAIN), 'A'),
      'description' => sprintf(__('Dimensions for size %s (default is %s)', Bootstrap::DOMAIN), 'A', self::DEFAULT_DIMENSIONS_A . 'cm <i>[x * y * z]</i>'),
      'default' => self::DEFAULT_DIMENSIONS_A,
      'disabled' => true
    ]);

    $this->addSetting('cost_b', [
      'type' => 'number',
      'title' => sprintf(__('Cost for size %s', Bootstrap::DOMAIN), 'B'),
      'description' => sprintf(__('Primary cost (default is %s net)', Bootstrap::DOMAIN), wc_price(self::DEFAULT_PRICE_B)),
      'default' => self::DEFAULT_PRICE_B,
      'disabled' => true
    ]);

    $this->addSetting('dimensions_b', [
      'type' => 'number',
      'title' => sprintf(__('Dimensions limit (Size %s)', Bootstrap::DOMAIN), 'B'),
      'description' => sprintf(__('Dimensions for size %s (default is %s)', Bootstrap::DOMAIN), 'B', self::DEFAULT_DIMENSIONS_B . 'cm <i>[x * y * z]</i>'),
      'default' => self::DEFAULT_DIMENSIONS_B,
      'disabled' => true
    ]);

    $this->addSetting('cost_c', [
      'type' => 'number',
      'title' => sprintf(__('Cost for size %s', Bootstrap::DOMAIN), 'C'),
      'description' => sprintf(__('Primary cost (default is %s net)', Bootstrap::DOMAIN), wc_price(self::DEFAULT_PRICE_C)),
      'default' => self::DEFAULT_PRICE_C,
      'disabled' => true
    ]);

    $this->addSetting('dimensions_c', [
      'type' => 'number',
      'title' => sprintf(__('Dimensions limit (Size %s)', Bootstrap::DOMAIN), 'C'),
      'description' => sprintf(__('Dimensions for size %s (default is %s)', Bootstrap::DOMAIN), 'C', self::DEFAULT_DIMENSIONS_C . 'cm <i>[x * y * z]</i>'),
      'default' => self::DEFAULT_DIMENSIONS_C,
      'disabled' => true
    ]);
  }

  public function calculateShipping($package = [])
  {
    $weightLimit = (float) $this->get_option('weight_limit', self::WEIGHT_LIMIT);
    $methodPrice = (float) $this->get_option('cost', self::DEFAULT_PRICE_A);

    $totalPrice = (float) $this->getCartTotal();
    $totalWeight = (float) $this->getCartWeight($package);

    if ($weightLimit > 0 && $totalWeight > $weightLimit) {
      return; //Permitted weight exceeded
    }

    if ('yes' === $this->get_option('free_enable', 'yes') && $totalPrice >= (float) $this->get_option('free_above', self::DEFAULT_FREE)) {
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
