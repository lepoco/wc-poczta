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

	if ( !class_exists( 'RDEV_WCSP_EasyPack' ) )
	{
		/**
		*
		* RDEV_WCSP_EasyPack
		* @author   Leszek Pomianowski <https://rdev.cc>
		* @access   public
		* @link     https://docs.inpost24.com/pages/viewpage.action?pageId=7798862
		*/
		class RDEV_WCSP_EasyPack extends WC_Shipping_Method
		{
			/**
			* __construct
			* The constructor registers the language domain, filters and other actions
			*
			* @access   public
			*/
			public function __construct( $instance_id = 0 )
			{
				$this->id = 'rdev_sp_easypack';
				$this->instance_id = absint( $instance_id );

				$this->method_title = __( 'Self Pickup - InPost Paczkomaty', RDEV_SELFPICKUP_DOMAIN );
				$this->method_description = __( 'Pickup at InPost Paczkomaty', RDEV_SELFPICKUP_DOMAIN );
				
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

				$this->enabled = $this->get_option( 'enabled', RDEV_SELFPICKUP_DOMAIN );
				$this->title   = $this->get_option( 'title', RDEV_SELFPICKUP_DOMAIN );
				$this->info    = $this->get_option( 'info', RDEV_SELFPICKUP_DOMAIN );
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
				$this->instance_form_fields = array(
					'title' => array(
						'type' => 'text',
						'title' => __( 'Title', RDEV_SELFPICKUP_DOMAIN ),
						'description' => __( 'Title displayed when you select a shipping option.', RDEV_SELFPICKUP_DOMAIN ),
						'default' => __('Self Pickup - InPost Paczkomaty', RDEV_SELFPICKUP_DOMAIN )
					),
					'cost' => array(
						'type' => 'number',
						'title' => sprintf( __( 'Cost (Dimensions %s)', RDEV_SELFPICKUP_DOMAIN ), 'A' ),
						'description' => sprintf( __('Primary cost (default is PLN %s + 23&#37; VAT)', RDEV_SELFPICKUP_DOMAIN ), '11.99' ),
						'default' => 11.99
					),
					'weight_a' => array(
						'type' => 'number',
						'title' => sprintf( __( 'Weight (Dimensions %s)', RDEV_SELFPICKUP_DOMAIN ), 'A' ),
						'description' => __( 'Maximum allowed weight (default is 5kg)', RDEV_SELFPICKUP_DOMAIN ),
						'default' => 5
					),
					'cost_b' => array(
						'type' => 'number',
						'title' => sprintf( __( 'Cost (Dimensions %s)', RDEV_SELFPICKUP_DOMAIN ), 'B' ),
						'description' => sprintf( __('Primary cost (default is PLN %s + 23&#37;VAT)', RDEV_SELFPICKUP_DOMAIN ), '12.99' ),
						'default' => 12.99
					),
					'weight_b' => array(
						'type' => 'number',
						'title' => sprintf( __( 'Weight (Dimensions %s)', RDEV_SELFPICKUP_DOMAIN ), 'B' ),
						'description' => __( 'Maximum allowed weight (default is 10kg)', RDEV_SELFPICKUP_DOMAIN ),
						'default' => 10
					),
					'cost_c' => array(
						'type' => 'number',
						'title' => sprintf( __( 'Cost (Dimensions %s)', RDEV_SELFPICKUP_DOMAIN ), 'C' ),
						'description' => sprintf( __('Primary cost (default is PLN %s + 23&#37; VAT)', RDEV_SELFPICKUP_DOMAIN ), '14.99' ),
						'default' => 14.99
					),
					'weight_c' => array(
						'type' => 'number',
						'title' => sprintf( __( 'Weight (Dimensions %s)', RDEV_SELFPICKUP_DOMAIN ), 'C' ),
						'description' => __( 'Maximum allowed weight (default is 25kg)', RDEV_SELFPICKUP_DOMAIN ),
						'default' => 25
					),
					'parcellocker_enabled' => array(
						'type' => 'checkbox',
						'title' => __( 'Enable pickup in Parcel Lockers', RDEV_SELFPICKUP_DOMAIN ),
						'description' => __( 'If you select this option, pickup will be possible at Parcel Lockers.', RDEV_SELFPICKUP_DOMAIN ),
						'default' => 'yes'
                    ),
                    'pop_enabled' => array(
						'type' => 'checkbox',
						'title' => __( 'Enable pickup in POP', RDEV_SELFPICKUP_DOMAIN ),
						'description' => __( 'If you select this option, pickup will be possible at Parcel Collection Points.', RDEV_SELFPICKUP_DOMAIN ),
						'default' => 'yes'
					),
					'geolocation_enabled' => array(
						'type' => 'checkbox',
						'title' => __( 'Enable geolocation', RDEV_SELFPICKUP_DOMAIN ),
						'description' => __( 'If you enable this option, the pickup point will be proposed based on your geolocation.', RDEV_SELFPICKUP_DOMAIN ),
						'default' => 'yes'
					),
				);
			}

			/**
			* calculate_shipping
			* Shows the final shipping price on the checkout page
			*
			* @access   public
			*/
			public function calculate_shipping( $package = array() )
			{
				$weight = 0;
				
				$cost = -1;

				foreach ($package['contents'] as $item_id => $values)
				{
					$_product = $values['data'];
					$weight = $weight + $_product->get_weight() * $values['quantity'];
				}

				if($weight <= $this->get_option('weight_a'))
					$cost = $this->get_option('cost');
				else if($weight <= $this->get_option('weight_b'))
					$cost = $this->get_option('cost_b');
				else if($weight < $this->get_option('weight_c'))
					$cost = $this->get_option('cost_c');
				else
					$cost = -1; //too high

				$this->add_rate(
					array(
						'id'        => $this->get_rate_id(),
						'label'     => $this->title,
						'calc_tax'  => 'per_order',
						'cost'      => $this->get_option('cost')
					)
				);
			}
		}
	}
