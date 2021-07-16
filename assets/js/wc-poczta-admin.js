/**
 * @package    WordPress
 * @subpackage WC Poczta - Self Pickup with WooCommerce
 *
 * @copyright  Copyright (c) 2020-2021, Leszek Pomianowski
 * @link       https://rdev.cc/
 * @license    GPL-3.0 https://www.gnu.org/licenses/gpl-3.0.txt
 */

let wcPocztaClipboard = new ClipboardJS(".wc-poczta-order__clipboard");
let __wcpoczta__thread = 0;

wcPocztaClipboard.on("success", function (e) {
  const ALERT = document.querySelector(".wc-poczta-alert");
  ALERT.classList.remove("--hidden");

  __wcpoczta__thread = Math.floor(Math.random() * 1048576);
  let __wcpoczta__thread_local = __wcpoczta__thread;

  setTimeout(function () {
    if (__wcpoczta__thread !== __wcpoczta__thread_local) {
      return;
    }
    ALERT.classList.add("--hidden");
  }, 3000);
});
