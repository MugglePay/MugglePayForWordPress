<?php

if (! defined('ABSPATH')) {
    exit;
}

/*
Plugin Name:  MugglePay
Plugin URI:   https://mugglepay.com/
Description:  MugglePay is a one-stop payment solution for merchants with an online payment need.
Version:      1.0.8
Author:       MugglePay
Author URI:   https://x.com/mugglepay
Text Domain:  mugglepay
Domain Path:  /i18n/languages/
Requires Plugins: woocommerce
License:      GPLv3+
License URI:  https://www.gnu.org/licenses/gpl-3.0.html
*/

define( 'MPWP_MAIN_FILE', __FILE__ );
define('MPWP_PLUGIN_URL', plugins_url('', __FILE__));
define('MPWP_PLUGIN_DIR', plugin_dir_path(__FILE__));

function mpwp_init()
{
    require_once MPWP_PLUGIN_DIR . 'class/class-mpwp-gateway.php';
    require_once MPWP_PLUGIN_DIR . 'class/class-mpwp-muggle-pay.php';
    require_once MPWP_PLUGIN_DIR . 'class/class-mpwp-eth.php';
    require_once MPWP_PLUGIN_DIR . 'class/class-mpwp-usdc.php';
    require_once MPWP_PLUGIN_DIR . 'class/class-mpwp-usdt.php';

    // add_action( 'init', 'cb_wc_register_blockchain_status' );
    add_filter('woocommerce_payment_gateways', 'mpwp_add_gateway_class');
    // add_filter('wc_order_statuses', 'mpwp_wc_add_status');
    add_action('mpwp_check_orders', 'mpwp_wc_check_orders');
    add_action('woocommerce_admin_order_data_after_order_details', 'mpwp_order_meta_general');
    add_action('woocommerce_order_details_after_order_table', 'mpwp_order_meta_general');
    // add_filter( 'woocommerce_email_order_meta_fields', 'cb_custom_woocommerce_email_order_meta_fields', 10, 3 );
    // add_filter( 'woocommerce_email_actions', 'cb_register_email_action' );
    add_action('admin_enqueue_scripts', 'mpwp_admin_load_scripts');
    add_action('wp_enqueue_scripts', 'mpwp_public_style');
    // add payment gateway filter
    add_filter('woocommerce_available_payment_gateways', 'mpwp_filter_woocommerce_available_payment_gateways', 10, 1);

}
add_action('plugins_loaded', 'mpwp_init');


/**
 * Registers WooCommerce Blocks integration.
 */
function mpwp_wc_gateway_woocommerce_block_support() {

    require_once __DIR__ . '/class/class-mpwp-gateway-blocks-support.php';
    require_once __DIR__ . '/class/class-mpwp-muggle-pay-methods.php';
    require_once __DIR__ . '/class/class-mpwp-usdt-methods.php';
    require_once __DIR__ . '/class/class-mpwp-usdc-methods.php';
    require_once __DIR__ . '/class/class-mpwp-eth-methods.php';

    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
            $payment_method_registry->register( new MPWP_WC_Gateway_Blocks_Support() );
            $payment_method_registry->register( new MPWP_WC_Muggle_Pay_Methods() );
            $payment_method_registry->register( new MPWP_WC_Usdt_Methods() );
            $payment_method_registry->register( new MPWP_WC_Usdc_Methods() );
            $payment_method_registry->register( new MPWP_WC_Eth_Methods() );
        }
    );

}
add_action( 'woocommerce_blocks_loaded', 'mpwp_wc_gateway_woocommerce_block_support' );

// Regiester Gateway To WooCommerce
function mpwp_add_gateway_class($methods)
{
    $methods[] = 'MPWP_WC_Gateway';
    $methods[] = 'MPWP_WC_Muggle_Pay';
    $methods[] = 'MPWP_WC_Usdt';
    $methods[] = 'MPWP_WC_Usdc';
    $methods[] = 'MPWP_WC_Eth';
    return $methods;
}

/**
 * Check All MugglePay Order Status
 */
function mpwp_wc_check_orders()
{
    $gateway = WC()->payment_gateways()->payment_gateways()['mpwp'];
    return $gateway->check_orders();
}

/**
 * Setup cron job.
 */
function mpwp_cron_schedules($schedules)
{
    if (!isset($schedules["5min"])) {
        $schedules["5min"] = array(
            'interval' => 5 * 60,
            'display' => __('Once every 5 minutes', 'mugglepay')
        );
    }
    return $schedules;
}
add_filter('cron_schedules', 'mpwp_cron_schedules');

function mpwp_activation()
{
    if (! wp_next_scheduled('mpwp_check_orders')) {
        wp_schedule_event(time(), '5min', 'mpwp_check_orders');
    }
}
function mpwp_deactivation()
{
    wp_clear_scheduled_hook('mpwp_check_orders');
}
register_activation_hook(__FILE__, 'mpwp_activation');
register_deactivation_hook(__FILE__, 'mpwp_deactivation');


