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

    if ( !class_exists( 'RDEV_SELFPICKUP_POCZTA' ) )
	{
        class RDEV_SELFPICKUP_POCZTA
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
                if( !is_checkout() )
                    return;

                if( $method->method_id != 'rdev_sp_poczta' )
                    return;

                $chosen_method_id = WC()->session->chosen_shipping_methods[ $index ];

                if( strpos($chosen_method_id, 'rdev_sp_poczta') !== false )
                {
                    echo '<div class="rdev_sp_poczta-container">';
                    echo '<button id="rdev_sp_poczta-select" type="button" class="button rdev_sp_poczta-select wcpoczta-button">' . __('Select a pickup point', RDEV_SELFPICKUP_DOMAIN) . '</button>';

                    echo '<input type="hidden" name="rdev_sp_poczta_pni" id="rdev_sp_poczta_pni" value="" data-kpxc-id="rdev_sp_poczta_pni">';
                    echo '<input type="hidden" name="rdev_sp_poczta_type" id="rdev_sp_poczta_type" value="" data-kpxc-id="rdev_sp_poczta_type">';
                    echo '<input type="hidden" name="rdev_sp_poczta_name" id="rdev_sp_poczta_name" value="" data-kpxc-id="rdev_sp_poczta_name">';
                    echo '<input type="hidden" name="rdev_sp_poczta_city" id="rdev_sp_poczta_city" value="" data-kpxc-id="rdev_sp_poczta_city">';
                    echo '<input type="hidden" name="rdev_sp_poczta_street" id="rdev_sp_poczta_street" value="" data-kpxc-id="rdev_sp_poczta_street">';
                    echo '<input type="hidden" name="rdev_sp_poczta_zipcode" id="rdev_sp_poczta_zipcode" value="" data-kpxc-id="rdev_sp_poczta_zipcode">';
                    echo '<input type="hidden" name="rdev_sp_poczta_province" id="rdev_sp_poczta_province" value="" data-kpxc-id="rdev_sp_poczta_province">';

                    woocommerce_form_field( 'rdev_sp_poczta_carrier_name' , array(
                        'type'          => 'text',
                        'class'         => array('form-row-wide carrier-name woo-poczta-input'),
                        'required'      => true,
                        'placeholder'   => __( 'Pickup point name', RDEV_SELFPICKUP_DOMAIN ),
                    ), WC()->checkout->get_value( 'rdev_sp_poczta_carrier_name' ));

                    woocommerce_form_field( 'rdev_sp_poczta_carrier_address' , array(
                        'type'          => 'text',
                        'class'         => array('form-row-wide carrier-address woo-poczta-input'),
                        'required'      => true,
                        'placeholder'   => __( 'Pickup point address', RDEV_SELFPICKUP_DOMAIN ),
                    ), WC()->checkout->get_value( 'rdev_sp_poczta_carrier_address' ));

                    echo '</div>';
                }
            }

            public static function CustomFieldsValidation()
            {
                if( isset( $_POST[ 'shipping_method' ] ) )
                {
                    if( !empty( $_POST[ 'shipping_method' ] ) )
                    {
                        if( strpos($_POST[ 'shipping_method' ][ 0 ], 'rdev_sp_poczta') !== false )
                        {
                            if( !isset( $_POST[ 'rdev_sp_poczta_carrier_name' ], $_POST[ 'rdev_sp_poczta_carrier_address' ] ) )
                            {
                                wc_add_notice( __( 'There was an error in the form. No fields with pickup address found', RDEV_SELFPICKUP_DOMAIN ), 'error' );
                            }
                            else
                            {
                                if( trim( $_POST[ 'rdev_sp_poczta_carrier_name' ] ) == '' || trim( $_POST[ 'rdev_sp_poczta_carrier_address' ] ) == '' )
                                {
                                    wc_add_notice( __( 'You must select a pickup point', RDEV_SELFPICKUP_DOMAIN ), 'error' );
                                }
                                else
                                {
                                    if( !isset(
                                        $_POST[ 'rdev_sp_poczta_pni' ],
                                        $_POST[ 'rdev_sp_poczta_type' ],
                                        $_POST[ 'rdev_sp_poczta_name' ],
                                        $_POST[ 'rdev_sp_poczta_city' ],
                                        $_POST[ 'rdev_sp_poczta_street' ],
                                        $_POST[ 'rdev_sp_poczta_zipcode' ],
                                        $_POST[ 'rdev_sp_poczta_province' ]
                                    ))
                                    {
                                        wc_add_notice( sprintf( __( '\'%s\' pickup point is invalid. Form error?', RDEV_SELFPICKUP_DOMAIN ), sanitize_text_field( $_POST[ 'rdev_sp_poczta_carrier_name' ] ) ), 'error' );
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
                        if( strpos($_POST[ 'shipping_method' ][ 0 ], 'rdev_sp_poczta') !== false )
                        {
                            if( isset( $_POST['rdev_sp_poczta_pni'] ) )
                                if( !empty( $_POST['rdev_sp_poczta_pni'] ) )
                                    update_post_meta( $order_id, '_rdev_sp_poczta_pni', sanitize_text_field( $_POST['rdev_sp_poczta_pni'] ) );

                            if( isset( $_POST['rdev_sp_poczta_type'] ) )
                                if( !empty( $_POST['rdev_sp_poczta_type'] ) )
                                    update_post_meta( $order_id, '_rdev_sp_poczta_type', sanitize_text_field( $_POST['rdev_sp_poczta_type'] ) );

                            if( isset( $_POST['rdev_sp_poczta_name'] ) )
                                if( !empty( $_POST['rdev_sp_poczta_name'] ) )
                                    update_post_meta( $order_id, '_rdev_sp_poczta_name', sanitize_text_field( $_POST['rdev_sp_poczta_name'] ) );

                            if( isset( $_POST['rdev_sp_poczta_city'] ) )
                                if( !empty( $_POST['rdev_sp_poczta_city'] ) )
                                    update_post_meta( $order_id, '_rdev_sp_poczta_city', sanitize_text_field( $_POST['rdev_sp_poczta_city'] ) );

                            if( isset( $_POST['rdev_sp_poczta_street'] ) )
                                if( !empty( $_POST['rdev_sp_poczta_street'] ) )
                                    update_post_meta( $order_id, '_rdev_sp_poczta_street', sanitize_text_field( $_POST['rdev_sp_poczta_street'] ) );

                            if( isset( $_POST['rdev_sp_poczta_zipcode'] ) )
                                if( !empty( $_POST['rdev_sp_poczta_zipcode'] ) )
                                    update_post_meta( $order_id, '_rdev_sp_poczta_zipcode', sanitize_text_field( $_POST['rdev_sp_poczta_zipcode'] ) );

                            if( isset( $_POST['rdev_sp_poczta_province'] ) )
                                if( !empty( $_POST['rdev_sp_poczta_province'] ) )
                                    update_post_meta( $order_id, '_rdev_sp_poczta_province', sanitize_text_field( $_POST['rdev_sp_poczta_province'] ) );
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

                    $data_pni = get_post_meta( $order_id, '_rdev_sp_poczta_pni', true );
                    if( trim( $data_pni ) == '')
                        return;

                    $html = '<div id="woo-poczta-summary">';
                    $html .= '<h3>' . __( 'Shipping - Self Pickup (Polish Post)', RDEV_SELFPICKUP_DOMAIN ) . '</h3>';
                    $html .= '<p>' . __( 'You have decided to collect your package at the pickup point.<br/>Here are the pickup point details:', RDEV_SELFPICKUP_DOMAIN ) . '</p>';

                    $html .= '<table style="width:100%">';
                    $html .= '<tr class="wcpoczta-tr-border"><td><strong>' . __( 'Point name', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . get_post_meta( $order_id, '_rdev_sp_poczta_name', true ) . ' <i>(' . $data_pni . ')</i></td></tr>';
                    $html .= '<tr class="wcpoczta-tr-border"><td><strong>' . __( 'Street', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . get_post_meta( $order_id, '_rdev_sp_poczta_street', true ) . '</td></tr>';
                    $html .= '<tr class="wcpoczta-tr-border"><td><strong>' . __( 'City', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . get_post_meta( $order_id, '_rdev_sp_poczta_zipcode', true ) . ' ' . get_post_meta( $order_id, '_rdev_sp_poczta_city', true ) . '</td></tr>';
                    $html .= '<tr class="wcpoczta-tr-border"><td><strong>' . __( 'Phone', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . $order->get_billing_phone() . '</td></tr>';
                    $html .= '</table><div>';

                    echo $html;
            }

            public static function OrderMetaSummary( $order )
            {
                $order_id = $order->get_id();
                $data_pni = get_post_meta( $order_id, '_rdev_sp_poczta_pni', true );

                if( trim( $data_pni ) == '')
                    return;

                $pickup_data = array(
                    'pni'       => $data_pni,
                    'url'       => admin_url('admin-ajax.php'),
                    'name'      => get_post_meta( $order_id, '_rdev_sp_poczta_name', true ),
                    'type'      => get_post_meta( $order_id, '_rdev_sp_poczta_type', true ),
                    'city'      => get_post_meta( $order_id, '_rdev_sp_poczta_city', true ),
                    'zipcode'   => get_post_meta( $order_id, '_rdev_sp_poczta_zipcode', true ),
                    'street'    => get_post_meta( $order_id, '_rdev_sp_poczta_street', true ),

                    'phone'     => $order->get_billing_phone()
                );

                $html = '<hr style="width: 100%;margin-top:15px;margin-bottom:15px;border:0;background:transparent;"/>';
                $html .= '<h3>' . __( 'Shipping - Self Pickup', RDEV_SELFPICKUP_DOMAIN ) . '</h3>';
                $html .= '<p style="margin: 0;font-weight: 400;line-height: 1.6em;font-size: 12px;">' . __( 'Customer has chosen to ship to a pickup point', RDEV_SELFPICKUP_DOMAIN ) . '.</p>';
                $html .= '<hr />';

                $html .= '<table style="width:100%">';
                $html .= '<tr><td><strong>PNI</strong></td><td>' . $pickup_data['pni'] . '</td></tr>';
                $html .= '<tr><td><strong>' . __( 'Type', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . $pickup_data['type'] . '</td></tr>';
                $html .= '<tr><td><strong>' . __( 'Point name', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . $pickup_data['name'] . '</td></tr>';
                $html .= '<tr><td><strong>' . __( 'Street', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . $pickup_data['street'] . '</td></tr>';
                $html .= '<tr><td><strong>' . __( 'City', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . $pickup_data['city'] . '</td></tr>';
                $html .= '<tr><td><strong>' . __( 'Zip-Code', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . $pickup_data['zipcode'] . '</td></tr>';
                $html .= '<tr><td><strong>' . __( 'Phone', RDEV_SELFPICKUP_DOMAIN ) . '</strong></td><td>' . $pickup_data['phone'] . '</td></tr>';
                $html .= '</table>';

                $html .= '<a type="button" disabled="disabled" href="#" class="button button-primary" style="margin-top:15px;text-align:center;width:100%;">' . __( 'Export file for upload in Envelo', RDEV_SELFPICKUP_DOMAIN ) . '</a>';
                $html .= '<a type="button" href="https://www.envelo.pl/parcel/#/" target="_blank" rel="noopener nofollow" class="button button-primary" style="margin-top:5px;width:100%;text-align:center;">' . __( 'Open the Envelo carrier website', RDEV_SELFPICKUP_DOMAIN ) . '</a>';
                echo $html;
            }
        }
    }
