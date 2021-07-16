=== Self Pickup with WooCommerce - InPost Paczkomaty, Polish Post, Żabka and Ruch ===
Contributors: RapidDev
Donate link: https://www.paypal.com/paypalme/devcc
Tags: e-commerce, paczkomaty, inpost, poczta, polska, żabka, odbior, punkcie, ruch, orlen, woocommerce
Requires at least: 5.0.0
Tested up to: 5.8.0
Requires PHP: 7.4.1
Stable tag: trunk
License: GPL-3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

Integration of self pickup in the Polish Post offices, Orlen, Żabka, Ruch stores and others.

== Description ==

WC Poczta is a popular open-source plug for easy self pickup which works for over a thousand online stores.

Our plugin is free, flexible, and developed together with the community, thanks to which it provides the functions suited for many stores. The freedom of open-source means you retain full ownership of your store’s content and data forever.

- **Add a interactive map of pickup points** available both for parcel machines and collection points in many shops and post offices.
- **Allow free shipping** which is available in the default options, just set the amount.
- **Customize options** using the add shipping creator. You can change names, amounts, weights, specific types of pickup points and more.
- **Give your customers the convenience** of using modern solutions without compromises.

= Grow your business, do not waste time =

Just install and add shipping options. You don't need to provide license keys, API keys, log in or sign contracts. It just works.

= Own and control your data =

Data about your company and customers is not collected by us. It is also not verified in any external API. 

However, remember to read the privacy policy of [InPost](https://inpost.pl/en/policy) and [Poczta Polska](https://bip.poczta-polska.pl/iinformacja-o-zbieraniu-danych-osobowych/), who provide their maps.

= Contribute =

WC Poczta is developed and supported by RapidDev, the creators of other WordPress solutions that are appreciated by te community. Did you find a bug or have a suggestion? Be sure to let us know!

== Frequently asked questions ==

= Will WC Poczta work with my store? =

We strive to provide an extensive solution that works for most stores. In any case, we recommend you to test the plugin before deploying.

= Can I change the appearance of buttons and form fields? =

Yes! You can do it yourself in [WordPress custom CSS](https://wordpress.com/support/editing-css/), or ask your developer to add WC Poczta styles to your theme.

In any case, you just need to change these classes: .wc-poczta__button & .wc-poczta__input

= Do I have to enable all pickup points? =

In the plugin settings, you can choose which pickup points are enabled.

= Is any data collected about me? =

No. The plugin works exclusively on your site and does not require logging into any API. We, as creators, also do not require you to log in through our services.

In any case, interactive map providers may collect data while selecting a point. Make sure you and your client understand their policies.

= Can the plug detect the weight of the shipped products? =

The plug has a built-in protection against too heavy packages. Most points allow packages up to a maximum of 25kg, and these are the default limits.

= Is this an official plugin? =

No. The plugin was created by a Polish development team based on the official documentation.

= Does WC Poczta come with external solutions? =

Plugin presents official logos of the companies that provide the functionality of pickup point. These logos are used for visualization purposes only and are the property of their respective owners.

During the development process, we also used tools provided by the creators of WooCommerce and external libraries using the Composer. You will find all the necessary policies and licenses in the source code.

== Installation ==

= Minimum Requirements =

* PHP version 7.4.1 or greater (PHP 8.0.8 or greater is recommended)
* WordPress version 5.0.0 or greater (WordPress 5.7.2 or greater is recommended)
* WooCommerce version 5.0.0 or greater (WooCommerce 5.5.1 or greater is recommended)

= Automatic installation =
Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of our plugin, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type our plugin name and click Search Plugins. Once you’ve found it you can view details, such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =
The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =
Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Screenshots ==

1. Self pickup map via InPost Paczkomaty
2. Self pickup map via Poczta Polska
3. Plugin settings
4. Admin order summary
5. Select pickup point in checkout

== Changelog ==

= 1.3.0 =
1. Fixing the weight limit
2. Correct the display of pickup point in order summary
3. Email fields added

= 1.2.0 =
1. Everything is new
2. Lots of bugs added to fix later

= 1.1.0 =
1. Support for Paczkomaty InPost (Parcel Lockers)
2. Shipping classes separated
3. Renaming classes and methods

= 1.0.3 =
1. Translation fixes

= 1.0.2 =
1. VAT value fix

= 1.0.1 =
1. Initial fixes

= 1.0.0 =
1. The plugin was created

== Upgrade Notice ==

= 1.3.0 =
Fixing the weight limit and add custom e-mail fields.

= 1.2.0 =
The new version of the plug is not compatible with the old one. Be careful while updating.