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

    if ( !class_exists( 'RDEV_SELFPICKUP_EASYPACK' ) )
	{
        class RDEV_SELFPICKUP_EASYPACK
        {
            public function __construct()
            {
                /** Register custom fields **/
                add_action( 'woocommerce_after_shipping_rate', array( $this, 'CustomFields'), 20, 2 );
                add_action( 'woocommerce_checkout_process', array( $this, 'CustomFieldsValidation') );

                /** Custom meta **/
                add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'OrderMeta') );
                add_action( 'woocommerce_order_details_after_customer_details', array($this, 'OrderMetaDisplay'), 10, 1);
                add_action( 'woocommerce_admin_order_data_after_order_details',  array($this, 'OrderMetaSummary') );
            }

            public static function CustomFields( $method, $index )
            {
                if( !is_checkout() || $method->method_id != 'rdev_sp_easypack' )
                    return;
                    
                $chosen_method_id = WC()->session->chosen_shipping_methods[ $index ];
                if( strpos($chosen_method_id, 'rdev_sp_easypack') !== false )
                {
                    echo '<div class="rdev_sp_easypack-container">';
                    echo '<button id="rdev_sp_easypack-select" type="button" class="button wcpoczta-button rdev_sp_easypack-select">' . __('Select a pickup point', RDEV_SELFPICKUP_DOMAIN) . '</button>';

                    echo '<input type="hidden" name="rdev_sp_easypack_point" id="rdev_sp_easypack_point" value="" data-kpxc-id="rdev_sp_easypack_point">';
                    echo '<input type="hidden" name="rdev_sp_easypack_type" id="rdev_sp_easypack_type" value="" data-kpxc-id="rdev_sp_easypack_type">';
                    echo '<input type="hidden" name="rdev_sp_easypack_address1" id="rdev_sp_easypack_address1" value="" data-kpxc-id="rdev_sp_easypack_address1">';
                    echo '<input type="hidden" name="rdev_sp_easypack_address2" id="rdev_sp_easypack_address2" value="" data-kpxc-id="rdev_sp_easypack_address2">';
                    echo '<input type="hidden" name="rdev_sp_easypack_location24" id="rdev_sp_easypack_location24" value="" data-kpxc-id="rdev_sp_easypack_location24">';
                    echo '<input type="hidden" name="rdev_sp_easypack_zipcode" id="rdev_sp_easypack_zipcode" value="" data-kpxc-id="rdev_sp_easypack_zipcode">';
                    echo '<input type="hidden" name="rdev_sp_easypack_province" id="rdev_sp_easypack_province" value="" data-kpxc-id="rdev_sp_easypack_province">';

                    woocommerce_form_field( 'rdev_sp_easypack_carrier_name' , array(
                        'type'          => 'text',
                        'class'         => array('form-row-wide carrier-name woo-poczta-input'),
                        'required'      => true,
                        'placeholder'   => __( 'Pickup point name', RDEV_SELFPICKUP_DOMAIN ),
                    ), WC()->checkout->get_value( 'rdev_sp_easypack_carrier_name' ));

                    woocommerce_form_field( 'rdev_sp_easypack_carrier_address' , array(
                        'type'          => 'text',
                        'class'         => array('form-row-wide carrier-address woo-poczta-input'),
                        'required'      => true,
                        'placeholder'   => __( 'Pickup point address', RDEV_SELFPICKUP_DOMAIN ),
                    ), WC()->checkout->get_value( 'rdev_sp_easypack_carrier_address' ));

                    echo '</div>';
                }
            }

            public static function CustomFieldsValidation()
            {
                if( isset( $_POST[ 'shipping_method' ] ) )
                {
                    if( !empty( $_POST[ 'shipping_method' ] ) )
                    {
                        if( strpos($_POST[ 'shipping_method' ][ 0 ], 'rdev_sp_easypack') !== false )
                        {
                            if( !isset( $_POST[ 'rdev_sp_easypack_carrier_name' ], $_POST[ 'rdev_sp_easypack_carrier_address' ] ) )
                            {
                                wc_add_notice( __( 'There was an error in the form. No fields with pickup address found', RDEV_SELFPICKUP_DOMAIN ), 'error' );
                            }
                            else
                            {
                                if( trim( $_POST[ 'rdev_sp_easypack_carrier_name' ] ) == '' || trim( $_POST[ 'rdev_sp_easypack_carrier_address' ] ) == '' )
                                {
                                    wc_add_notice( __( 'You must select a pickup point', RDEV_SELFPICKUP_DOMAIN ), 'error' );
                                }
                                else
                                {
                                    if( !isset(
                                        $_POST[ 'rdev_sp_easypack_point' ],
                                        $_POST[ 'rdev_sp_easypack_type' ],
                                        $_POST[ 'rdev_sp_easypack_address1' ],
                                        $_POST[ 'rdev_sp_easypack_address2' ],
                                        $_POST[ 'rdev_sp_easypack_location24' ],
                                        $_POST[ 'rdev_sp_easypack_zipcode' ],
                                        $_POST[ 'rdev_sp_easypack_province' ]
                                    ))
                                    {
                                        wc_add_notice( sprintf( __( '\'%s\' pickup point is invalid. Form error?', RDEV_SELFPICKUP_DOMAIN ), sanitize_text_field( $_POST[ 'rdev_sp_easypack_carrier_name' ] ) ), 'error' );
                                    }
                                    else
                                    {
                                        //success?
                                    }
                                }
                            }
                        }
                    }
                }
            }

            public static function OrderMeta( $order_id )
            {
                if( isset( $_POST[ 'shipping_method' ] ) )
                {
                    if( !empty( $_POST[ 'shipping_method' ] ) )
                    {
                        if( strpos($_POST[ 'shipping_method' ][ 0 ], 'rdev_sp_easypack') !== false )
                        {
                            if( isset( $_POST['rdev_sp_easypack_point'] ) )
                                if( !empty( $_POST['rdev_sp_easypack_point'] ) )
                                    update_post_meta( $order_id, '_rdev_sp_easypack_point', sanitize_text_field( $_POST['rdev_sp_easypack_point'] ) );

                            if( isset( $_POST['rdev_sp_easypack_type'] ) )
                                if( !empty( $_POST['rdev_sp_easypack_type'] ) )
                                    update_post_meta( $order_id, '_rdev_sp_easypack_type', sanitize_text_field( $_POST['rdev_sp_easypack_type'] ) );

                            if( isset( $_POST['rdev_sp_easypack_address1'] ) )
                                if( !empty( $_POST['rdev_sp_easypack_address1'] ) )
                                    update_post_meta( $order_id, '_rdev_sp_easypack_address1', sanitize_text_field( $_POST['rdev_sp_easypack_address1'] ) );

                            if( isset( $_POST['rdev_sp_easypack_address2'] ) )
                                if( !empty( $_POST['rdev_sp_easypack_address2'] ) )
                                    update_post_meta( $order_id, '_rdev_sp_easypack_address2', sanitize_text_field( $_POST['rdev_sp_easypack_address2'] ) );

                            if( isset( $_POST['rdev_sp_easypack_location24'] ) )
                                if( !empty( $_POST['rdev_sp_easypack_location24'] ) )
                                    update_post_meta( $order_id, '_rdev_sp_easypack_location24', sanitize_text_field( $_POST['rdev_sp_easypack_location24'] ) );

                            if( isset( $_POST['rdev_sp_easypack_zipcode'] ) )
                                if( !empty( $_POST['rdev_sp_easypack_zipcode'] ) )
                                    update_post_meta( $order_id, '_rdev_sp_easypack_zipcode', sanitize_text_field( $_POST['rdev_sp_easypack_zipcode'] ) );

                            if( isset( $_POST['rdev_sp_easypack_province'] ) )
                                if( !empty( $_POST['rdev_sp_easypack_province'] ) )
                                    update_post_meta( $order_id, '_rdev_sp_easypack_province', sanitize_text_field( $_POST['rdev_sp_easypack_province'] ) );
                        }
                    }
                }
            }

            /**
            * OrderMetaDisplay
            * HTML to display in checkout and order summary for customer
            *
            * @access   public
            */
            public function OrderMetaDisplay( $order_id )
            {
                    $order = wc_get_order( $order_id );
                    $order_id = $order->get_id();

                    $data_point = get_post_meta( $order_id, '_rdev_sp_easypack_point', true );
                    if( trim( $data_point ) == '')
                        return;

                    $html = '<div id="woo-poczta-summary">';
                    $html .= '<h3>' . __( 'Shipping - Self Pickup', RDEV_SELFPICKUP_DOMAIN ) . '</h3>';
                    $html .= '<p>' . __( 'You have decided to collect your package at the pickup point.<br/>Here are the pickup point details:', RDEV_SELFPICKUP_DOMAIN ) . '</p>';

                    $html .= '<table style="width:100%">';
                    $html .= '<tr class="wcpoczta-tr-border"><td><strong>' . __( 'Point name', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td><strong>' . __('Parcel locker', RDEV_SELFPICKUP_DOMAIN ) . ' ' . $data_point . '</strong></i></td></tr>';
                    $html .= '<tr class="wcpoczta-tr-border"><td><strong>' . __( 'Address', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . get_post_meta( $order_id, '_rdev_sp_easypack_address2', true ) . ', ' . get_post_meta( $order_id, '_rdev_sp_easypack_address1', true ) . '</td></tr>';
                    $html .= '<tr class="wcpoczta-tr-border"><td><strong>' . __( 'Phone', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . $order->get_billing_phone() . '</td></tr>';
                    $html .= '</table><div>';

                    echo $html;
            }

            public static function OrderMetaSummary( $order )
            {
                $order_id = $order->get_id();
                $point_name = get_post_meta( $order_id, '_rdev_sp_easypack_point', true );

                if( trim( $point_name ) == '')
                    return;

                $pickup_data = array(
                    'name'      => $point_name,
                    'url'       => admin_url('admin-ajax.php'),
                    'address1'  => get_post_meta( $order_id, '_rdev_sp_easypack_address1', true ),
                    'address2'  => get_post_meta( $order_id, '_rdev_sp_easypack_address2', true ),
                    'location24'=> get_post_meta( $order_id, '_rdev_sp_easypack_location24', true ),
                    'zipcode'   => get_post_meta( $order_id, '_rdev_sp_easypack_zipcode', true ),
                    'province'  => get_post_meta( $order_id, '_rdev_sp_easypack_province', true ),

                    'phone'     => $order->get_billing_phone()
                );

                $html = '<hr style="width: 100%;margin-top:15px;margin-bottom:15px;border:0;background:transparent;"/>';
                $html .= '<h3>' . __( 'Shipping - Self Pickup (InPost)', RDEV_SELFPICKUP_DOMAIN ) . '</h3>';
                $html .= '<p style="margin: 0;font-weight: 400;line-height: 1.6em;font-size: 12px;">' . __( 'Customer has chosen to ship to a pickup point', RDEV_SELFPICKUP_DOMAIN ) . '.</p>';
                $html .= '<hr />';

                $html .= '<table style="width:100%">';
                $html .= '<tr><td><strong>' . __( 'Point name', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . $pickup_data['name'] . '</td></tr>';
                $html .= '<tr><td><strong>' . __( 'Address', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . $pickup_data['address1'] . ', ' . $pickup_data['address2'] . '</td></tr>';
                $html .= '<tr><td><strong>' . __( 'Opened 24/7', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . ($pickup_data['location24'] == 'true' ? __( 'Yes', RDEV_SELFPICKUP_DOMAIN ) : __( 'No', RDEV_SELFPICKUP_DOMAIN ) ) . '</td></tr>';
                $html .= '<tr><td><strong>' . __( 'Zip-Code', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . $pickup_data['zipcode'] . '</td></tr>';
                $html .= '<tr><td><strong>' . __( 'Province', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . $pickup_data['province'] . '</td></tr>';
                $html .= '<tr><td><strong>' . __( 'Phone', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . $pickup_data['phone'] . '</td></tr>';
                $html .= '</table>';

                $html .= '<a type="button" href="https://inpost.pl/SzybkieNadania/" target="_blank" rel="noopener nofollow" class="button button-primary" style="margin-top:5px;width:100%;text-align:center;">' . __( 'Open the InPost carrier website', RDEV_SELFPICKUP_DOMAIN ) . '</a>';
                echo $html;
            }
        }
    }
