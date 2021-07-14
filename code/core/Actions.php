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

final class Actions
{
  private $bootstrap = null;

  private $activeMethod = '';

  private $methods = [];

  private $methodsIds = [];

  public static function initialize($bootstrap, $methods): self
  {
    return (new self())->registerMeta($bootstrap, $methods);
  }

  private function registerMeta($bootstrap, $methods): self
  {
    $this->bootstrap = $bootstrap;
    $this->methods = $methods;

    foreach ($this->methods as $method) {
      $this->methodsIds[] = $method->getMethodId();
    }

    add_action('wp_enqueue_scripts', [$this, 'Scripts'], 99);
    add_action('wp_head', [$this, 'FrontHead'], 99);
    add_action('admin_notices', [$this, 'AdminNotice'], 20);

    add_action('woocommerce_after_shipping_rate', [$this, 'customFields'], 20, 2);
    add_action('woocommerce_checkout_process', [$this, 'customFieldsValidation']);
    add_action('woocommerce_checkout_update_order_meta', [$this, 'customFieldsSave']);

    add_action('add_meta_boxes', function () {
      foreach (wc_get_order_types('order-meta-boxes') as $type) {
        add_meta_box('woocommerce-order-wcpoczta', __('Shipping', Bootstrap::DOMAIN), [$this, 'metaAdmin'], $type, 'normal', 'high');
      }
    }, 30);

    return $this;
  }

  public function Scripts(): void
  {
    if (!function_exists('is_checkout')) {
      return;
    }

    if (!(is_cart() || is_checkout())) {
      return;
    }

    //https://pp-widget.silk-sh.eu/widget/scripts/ppwidget.js

    wp_enqueue_style('cdn-wcpoczta-easypack', 'https://geowidget.easypack24.net/css/easypack.css', [], Bootstrap::VERSION);
    wp_enqueue_script('cdn-wcpoczta-easypack', 'https://geowidget.easypack24.net/js/sdk-for-javascript.js', [], Bootstrap::VERSION, true);
    wp_enqueue_script('cdn-wcpoczta-pocztapolska', 'https://mapa.ecommerce.poczta-polska.pl/widget/scripts/ppwidget.js', [], Bootstrap::VERSION, true);
    wp_enqueue_script('wc-poczta', $this->bootstrap->getPluginAsset('js/wc-poczta.min.js'), [], Bootstrap::VERSION, true);
  }

  public function FrontHead(): void
  {
    if (!function_exists('is_checkout')) {
      return;
    }

    if (!(is_cart() || is_checkout())) {
      return;
    }

    $data = [
      'uri' => 'https://rdev.cc/',
      'version' => Bootstrap::VERSION,
      'prefix' => Bootstrap::PREFIX,
      'domain' => Bootstrap::DOMAIN,
      'methods' => $this->methodsIds,
    ];

    echo '<style type="text/css">.wc-poczta__button {width: 100%;;margin-bottom: 15px} .wc-poczta, .wc-poczta-input, .wc-poczta-input > input {width: 100%;}.wc-poczta__container {margin-bottom:20px;}</style>';
    echo '<script type="text/javascript">const WCPOCZTA = ' . json_encode($data, JSON_UNESCAPED_UNICODE) . '</script>';
  }

  public function AdminNotice(): void
  {
    $html  = '<div class="notice notice-success is-dismissible"><p>';
    $html .= '<strong>' . __('WC Poczta', 'sample-text-domain') . '</strong>';
    $html .= '<br>' . __('Done!', 'sample-text-domain') . '';
    $html .= '</p></div>';

    echo $html;
  }

  public function customFields($method, $index): void
  {
    if (!in_array($method->method_id, $this->methodsIds)) {
      return;
    }

    if (!$this->isSelectedMethod($method, $index)) {
      return;
    }

    $hiddenFields = [
      'wc-poczta__input--raw',
      'wc-poczta__input--id',
      'wc-poczta__input--name',
      'wc-poczta__input--type',
      'wc-poczta__input--city',
      'wc-poczta__input--address',
      'wc-poczta__input--zipcode',
      'wc-poczta__input--province'
    ];

    $selectedMethod = $this->getShippingMethod($method->method_id);
    $currentAddress = trim(WC()->checkout->get_value('wc-poczta__input--address') . ' ' . WC()->checkout->get_value('wc-poczta__input--city'));
    $buttonDataSettings = '';

    if (null !== $selectedMethod) {
      foreach ($selectedMethod->instance_settings as $key => $value) {
        if (is_array($value)) {
          if ('points' === $key) {
            $points = '';

            for ($i = 0; $i < count($value); $i++) {
              $points .= ($i > 0 ? ';' : '') . $value[$i];
            }

            $buttonDataSettings .= ' ' . 'data-s-points="' . $points . '"';
          }
        } else if (!in_array($key, ['wppoczta_tip', 'free_above', 'free_enable'])) {
          $buttonDataSettings .= ' ' . 'data-s-' . $key . '="' . $value . '"';
        }
      }
    }

    $html = '';
    $html .= '<div class="wc-poczta wc-poczta__container ' . $method->method_id . '">';
    $html .= '<p class="form-row form-row-wide"><button type="button" class="button wc-poczta__button" data-method="' . $method->method_id . '"' . $buttonDataSettings . '>' . __('Select a pickup point', Bootstrap::DOMAIN) . '</button></p>';

    foreach ($hiddenFields as $field) {
      $html .= '<input type="hidden" name="' . $field . '" id="' . $field . '" value="" data-kpxc-id="' . $field . '">';
    }

    $html .= '<p class="form-row form-row-wide wc-poczta-input"><input disabled="disabled" type="text" class="input-text " name="wc-poczta__input--carrier" id="wc-poczta__input--carrier" placeholder="' . __('Pickup point name', Bootstrap::DOMAIN) . '" value="' . WC()->checkout->get_value('wc-poczta__input--name') . '"></p>';
    $html .= '<p class="form-row form-row-wide wc-poczta-input"><input disabled="disabled" type="text" class="input-text " name="wc-poczta__input--carrier_address" id="wc-poczta__input--carrier_address" placeholder="' . __('Pickup point address', Bootstrap::DOMAIN) . '" value="' . $currentAddress . '"></p>';

    $html .= '</div>';

    echo $html;
  }

