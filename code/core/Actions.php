<?php

/**
 * @package    WordPress
 * @subpackage WC Poczta - Self Pickup with WooCommerce
 *
 * @copyright  Copyright (c) 2020-2021, Leszek Pomianowski
 * @link       https://rdev.cc/
 * @license    GPL-3.0 https://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace WCPoczta\Code\Core;

use WP_Post;
use WC_Order;

final class Actions
{
  private $bootstrap = null;

  private $methods = [];

  private $methodsIds = [];

  public static function initialize($bootstrap, $methods): self
  {
    return (new self())->register($bootstrap, $methods);
  }

  private function register($bootstrap, $methods): self
  {
    $this->bootstrap = $bootstrap;
    $this->methods = $methods;

    foreach ($this->methods as $method) {
      $this->methodsIds[] = $method->getMethodId();
    }

    $this->addActions();

    return $this;
  }

  private function addActions(): void
  {
    add_action('in_plugin_update_message-' . Bootstrap::SLUG . '/' . Bootstrap::SLUG . '.php', [$this, 'upgradeNotice'], 10, 2);
    add_action('after_plugin_row_' . Bootstrap::SLUG . '/' . Bootstrap::SLUG . '.php', [$this, 'upgradeNoticeMultisite'], 10, 2);
    //add_action('admin_notices', [$this, 'adminNotice'], 20);

    add_action('wp_enqueue_scripts', [$this, 'scripts'], 99);
    add_action('admin_enqueue_scripts', [$this, 'adminScripts'], 99);
    add_action('wp_head', [$this, 'frontHead'], 99);

    add_action('woocommerce_review_order_after_shipping', [$this, 'customFields'], 20);
    add_action('woocommerce_checkout_process', [$this, 'customFieldsValidation']);
    add_action('woocommerce_checkout_update_order_meta', [$this, 'customFieldsSave']);
    add_action('woocommerce_after_order_details', [$this, 'customDetails'], 1, 1);
    add_filter('woocommerce_email_order_meta', [$this, 'emailDetails'], 10, 3);

    add_action('add_meta_boxes', function () {
      foreach (wc_get_order_types('order-meta-boxes') as $type) {
        add_meta_box('woocommerce-order-wcpoczta', 'WC Poczta - ' . __('Shipping', Bootstrap::DOMAIN), [$this, 'metaAdmin'], $type, 'normal', 'high');
      }
    }, 30);
  }

  public function upgradeNotice($data, $response)
  {
    if (isset($response->upgrade_notice) && strlen(trim($response->upgrade_notice)) > 0) {
      echo '<br><span class="wc-poczta__update" style="color: #d54e21"><strong>' . __('Important Upgrade Notice', Bootstrap::DOMAIN) . ':</strong><br>' . strip_tags($response->upgrade_notice) . '</span>';
    }
  }

  public function upgradeNoticeMultisite($file, $plugin)
  {
    if (is_multisite() && version_compare($plugin['Version'], $plugin['new_version'], '<')) {
      $wp_list_table = _get_list_table('WP_Plugins_List_Table');
      printf(
        '<tr class="plugin-update-tr"><td colspan="%s" class="plugin-update update-message notice inline notice-warning notice-alt"><div class="update-message"><h4 style="margin: 0; font-size: 14px;">%s</h4>%s</div></td></tr>',
        $wp_list_table->get_column_count(),
        $plugin['Name'],
        wpautop($plugin['upgrade_notice'])
      );
    }
  }

  public function scripts(): void
  {
    if (!function_exists('is_checkout')) {
      return;
    }

    if (!(is_cart() || is_checkout())) {
      return;
    }

    //https://pp-widget.silk-sh.eu/widget/scripts/ppwidget.js
    wp_enqueue_style('wc-poczta', $this->bootstrap->getPluginAsset('css/wc-poczta.min.css'), [], $this->bootstrap->getVersion(), 'all');
    wp_enqueue_style('easypack24', 'https://geowidget.easypack24.net/css/easypack.css', [], $this->bootstrap->getVersion(), 'all');

    wp_enqueue_script('easypack24', 'https://geowidget.easypack24.net/js/sdk-for-javascript.js', [], $this->bootstrap->getVersion(), true);
    wp_enqueue_script('poczta-polska', 'https://mapa.ecommerce.poczta-polska.pl/widget/scripts/ppwidget.js', [], $this->bootstrap->getVersion(), true);
    wp_enqueue_script('wc-poczta', $this->bootstrap->getPluginAsset('js/wc-poczta.min.js'), [], $this->bootstrap->getVersion(), true);
  }

  public function adminScripts(): void
  {
    wp_enqueue_style('wc-poczta', $this->bootstrap->getPluginAsset('css/wc-poczta.min.css'), [], $this->bootstrap->getVersion(), 'all');
    wp_enqueue_script('clipboard-js', 'https://cdn.jsdelivr.net/npm/clipboard-js@0.3.6/clipboard.min.js', [], '0.3.6', true);
    wp_enqueue_script('wc-poczta', $this->bootstrap->getPluginAsset('js/wc-poczta-admin.min.js'), [], $this->bootstrap->getVersion(), true);
  }

  public function frontHead(): void
  {
    if (!function_exists('is_checkout')) {
      return;
    }

    if (!(is_cart() || is_checkout())) {
      return;
    }

    echo '<script type="text/javascript">const WCPOCZTA = ' . json_encode([
      'uri' => 'https://rdev.cc/',
      'prefix' => Bootstrap::PREFIX,
      'domain' => Bootstrap::DOMAIN,
      'version' => $this->bootstrap->getVersion(),
      'methods' => $this->methodsIds,
    ], JSON_UNESCAPED_UNICODE) . '</script>';
  }

  public function adminNotice(): void
  {
    $this->bootstrap->getPluginView('notice');
  }

  public function customFields(): void
  {
    if (!function_exists('is_checkout')) {
      return;
    }

    if (!is_checkout()) {
      return;
    }

    $shippingMethods = WC()->session->get('chosen_shipping_methods');

    if (!is_array($shippingMethods)) {
      return;
    }

    if (count($shippingMethods) < 1) {
      return;
    }

    $methodId = array_shift($shippingMethods);

    if (!in_array($methodId, $this->methodsIds)) {
      return;
    }

    $selectedMethod = $this->getShippingMethod($methodId);
    $currentAddress = trim(WC()->checkout->get_value('wc-poczta__input--address') . ' ' . WC()->checkout->get_value('wc-poczta__input--city'));
    $buttonDataSettings = '';
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
        } else if (!in_array($key, ['wc_poczta_tip', 'free_above', 'free_enable'])) {
          $buttonDataSettings .= ' ' . 'data-s-' . $key . '="' . $value . '"';
        }
      }
    }

    /** @var WC_Theme */
    $theme = wp_get_theme();
    $containerPre = '<tr class="wc-poczta wc-poczta-select-point"><td colspan="2">';
    $containerSuf = '</td></tr>';

    if ('Storefront' == $theme->Name) {
      $containerPre = '<tr class="wc-poczta wc-poczta-select-point"><th colspan="2">';
      $containerSuf = '</th></tr>';
    }

    $html = $containerPre;
    $html .= '<div class="wc-poczta__container ' . $methodId . '">';
    $html .= '<p class="form-row form-row-wide"><button type="button" class="button wc-poczta__button" data-method="' . $methodId . '"' . $buttonDataSettings . '>' . __('Select a pickup point', Bootstrap::DOMAIN) . '</button></p>';

    foreach ($hiddenFields as $field) {
      $html .= '<input type="hidden" name="' . $field . '" id="' . $field . '" value="" data-kpxc-id="' . $field . '">';
    }

    $html .= '<p class="form-row form-row-wide wc-poczta-input"><input disabled="disabled" type="text" class="input-text wc-poczta__input" name="wc-poczta__input--carrier" id="wc-poczta__input--carrier" placeholder="' . __('Pickup point name', Bootstrap::DOMAIN) . '" value="' . WC()->checkout->get_value('wc-poczta__input--name') . '"></p>';
    $html .= '<p class="form-row form-row-wide wc-poczta-input"><input disabled="disabled" type="text" class="input-text wc-poczta__input" name="wc-poczta__input--carrier_address" id="wc-poczta__input--carrier_address" placeholder="' . __('Pickup point address', Bootstrap::DOMAIN) . '" value="' . $currentAddress . '"></p>';

    $html .= '</div>';
    $html .= $containerSuf;

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

  public function customFieldsSave(int $orderId): void
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

  /**
   * @param Automattic\WooCommerce\Admin\Overrides\Order $order
   */
  public function customDetails(WC_Order $order): void
  {
    $orderId = $order->get_id();
    $wcpId = get_post_meta($orderId, '_wcpoczta_id', true);

    if (empty($wcpId)) {
      return;
    }

    $this->bootstrap->getPluginView('customer', ['id' => $orderId, 'wcpId' => $wcpId, 'order' => $order, 'weight' => $this->getTotalWeight($order)]);
  }

  public function emailDetails($order, $sentToAdmin, $plainText)
  {
    $orderId = $order->get_id();
    $wpcId = get_post_meta($orderId, '_wcpoczta_id', true);

    if (empty($wpcId)) {
      return;
    }

    $weight = $this->getTotalWeight($order);
    $html = '';

    if ($plainText === false) {
      $html .= '<h2>' . __('Delivery to the pickup point', Bootstrap::DOMAIN) . '</h2>';

      $html .= '<p><strong>' . __('Pickup point', Bootstrap::DOMAIN) . ':</strong> ' . get_post_meta($orderId, '_wcpoczta_name', true);
      $html .= '<br><strong>' . __('Phone', Bootstrap::DOMAIN) . ':</strong> ' . $order->get_billing_phone();
      if($weight > 0) {
        $html .= '<br><strong>' . __('Weight', Bootstrap::DOMAIN) . ':</strong> ' . $weight . ' ' . get_option('woocommerce_weight_unit');
      }
      $html .= '</p>';

      $html .= '<p><strong>' . __('Pickup point address', Bootstrap::DOMAIN) . ':</strong>';
      $html .= '<br>' . get_post_meta($orderId, '_wcpoczta_address', true);
      $html .= '<br>' . get_post_meta($orderId, '_wcpoczta_zipcode', true);
      $html .= '<br>' . get_post_meta($orderId, '_wcpoczta_city', true);
      $html .= '</p>';
    } else {
      $html .= __('Delivery to the pickup point', Bootstrap::DOMAIN) . PHP_EOL;
      $html .= __('Pickup point', Bootstrap::DOMAIN) . ' - ' . get_post_meta($orderId, '_wcpoczta_name', true) . PHP_EOL . PHP_EOL;

      $html .= __('Pickup point address', Bootstrap::DOMAIN) . ':' . PHP_EOL;
      $html .= get_post_meta($orderId, '_wcpoczta_address', true) . PHP_EOL;
      $html .= get_post_meta($orderId, '_wcpoczta_zipcode', true) . PHP_EOL;
      $html .= get_post_meta($orderId, '_wcpoczta_city', true);
    }

    echo $html;
  }

  public function metaAdmin(?WP_Post $post): void
  {
    $shippingId = '';
    $wcpId = get_post_meta($post->ID, '_wcpoczta_id', true);
    $order = wc_get_order($post->ID);

    $__bc = $this->__backwardCompatibility($post, $order);

    if (!empty($__bc)) {
      echo $__bc;

      return;
    }

    if (empty($wcpId)) {
      echo '<div class="wc-poczta-order">' . __('Pickup point has not been selected as shipping method.', Bootstrap::DOMAIN) . '</div>';

      return;
    }

    $shippingMethods = $order->get_shipping_methods();

    if (is_array($shippingMethods)) {
      foreach ($shippingMethods as $shippingMethod) {
        $shippingId = $shippingMethod->get_method_id();
      }
    }

    $this->bootstrap->getPluginView('summary', ['methodId' => $shippingId, 'order' => $order, 'post' => $post, 'weight' => $this->getTotalWeight($order)]);
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

  /**
   * @param \Automattic\WooCommerce\Admin\Overrides\Order $order
   */
  private function getTotalWeight($order): float
  {
    $weight = 0.0;

    foreach ($order->get_items() as $productId => $product) {
      $quantity = $product->get_quantity();

      $product = $product->get_product();
      $productWeight = $product->get_weight();

      if (!empty($productWeight)) {
        $weight += floatval($productWeight * $quantity);
      }
    }

    return (float) $weight;
  }

  /**
   * Left depending on future needs
   * @deprecated Since version 1.2.0
   * @param WC_Shipping_Method $method
   */
  private function isSelectedMethod($method, int $index): bool
  {
    $choosenMethod = WC()->session->chosen_shipping_methods[$index];

    return false !== strpos($choosenMethod, $method->method_id);
  }

  /**
   * Returns the HTML value for pick points from the previous plugin version.
   * This feature will be removed in the future.
   * @deprecated Since version 1.2.0
   * @param \Automattic\WooCommerce\Admin\Overrides\Order $order
   */
  private function __backwardCompatibility(?WP_Post $post, $order): ?string
  {
    $easypackPoint = get_post_meta($post->ID, '_rdev_sp_easypack_point', true);
    $pocztaPoint = get_post_meta($post->ID, '_rdev_sp_poczta_pni', true);

    if (!empty($easypackPoint)) {
      return $this->bootstrap->getPluginView('summary-depracted', ['type' => 'easypack', 'post' => $post, 'order' => $order, 'weight' => $this->getTotalWeight($order)], true);
    }

    if (!empty($pocztaPoint)) {
      return $this->bootstrap->getPluginView('summary-depracted', ['type' => 'poczta', 'post' => $post, 'order' => $order, 'weight' => $this->getTotalWeight($order)], true);
    }

    return null;
  }
}
