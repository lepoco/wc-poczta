<?php defined('ABSPATH') or die('No script kiddies please!');
/**
 * @package    WordPress
 * @subpackage RDEV Self Pickup
 *
 * @author     RapidDev | Polish technology company
 * @copyright  Copyright (c) 2020, RapidDev
 * @link       https://rdev.cc/
 * @license    https://opensource.org/licenses/MIT
 */

	if ( !class_exists( 'RDEV_WCSP_Poczta' ) )
	{
		/**
		*
		* RDEV_WCSP_Poczta
		* @author   Leszek Pomianowski <https://rdev.cc>
		* @access   public
		* @link     https://odbiorwpunkcie.poczta-polska.pl/wp-content/uploads/2020/05/Instrukcja-integracji-05_2020.pdf
		*/
		class RDEV_WCSP_Poczta extends WC_Shipping_Method
		{
			/**
			* __construct
			* The constructor registers the language domain, filters and other actions
			*
			* @access   public
			*/
			public function __construct( $instance_id = 0 )
			{
				$this->id = 'rdev_sp_poczta';
				$this->instance_id = absint( $instance_id );
				$this->domain = RDEV_SELFPICKUP_DOMAIN;
				$this->method_title = __( 'Self Pickup - Polish Post', $this->domain );
				$this->method_description = __( 'Pickup at Żabka, Orlen, Biedronka, Ruch and Poczta Polska', $this->domain );
				
				$this->supports = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);

				$this->Init();
			}

			/**
			* Init
			* Registers all options necessary for the shipping method to work
			*
			* @access   protected
			*/
			protected function Init()
			{
				$this->RegisterSettings();
				$this->RegisterConditions();
				$this->init_settings();

				$this->enabled = $this->get_option( 'enabled', $this->domain );
				$this->title   = $this->get_option( 'title', $this->domain );
				$this->info    = $this->get_option( 'info', $this->domain );
			}

			/**
			* RegisterConditions
			* Enables the requirement for a phone number
			*
			* @access   protected
			*/
			protected function RegisterConditions()
			{
				add_filter( 'woocommerce_billing_fields', function( $address_fields )
				{
					$address_fields['billing_phone']['required'] = true;
					return $address_fields;
				}, 10, 1 );
			}

			/**
			* RegisterSettings
			* Adds settings
			*
			* @access   protected
			*/
			protected function RegisterSettings()
			{
				/*
					DOCUMENTATION 'INSTRUKCJA INTEGRACJI', CHAPTER: 'Typy punktów'

					POCZTA – placówki Poczty Polskiej
					ORLEN – stacje paliw Orlen
					AUTOMAT_POCZTOWY – automaty pocztowe Poczty Polskiej
					RUCH – kioski Ruchu
					ZABKA – sklep Żabka
					FRESHMARKET – sklep Freshmarket
					AUTOMAT_BIEDRONKA - automaty Biedronka (Smartbox)
					AUTOMAT_CARREFOUR - automaty Carrefour (Smartbox)
					AUTOMAT_PLACOWKA - automaty pocztowe Poczty Polskiej (Smartbox)
					AUTOMAT_LEWIATAN – automaty Lewiatan (Smartbox)
					AUTOMAT_SPOLEM – automat Społem (Smartbox)
				*/
				$this->instance_form_fields = array(
					'title' => array(
						'type' => 'text',
						'title' => __( 'Title', RDEV_SELFPICKUP_DOMAIN ),
						'description' => __( 'Title displayed when you select a shipping option.', RDEV_SELFPICKUP_DOMAIN ),
						'default' => __('Self Pickup - Żabka, Orlen, Ruch', RDEV_SELFPICKUP_DOMAIN )
					),
					'cost' => array(
						'title' => __( 'Cost', RDEV_SELFPICKUP_DOMAIN ),
						'type' => 'number',
						'description' => __( 'Primary cost (default is PLN 10.94 + 23% VAT)', RDEV_SELFPICKUP_DOMAIN ),
						'default' => 10.94
					),
					'weight' => array(
						'title' => __( 'Weight (kg)', RDEV_SELFPICKUP_DOMAIN ),
						'type' => 'number',
						'description' => __( 'Maximum allowed weight (default is 20kg)', RDEV_SELFPICKUP_DOMAIN ),
						'default' => 20
					),
					'poczta_enabled' => array(
						'type' => 'checkbox',
						'title' => __( 'Enable pickup in Polish Post offices', RDEV_SELFPICKUP_DOMAIN ),
						'description' => __( 'If you select this option, Polish Post offices will appear among the collection points.', RDEV_SELFPICKUP_DOMAIN ),
						'default' => 'yes'
					),
					'zabka_enabled' => array(
						'type' => 'checkbox',
						'title' => __( 'Enable pickup in Żabka', RDEV_SELFPICKUP_DOMAIN ),
						'description' => __( 'If you select this option, Żabka stores will appear among the collection points.', RDEV_SELFPICKUP_DOMAIN ),
						'default' => 'yes'
					),
					'ruch_enabled' => array(
						'title' => __( 'Enable pickup in Ruch', RDEV_SELFPICKUP_DOMAIN ),
						'type' => 'checkbox',
						'description' => __( 'If you select this option, Ruch stores will appear among the collection points.', RDEV_SELFPICKUP_DOMAIN ),
						'default' => 'yes'
					),
					'orlen_enabled' => array(
						'title' => __( 'Enable pickup in Orlen', RDEV_SELFPICKUP_DOMAIN ),
						'type' => 'checkbox',
						'description' => __( 'If you select this option, Orlen petrol stations will appear among the collection points.', RDEV_SELFPICKUP_DOMAIN ),
						'default' => 'no'
					),
					/*'freshmarket_enabled' => array(
						'title' => __( 'Enable pickup in Freshmarket', RDEV_SELFPICKUP_DOMAIN ),
						'type' => 'checkbox',
						'description' => __( 'If you select this option, Freshmarket stores will appear among the collection points.', RDEV_SELFPICKUP_DOMAIN ),
						'default' => 'no'
					),*/
					'poczta_automat_enabled' => array(
						'title' => __( 'Enable pickup in postal automats', RDEV_SELFPICKUP_DOMAIN ),
						'type' => 'checkbox',
						'description' => __( 'If you select this option, Polish Post postal automats will appear among the collection points.', RDEV_SELFPICKUP_DOMAIN ),
						'default' => 'no'
					)
				);
			}

			/**
			* calculate_shipping
			* Shows the final shipping price on the checkout page
			*
			* @access   public
			*/
			public function calculateShipping( $package = array() )
			{
				$this->add_rate(
					array(
						'id' => $this->id,
						'label' => $this->title,
						'calc_tax'  => 'per_order',
						'cost' => $this->get_option('cost')
					)
				);
			}
		}
	}
