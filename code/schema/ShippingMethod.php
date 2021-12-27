<?php

namespace WCPoczta\Code\Schema;

/**
 * Describes shipping method abstraction.
 *
 * @author    Leszek Pomianowski <kontakt@rapiddev.pl>
 * @copyright 2021 Leszek Pomianowski
 * @license   GPL-3.0 https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://dev.lepo.co/
 */
interface ShippingMethod
{
  public function initialize();

  public function calculateShipping();
}
