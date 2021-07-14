/**
 * @package    WordPress
 * @subpackage WC Poczta - Self Pickup with WooCommerce
 *
 * @copyright  Copyright (c) 2020-2021, Leszek Pomianowski
 * @link       https://rdev.cc/
 * @license    MPL-2.0 https://opensource.org/licenses/MPL-2.0
 *
 * @see https://docs.inpost24.com/pages/viewpage.action?pageId=7798862
 * @see https://odbiorwpunkcie.poczta-polska.pl/wp-content/uploads/2020/05/Instrukcja-integracji-05_2020.pdf
 */

class WC_POCZTA {
  constructor() {
    if (this.constructor !== WC_POCZTA) {
      throw new Error("Subclassing is not allowed");
    }
    const WC_BUTTON = document.querySelector(".wc-poczta__button");

    if (!WC_BUTTON) {
      return;
    }

    document.addEventListener("click", function (e) {
      if (e.target && e.target.classList.contains("wc-poczta__button")) {
        e.preventDefault();
        WC_POCZTA.buttonClick(e);
      }
    });
  }

  static buttonClick(e) {
    const DATASET = e.target.dataset;

    if (!DATASET.hasOwnProperty("method")) {
      return;
    }

    if (DATASET.method == "wc_poczta_easy_pack") {
      WC_POCZTA.showEasyPack(DATASET);
    }

    if (DATASET.method == "wc_poczta_poczta") {
      WC_POCZTA.showPoczta(DATASET);
    }
  }

  static getAddress() {
    let shipping = {};
    let returnAddress = "";

    if (
      null !==
      document.querySelector('input[name="ship_to_different_address"]:checked')
    ) {
      shipping = {
        code: document.querySelector('input[name="shipping_postcode"]').value,
        city: document.querySelector('input[name="shipping_city"]').value,
        street1: document.querySelector('input[name="shipping_address_1"]')
          .value,
        street2: document.querySelector('input[name="shipping_address_2"]')
          .value,
      };
    } else {
      shipping = {
        code: document.querySelector('input[name="billing_postcode"]').value,
        city: document.querySelector('input[name="billing_city"]').value,
        street1: document.querySelector('input[name="billing_address_1"]')
          .value,
        street2: document.querySelector('input[name="billing_address_2"]')
          .value,
      };
    }

    if (undefined !== shipping.street1) {
      returnAddress += shipping.street1;
    }

    if (undefined !== shipping.street2) {
      returnAddress += " " + shipping.street2;
    }

    if (undefined !== shipping.code) {
      returnAddress += " " + shipping.code;
    }

    if (undefined !== shipping.city) {
      returnAddress += " " + shipping.city;
    }

    return returnAddress;
  }

  static showEasyPack(dataset) {
    let pGeolocation = false;

    if (dataset.hasOwnProperty("sGeolocation_enabled")) {
      if ("yes" === dataset.sGeolocation_enabled) {
        pGeolocation = true;
      }
    }

    let pPoints = [];
    if (dataset.hasOwnProperty("sPoints")) {
      pPoints = dataset.sPoints.toLowerCase().split(";");
    } else {
      pPoints = ["pop", "parcel_locker"];
    }

    easyPack.init({
      defaultLocale: "pl",
      mapType: "osm",
      searchType: "osm",
      langSelection: false,
      filters: false,
      apiEndpoint: "https://api-pl-points.easypack24.net/v1",
      display: { showTypesFilters: false, showSearchBar: true },
      points: {
        types: pPoints,
      },
      map: {
        initialTypes: pPoints,
        useGeolocation: pGeolocation,
      },
    });

    easyPack.modalMap(
      function (point, modal) {
        modal.closeModal();
        WC_POCZTA.updateInputData(
          point,
          point.name,
          point.type,
          point.name,
          point.address.line1,
          point.address_details.post_code,
          point.address_details.city,
          point.address_details.province
        );
      },
      { width: 500, height: 600 }
    );
  }

  static showPoczta(dataset) {
    let pPoints = [];
    if (dataset.hasOwnProperty("sPoints")) {
      pPoints = dataset.sPoints.toUpperCase().split(";");
    } else {
      pPoints = ["POCZTA", "ZABKA", "RUCH"];
    }

    PPWidgetApp.toggleMap({
      callback: WC_POCZTA.callbackPoczta,
      payOnPickup: !1,
      address: WC_POCZTA.getAddress(),
      type: pPoints,
    });
  }

  static callbackPoczta(data) {
    WC_POCZTA.updateInputData(
      data,
      data.pni,
      data.type,
      data.name,
      data.street,
      data.zipCode,
      data.city,
      data.province
    );
  }

  static updateInputData(
    raw,
    id,
    type,
    name,
    address,
    zipcode,
    city,
    province
  ) {
    if (Array.isArray(type)) {
      let newType = "";

      for (let index = 0; index < type.length; index++) {
        newType += (index > 0 ? ";" : "") + type[index];
      }

      type = newType;
    }

    document.querySelector('input[name="wc-poczta__input--raw"]').value =
      JSON.stringify(raw);
    document.querySelector('input[name="wc-poczta__input--id"]').value = id;
    document.querySelector('input[name="wc-poczta__input--type"]').value = type;
    document.querySelector('input[name="wc-poczta__input--name"]').value = name;
    document.querySelector('input[name="wc-poczta__input--city"]').value = city;
    document.querySelector('input[name="wc-poczta__input--address"]').value =
      address;
    document.querySelector('input[name="wc-poczta__input--zipcode"]').value =
      zipcode;
    document.querySelector('input[name="wc-poczta__input--province"]').value =
      province;

    document.querySelector('input[name="wc-poczta__input--name"]').value = name;

    document.querySelector('input[name="wc-poczta__input--carrier"]').value =
      name;
    document.querySelector(
      'input[name="wc-poczta__input--carrier_address"]'
    ).value = address + " " + city;
  }
}

document.addEventListener("DOMContentLoaded", () => {
  return new WC_POCZTA();
});