  public function customFieldsValidation(): void
  {
    if (!$this->verifyPostData('shipping_method')) {
      return;
    }

    if (!in_array($_POST['shipping_method'][0], $this->methodsIds)) {
      return;
    }

    if (!isset($_POST['wc-poczta__input--name'], $_POST['wc-poczta__input--address'])) {
      wc_add_notice(__('There was an error in the form. No fields with pickup address found', Bootstrap::DOMAIN), 'error');
    }

    if (empty($_POST['wc-poczta__input--name']) || empty($_POST['wc-poczta__input--address'])) {
      wc_add_notice(__('You must select a <strong>pickup point</strong>', Bootstrap::DOMAIN), 'error');

      return;
    }

    if (
      !($this->verifyPostData('wc-poczta__input--raw')
        && $this->verifyPostData('wc-poczta__input--id')
        && $this->verifyPostData('wc-poczta__input--name')
        && $this->verifyPostData('wc-poczta__input--type')
        && $this->verifyPostData('wc-poczta__input--city')
        && $this->verifyPostData('wc-poczta__input--address'))
    ) {
      wc_add_notice(sprintf(__('\'%s\' pickup point is invalid. Form error?', RDEV_SELFPICKUP_DOMAIN), sanitize_text_field($_POST['wc-poczta__input--name'])), 'error');
    }
  }

  public function customFieldsSave($orderId): void
  {
    if (!$this->verifyPostData('shipping_method')) {
      return;
    }

    if (!in_array($_POST['shipping_method'][0], $this->methodsIds)) {
      return;
    }

    update_post_meta($orderId, '_wcpoczta_raw', $this->getPostData('wc-poczta__input--raw'));
    update_post_meta($orderId, '_wcpoczta_id', $this->getPostData('wc-poczta__input--id'));
    update_post_meta($orderId, '_wcpoczta_name', $this->getPostData('wc-poczta__input--name'));
    update_post_meta($orderId, '_wcpoczta_type', $this->getPostData('wc-poczta__input--type'));
    update_post_meta($orderId, '_wcpoczta_city', $this->getPostData('wc-poczta__input--city'));
    update_post_meta($orderId, '_wcpoczta_address', $this->getPostData('wc-poczta__input--address'));
    update_post_meta($orderId, '_wcpoczta_zipcode', $this->getPostData('wc-poczta__input--zipcode'));
    update_post_meta($orderId, '_wcpoczta_province', $this->getPostData('wc-poczta__input--province'));
  }

  public function metaAdmin($post): void
  {
    echo '<style type="text/css">#woocommerce-order-wcpoczta>.postbox-header{display:none}#woocommerce-order-wcpoczta .inside{padding:23px 24px;}</style>';

    $__bc = $this->__backwardCompatibility($post);

    if (!empty($__bc)) {
      echo $__bc;

      return;
    }

    $wcpId = get_post_meta($post->ID, '_wcpoczta_id', true);

    if (empty($wcpId)) {
      echo __('Pickup point has not been selected as shipping method.', Bootstrap::DOMAIN);

      return;
    }
    //dump($post);
    //I know it's really rude, but it works.
    //echo '</div></div></div><div><div><div>';
    // echo '</div><div>';

    // $orderId = $order->get_id();
    // dump($order);

    dump(get_post_meta($post->ID, '_wcpoczta_city', true));
    dump(get_post_meta($post->ID, '_wcpoczta_address', true));

    $data = get_post_meta($post->ID, '_wcpoczta_raw', true);
    dump(json_decode($data, true));
  }

  private function verifyPostData(string $key): bool
  {
    if (!isset($_POST[$key])) {
      return false;
    }

    if (empty($_POST[$key])) {
      return false;
    }

    return true;
  }

  private function getPostData(string $key): string
  {
    if (!isset($_POST[$key])) {
      return 'unknown';
    }

    if (empty($_POST[$key])) {
      return 'unknown';
    }

    return sanitize_text_field($_POST[$key]);
  }

  private function getShippingMethod(string $id): ?object
  {
    $allMethods = WC()->shipping->get_shipping_methods();

    foreach ($allMethods as $key => $method) {
      if ($id === $method->id && 'yes' === $method->enabled) {
        return $method;
      }
    }

    return null;
  }

  private function isSelectedMethod($method, $index): bool
  {
    $choosenMethod = WC()->session->chosen_shipping_methods[$index];

    return false !== strpos($choosenMethod, $method->method_id);
  }

  /**
   * This feature will be removed in the future, it returns the HTML value for pick points from the previous plugin version.
   * @deprecated Since version 1.2.0
   */
  private function __backwardCompatibility($post): ?string
  {
    $html = null;
    //$html = 'te dane są ze starej wersji wtyczki';

    if(null !== $html) {
      $html .= '<p style="color:#777;"><small>'.__('Above information comes from the old plugin version. This data may disappear during future updates.', Bootstrap::DOMAIN).'</small><br><a href="https://wordpress.org/plugins/wc-poczta/">wordpress.org/plugins/wc-poczta</a></p>';
    }
    return $html;
  }
}