/**
 * Add order MugglePay meta after General and before Billing
 *
 * @see: https://rudrastyh.com/woocommerce/customize-order-details.html
 *
 * @param WC_Order $order WC order instance
 */
function mpwp_order_meta_general($order)
{
    $gateway = WC()->payment_gateways()->payment_gateways()['mpwp'];
    if (isset($gateway->gateway_methods[$order->get_payment_method()])) {
        ?>

<br class="clear" />
<h3><?php  esc_html_e('MugglePay Payment Voucher', 'mugglepay'); ?>
</h3>
<div class="">
  <p>
    <?php
            // Translators: %s is the transaction ID.
            printf( esc_html__( 'Transaction ID: %s', 'mugglepay' ), esc_html( $order->get_transaction_id() ) );
        ?>
  </p>
</div>

<?php
    }
}


/**
 * i18n init
 */
function mpwp_plugin_languages_init()
{
    load_plugin_textdomain('mugglepay', false, basename(dirname(__FILE__)) . '/i18n/languages/');
}
add_action('plugins_loaded', 'mpwp_plugin_languages_init');


/**
 * Init Wooocommerce multi payment gateway
 */
function mpwp_filter_woocommerce_available_payment_gateways($available_gateways)
{

    if ( is_admin() && isset( $available_gateways['eth_methods'] ) ) {
        unset( $available_gateways['eth_methods'] );
    }

    if (isset($available_gateways['mpwp']) && $available_gateways['mpwp']) {
        $mpwp = $available_gateways['mpwp'];
        foreach ($available_gateways['mpwp']->gateway_methods as $key => $method) {
            if ($mpwp->get_option($key) === 'yes') {
                $available_gateways[$key] = clone $mpwp;

                $available_gateways[$key]->id = $key;
                $available_gateways[$key]->current_method = $method['currency'];
                $available_gateways[$key]->title = $method['title'];
                $available_gateways[$key]->order_button_text = $method['order_button_text'];
            }
        }

        // unset self
        unset($available_gateways['mpwp']);
    }

    return $available_gateways;
};
         

/**
 * Public style hook
 */
function mpwp_public_style() {
    wp_register_style( 'mpwp-public-style', MPWP_PLUGIN_URL.'/assets/css/mpwp-public.css', array(), 2 );
    wp_enqueue_style( 'mpwp-public-style' );
}

/**
 * Admin style and script hook
 */
function mpwp_admin_load_scripts() {

    wp_register_style( 'mpwp-admin-style', MPWP_PLUGIN_URL.'/assets/css/mpwp-admin.css', array(), 4 );
    wp_enqueue_style( 'mpwp-admin-style' );
    
    wp_enqueue_script('jquery');
    
    wp_register_script( 'mpwp-admin-script', MPWP_PLUGIN_URL.'/assets/js/mpwp-admin.js', array(), 1, true );
    wp_enqueue_script( 'mpwp-admin-script' );

}

add_filter( 'network_admin_plugin_action_links', 'mpwp_add_settings_link_to_network_plugins', 10, 2 );

/**
 * Settings Link
 */
function mpwp_add_settings_link_to_network_plugins($actions, $plugin_file) {
   
    if ( 'mugglepay/muggle-pay.php' === $plugin_file ) 
    {
        $sites = get_sites();
        
        foreach ($sites as $site) {

            switch_to_blog($site->blog_id);

            if (is_plugin_active('mugglepay/muggle-pay.php')) 
            {
                $settings_url = get_admin_url($site->blog_id) . 'admin.php?page=wc-settings&tab=checkout&section=mpwp';
                
                $actions['settings'] = '<a href="' . esc_url($settings_url) . '">' . esc_html__( 'Settings', 'mugglepay' ) . '</a>';
                
                break;
            }
        }

        restore_current_blog();
    }

    return $actions;
}

add_filter( 'plugin_action_links', 'mpwp_add_settings_link_to_plugins', 10, 2 );

function mpwp_add_settings_link_to_plugins($actions, $plugin_file) {
  
    if ( 'mugglepay/muggle-pay.php' === $plugin_file ) 
    {
       
        $settings_url = get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=mpwp';
        
        $actions['settings'] = '<a href="' . esc_url($settings_url) . '">' . esc_html__( 'Settings', 'mugglepay' ) . '</a>';
                
    }

    return $actions;
}

function declare_cart_checkout_blocks_compatibility() {
    // Check if the required class exists
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        // Declare compatibility for 'cart_checkout_blocks'
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
}
// Hook the custom function to the 'before_woocommerce_init' action
add_action('before_woocommerce_init', 'declare_cart_checkout_blocks_compatibility');