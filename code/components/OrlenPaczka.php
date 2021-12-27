<?php

namespace WCPoczta\Code\Components;

use WCPoczta\Code\Core\Bootstrap;
use WCPoczta\Code\Core\ShippingMethod;

/**
 * Class containing the logic of the OrlenPaczka shipping method.
 *
 * @author    Leszek Pomianowski <kontakt@rapiddev.pl>
 * @copyright 2021 Leszek Pomianowski
 * @license   GPL-3.0 https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://dev.lepo.co/
 * @see https://www.orlenpaczka.pl/
 * @see https://www.orlenpaczka.pl/wp-content/themes/orlenpaczka/files/Instrukcja_Manager%20ORLEN%20Paczka.pdf
 * @see https://www.orlenpaczka.pl/wp-content/uploads/2021/10/API_ORLENPaczka_v_1-16-007_A.pdf
 */
final class OrlenPaczka extends ShippingMethod implements \WCPoczta\Code\Schema\ShippingMethod
{
  public const DEFAULT_PRICE = 11.99;

  public const DEFAULT_FREE = 200;

  public const WEIGHT_LIMIT = 25;

  public function initialize(): void
  {
    $this->setTitle(__('Self Pickup - Orlen Paczka', Bootstrap::DOMAIN));
    $this->setDescription(__('Pickup at Orlen fuel stations', Bootstrap::DOMAIN));

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

  public function calculateShipping($package = []): void
  {
    $weightLimit = (float) $this->get_option('weight_limit', self::WEIGHT_LIMIT);
    $methodPrice = (float) $this->get_option('cost', self::DEFAULT_PRICE);

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

  protected function registerSettings(): void
  {
    $this->addSetting('title', [
      'type' => 'text',
      'title' => __('Title', Bootstrap::DOMAIN),
      'description' => __('Title displayed when you select a shipping option.', Bootstrap::DOMAIN),
      'default' => __('Self Pickup - Orlen Paczka', Bootstrap::DOMAIN)
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
        'automat_placowka' => __('Post Office Pickup machine', Bootstrap::DOMAIN),
        'automat_pocztowy' => __('Post Pickup machine', Bootstrap::DOMAIN),
        'skrzynka_pocztowa' => __('Post mailbox', Bootstrap::DOMAIN),
        'zabka' => __('Żabka store', Bootstrap::DOMAIN),
        'ruch' => __('Ruch store', Bootstrap::DOMAIN),
        'orlen' => __('Orlen petrol stations', Bootstrap::DOMAIN),
        'biedronka' => __('Biedronka store', Bootstrap::DOMAIN),
        'freshmarket' => __('Freshmarket store', Bootstrap::DOMAIN),
        'automat_biedronka' => __('Biedronka store Pickup machine', Bootstrap::DOMAIN),
        'automat_carrefour' => __('Carrefour store Pickup machine', Bootstrap::DOMAIN),
        'automat_spolem' => __('Społem store Pickup machine', Bootstrap::DOMAIN),
        'automat_lewiatan' => __('Lewiatan store Pickup machine', Bootstrap::DOMAIN),
        'lewiatan' => __('Lewiatan store', Bootstrap::DOMAIN),
        'abc' => __('ABC store', Bootstrap::DOMAIN),
        'delikatesy_centrum' => __('Deli Center store', Bootstrap::DOMAIN),
        'kaufland' => __('Kaufland store', Bootstrap::DOMAIN),
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
  }
}
