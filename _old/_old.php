<?php
/**
 * @package    WordPress
 * @subpackage WC Poczta - Self Pickup with WooCommerce
 *
 * @copyright  Copyright (c) 2020-2021, Leszek Pomianowski
 * @link       https://rdev.cc/
 * @license    https://opensource.org/licenses/MIT
 */

namespace WCPoczta\Code\Core;
defined('ABSPATH') or die('No script kiddies please!');

	/**
	* RDEV_SELFPICKUP
	*
	* @author   Leszek Pomianowski <https://rdev.cc>
	* @access   public
	* @link     https://odbiorwpunkcie.poczta-polska.pl/wp-content/uploads/2020/05/Instrukcja-integracji-05_2020.pdf
	*/
	class Bootstrap
	{
		/**
		* __construct
		* The constructor registers the language domain, filters and other actions
		*
		* @access   public
		*/
		public function __construct()
		{
			/** Language **/
			self::RegisterDomain();

			/** Shipping subclasses */
			self::InitPoczta();
			self::InitEasyPack();

			/** Scripts **/
			self::Header();
			self::RegisterScripts();

			/** Shipping methods **/
			$this->RegisterShipping();
		}

		/**
		* RegisterDomain
		* Adds a plugin translation
		*
		* @access   protected
		*/
		protected static function RegisterDomain()
		{
			/** After load plugins, register domain **/
			add_action( 'plugins_loaded', function()
			{
				load_plugin_textdomain( RDEV_SELFPICKUP_DOMAIN, false, basename( RDEV_SELFPICKUP_PATH ) . '/languages/' );
			});
		}
		
		protected static function InitPoczta()
		{
			( new RDEV_SELFPICKUP_POCZTA() );
		}

		protected static function InitEasyPack()
		{
			( new RDEV_SELFPICKUP_EASYPACK() );
		}

		/**
		* Header
		* Additional styles and data for javascript
		*
		* @access   protected
		*/
		protected static function Header()
		{
			/** Action fired in header **/
			add_action( 'wp_head', function()
			{
				if ( function_exists( 'is_checkout' ) )
				{
					if ( is_cart() || is_checkout() )
					{
						$html = '';

						$html .= '<style>';
						$html .= '.rdev_sp_easypack-modal, div[style=\'display: flex; flex-direction: column; align-items: center; justify-content: center; position: fixed; z-index: 9999999; top: 0px; right: 0px; bottom: 0px; left: 0px;\'] {background: rgba(0, 0, 0, .6);}';
						$html .= '.woo-poczta-input{pointer-events: none;}.wcpoczta-button{width:100%;margin-top:15px;margin-bottom:15px;}.wcpoczta-tr-border{border-bottom: 1px solid #dcd7ca !important;}';
						$html .= '</style>';

						if( class_exists( 'RDEV_WCSP_Poczta' ) && class_exists( 'RDEV_WCSP_EasyPack' ) )
						{
							$WCSP_PocztaConfig = new RDEV_WCSP_Poczta();
							$WCSP_EasyPackConfig = new RDEV_WCSP_EasyPack();

							$rdev_sp_data = array(
								
							);

							$rdev_sp_bool = array(
								'poczta_poczta'   =>    isset( $WCSP_PocztaConfig->settings[ 'poczta_enabled' ] ) ? $WCSP_PocztaConfig->settings[ 'poczta_enabled' ] : true,
								'poczta_zabka'    =>    isset( $WCSP_PocztaConfig->settings[ 'zabka_enabled' ] ) ? $WCSP_PocztaConfig->settings[ 'zabka_enabled' ] : true,
								'poczta_ruch'     =>    isset( $WCSP_PocztaConfig->settings[ 'ruch_enabled' ] ) ? $WCSP_PocztaConfig->settings[ 'ruch_enabled' ] : false,
								'poczta_orlen'    =>    isset( $WCSP_PocztaConfig->settings[ 'orlen_enabled' ] ) ? $WCSP_PocztaConfig->settings[ 'orlen_enabled' ] : false,
								'poczta_automat'  =>    isset( $WCSP_PocztaConfig->settings[ 'poczta_automat_enabled' ] ) ? $WCSP_PocztaConfig->settings[ 'poczta_automat_enabled' ] : false,

								'easypack_pop'    =>    isset( $WCSP_EasyPackConfig->settings[ 'pop_enabled' ] ) ? $WCSP_EasyPackConfig->settings[ 'pop_enabled' ] : false,
								'easypack_locker' =>    isset( $WCSP_EasyPackConfig->settings[ 'parcellocker_enabled' ] ) ? $WCSP_EasyPackConfig->settings[ 'parcellocker_enabled' ] : true,
								'easypack_geo'    =>    isset( $WCSP_EasyPackConfig->settings[ 'geolocation_enabled' ] ) ? $WCSP_EasyPackConfig->settings[ 'geolocation_enabled' ] : false
							);

							$html .= '<script type="text/javascript">let rdev_sp = {';
							
							$c = 0;
							foreach ( $rdev_sp_data as $key => $value )
							{
								$html .= ( $c > 0 ? ', ' : '' ) . $key . ': ' . $value;
								$c++;
							}
							foreach ( $rdev_sp_bool as $key => $value )
							{
								$html .= ( $c > 0 ? ', ' : '' ) . $key . ': ' . self::IsValueTrue( $value );
								$c++;
							}

							$html .= '}</script>';
						}

						echo $html;
					}
				}
			});
		}
		
		/**
		 * RegisterScripts
		 * Additional scripts
		 *
		 * @access   protected
		 */
		protected static function RegisterScripts()
		{
			/** Front page scripts **/
			add_action( 'wp_enqueue_scripts', function()
			{
				if ( function_exists( 'is_checkout' ) )
				{
					if ( is_cart() || is_checkout() )
					{
						wp_enqueue_style( 'cdn-easypack', 'https://geowidget.easypack24.net/css/easypack.css', array(), RDEV_SELFPICKUP_VERSION );
						wp_enqueue_script( 'cdn-easypack', 'https://geowidget.easypack24.net/js/sdk-for-javascript.js', array(), RDEV_SELFPICKUP_VERSION, true);
						
						wp_enqueue_script( 'cdn-poczta', 'https://mapa.ecommerce.poczta-polska.pl/widget/scripts/ppwidget.js', array(), RDEV_SELFPICKUP_VERSION, true);
						wp_enqueue_script( 'wc-poczta',  RDEV_SELFPICKUP_URL . 'js/rdev-selfpickup.js', array(), RDEV_SELFPICKUP_VERSION, true);
					}
				}
			}, 99 );
		}

		
		/**
		 * RegisterShipping
		 * Registration of a new shipping method
		 *
		 * @access   protected
		 */
		protected function RegisterShipping()
		{
			/* If woocommerce active */
			if ( in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
			{
				add_action('woocommerce_shipping_init', function()
				{
					require_once RDEV_SELFPICKUP_PATH . 'assets/woocommerce/shipping-poczta.php' ;
					require_once RDEV_SELFPICKUP_PATH . 'assets/woocommerce/shipping-easypack.php' ;
				});

				/** Add shipping method **/
				add_filter('woocommerce_shipping_methods', function( $methods )
				{
					$methods['rdev_sp_poczta'] = 'RDEV_WCSP_Poczta';
					$methods['rdev_sp_easypack'] = 'RDEV_WCSP_EasyPack';
					return $methods;
				});
				
				/** Validate shipping method **/
				add_action( 'woocommerce_review_order_before_cart_contents', array($this, 'ValidateWeight') , 10 );
				add_action( 'woocommerce_after_checkout_validation', array($this, 'ValidateWeight') , 10 );
			}
		}

		/**
		* ValidateWeight
		* Verify shipping weight
		*
		* @access   public
		*/
		public function ValidateWeight( $posted )
		{
			$packages = WC()->shipping->get_packages();
			$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
			
			//fix
			if( is_array( $chosen_methods ) )
			{
				foreach ( $packages as $i => $package )
				{
					if( strpos( $chosen_methods[ $i ], 'rdev_sp_poczta' ) !== false  )
					{

						//$WCPoczta = new RDEV_WCSP_Poczta();
						//$WCPoczta->Init();
						//var_dump($WCPoczta->settings['weight']);

						//wc_add_notice( 'wybrano poczta', 'error' );
					}
					else if( strpos( $chosen_methods[ $i ], 'rdev_sp_easypack' ) !== false  )
					{
						//wc_add_notice( 'wybrano easypack', 'error' );
					}
					else
					{
						continue;
					}
				}

			}

			

			/*
			if( is_array( $chosen_methods ) &&  )
			{
				foreach ( $packages as $i => $package )
				{
					if ( strpos( $chosen_methods[ $i ], 'rdev_sp_poczta' ) !== false )
					{
						continue;
					}

					$WCPoczta = new RDEV_WCSP_Poczta();
					$weightLimit = isset( $WCPoczta->settings[ 'weight' ] ) ? $WCPoczta->settings[ 'weight' ] : 20;

					$weight = 0;
					foreach ( $package['contents'] as $item_id => $values ) 
					{ 
						$_product = $values['data']; 
						$weight += $_product->get_weight() * (float)$values['quantity'];
					}

					$weight = wc_get_weight( $weight, 'kg' );
					
					if( $weight > $weightLimit )
					{
						$message = sprintf(
							__( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', RDEV_SELFPICKUP_DOMAIN ),
							$weight,
							$weightLimit,
							isset( $WCPoczta->settings[ 'title' ] ) ? $WCPoczta->settings[ 'title' ] : __( 'Self Pickup - Żabka, Orlen, Ruch', RDEV_SELFPICKUP_DOMAIN )
						);
						
						if( !wc_has_notice( $message, 'error' ) )
						{
							wc_add_notice( $message, 'error' );
						}
					}

				}
			}*/
		}
			
		/**
		* IsValueTrue
		* Whether the pickup point is enabled
		*
		* @access   public
		*/
		public static function IsValueTrue( $pickup )
		{
			if( $pickup === 'yes' || $pickup === 'true' || $pickup === true || $pickup === 1 )
				return 'true';
			else
				return 'false';
		}
	}
