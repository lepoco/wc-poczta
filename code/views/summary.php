<?php

/**
 * @package    WordPress
 * @subpackage WC Poczta - Self Pickup with WooCommerce
 *
 * @copyright  Copyright (c) 2020-2021, Leszek Pomianowski
 * @link       https://rdev.cc/
 * @license    GPL-3.0 https://www.gnu.org/licenses/gpl-3.0.txt
 */

/** Image */
$logoImage = 'envelo-logo.png';
$methodType = get_post_meta($data['post']->ID, '_wcpoczta_type', true);
$methodTypeLower = strtolower($methodType);

if (false !== strpos($methodTypeLower, 'pok') || false !== strpos($methodTypeLower, 'pop')) {
  $logoImage = 'inpost-logo.svg';
} else if (false !== strpos($methodTypeLower, 'orlen')) {
  $logoImage = 'orlen-logo.svg';
} else if (false !== strpos($methodTypeLower, 'zabka') || false !== strpos($methodTypeLower, 'żabka')) {
  $logoImage = 'zabka-logo.svg';
} else if (false !== strpos($methodTypeLower, 'biedronka')) {
  $logoImage = 'biedronka-logo.png';
} else if (false !== strpos($methodTypeLower, 'ruch')) {
  $logoImage = 'ruch-logo.png';
} else if (false !== strpos($methodTypeLower, 'abc')) {
  $logoImage = 'abc-logo.png';
} else if (false !== strpos($methodTypeLower, 'lewiatan')) {
  $logoImage = 'lewiatan-logo.png';
}

$logoImageUrl = $this->getPluginAsset('img/' . $logoImage);

/** Data */



$displayData = [
  'point'   => get_post_meta($data['post']->ID, '_wcpoczta_name', true),
  'phone'   => $data['order']->get_billing_phone(),
  'weight'  => $data['weight'] > 0 ? $data['weight'] . ' ' . get_option('woocommerce_weight_unit') : __('Unknown', self::DOMAIN),
  'type'    => strtoupper(str_replace([';', '_'], [' / ', ' '], $methodType)),
  'address' => get_post_meta($data['post']->ID, '_wcpoczta_address', true),
  'zipcode' => get_post_meta($data['post']->ID, '_wcpoczta_zipcode', true),
  'city'    => get_post_meta($data['post']->ID, '_wcpoczta_city', true)
];

?>
<div class="wc-poczta-order">
  <div class="wc-poczta-order__image">
    <img src="<?php echo $logoImageUrl; ?>">
  </div>

  <div class="wc-poczta-order__wrapper">
    <div class="wc-poczta-order__container">

      <div class="wc-poczta-order__column">
        <div class="wc-poczta-order__column__content">
          <?php _e('Pickup point', self::DOMAIN); ?>
          <div class="wc-poczta-order__flex">
            <strong><?php echo $displayData['point']; ?></strong>
            <span class="wc-poczta-order__clipboard" data-clipboard-text="<?php echo $displayData['point']; ?>"></span>
          </div>
        </div>
        <div class="wc-poczta-order__column__content">
          <?php _e('Phone', self::DOMAIN); ?>
          <div class="wc-poczta-order__flex">
            <strong><?php echo $displayData['phone']; ?></strong>
            <span class="wc-poczta-order__clipboard" data-clipboard-text="<?php echo $displayData['phone']; ?>"></span>
          </div>
        </div>
      </div>

      <div class="wc-poczta-order__column">
        <div class="wc-poczta-order__column__content">
          <?php _e('Weight', self::DOMAIN); ?>
          <div class="wc-poczta-order__flex">
            <strong><?php echo $displayData['weight']; ?></strong>
            <span class="wc-poczta-order__clipboard" data-clipboard-text="<?php echo $displayData['weight']; ?>"></span>
          </div>
        </div>
        <div class="wc-poczta-order__column__content">
          <?php _e('Type', self::DOMAIN); ?>
          <div class="wc-poczta-order__flex">
            <strong><?php echo $displayData['type']; ?></strong>
            <span class="wc-poczta-order__clipboard" data-clipboard-text="<?php echo $displayData['type']; ?>"></span>
          </div>
        </div>
      </div>

      <div class="wc-poczta-order__column">
        <div class="wc-poczta-order__column__content">
          <?php _e('Pickup point address', self::DOMAIN); ?>
          <div class="wc-poczta-order__flex">
            <strong><?php echo $displayData['address']; ?></strong>
            <span class="wc-poczta-order__clipboard" data-clipboard-text="<?php echo $displayData['address']; ?>"></span>
          </div>
          <div class="wc-poczta-order__flex">
            <strong><?php echo $displayData['zipcode']; ?></strong>
            <span class="wc-poczta-order__clipboard" data-clipboard-text="<?php echo $displayData['zipcode']; ?>"></span>
          </div>
          <div class="wc-poczta-order__flex">
            <strong><?php echo $displayData['city']; ?></strong>
            <span class="wc-poczta-order__clipboard" data-clipboard-text="<?php echo $displayData['city']; ?>"></span>
          </div>
        </div>
      </div>

    </div>
  </div>

  <div class="wc-poczta-order__footer">
    WC POCZTA - <a target="_blank" rel="noopener" href="https://rdev.cc/">rdev.cc/</a>
  </div>

</div>

<div class="wc-poczta-alert --hidden --success">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-square" viewBox="0 0 16 16">
    <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z" />
    <path d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.235.235 0 0 1 .02-.022z" />
  </svg> <span class="wc-poczta-alert__content"><?php _e('The data has been copied to the clipboard.', self::DOMAIN); ?></span>
</div>