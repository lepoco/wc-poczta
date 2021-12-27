<?php

/**
 * @package    WordPress
 * @subpackage WC Poczta - Self Pickup with WooCommerce
 *
 * @copyright  Copyright (c) 2020-2021, Leszek Pomianowski
 * @link       https://dev.lepo.co/
 * @license    GPL-3.0 https://www.gnu.org/licenses/gpl-3.0.txt
 */

?>
<section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">
  <div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">

    <h2 class="woocommerce-column__title"><?php _e('Self pickup', self::DOMAIN); ?></h2>

    <p><?php _e('You have chosen pickup as the shipping method. Your package will be delivered to the selected point:', self::DOMAIN); ?></p>

    <address>
      <?php echo get_post_meta($data['id'], '_wcpoczta_name', true); ?>
      <br>
      <br>
      <?php echo get_post_meta($data['id'], '_wcpoczta_address', true); ?>
      <br>
      <?php echo get_post_meta($data['id'], '_wcpoczta_zipcode', true); ?>
      <br>
      <?php echo get_post_meta($data['id'], '_wcpoczta_city', true); ?>
    </address>

  </div>
</section>
