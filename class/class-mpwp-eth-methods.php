<?php

if (! defined('ABSPATH')) {
    exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\StoreApi\Payments\PaymentContext;
use Automattic\WooCommerce\StoreApi\Payments\PaymentResult;

final class MPWP_WC_Eth_Methods extends AbstractPaymentMethodType {
	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'eth_methods';

	private $gateway;

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {

		$this->settings = get_option( 'woocommerce_mpwp_settings', array() );

		$gateways = WC()->payment_gateways->payment_gateways();

		$this->gateway  = $gateways[ 'mpwp' ];

		add_action( 'woocommerce_rest_checkout_process_payment_with_context', array( $this, 'mpwp_failed_payment_notice' ), 8, 2 );
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return ! empty( $this->settings[ $this->name ] ) && 'yes' === $this->settings[ $this->name ];
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$script_url = plugins_url( "/assets/js/blocks/eth_methods.js", MPWP_MAIN_FILE );
	
		wp_register_script(
			"eth_methods",
			$script_url,
			array(  'wp-element', 'wc-blocks-checkout', 'react', 'wc-blocks-registry', 'wc-settings', 'wp-html-entities', 'wp-i18n'),
			'1.2',
			true
		);

		wp_set_script_translations( 'eth_methods', 'eth_methods' );

		return array( "eth_methods" );
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		
		return array(
			'title'             => $this->gateway->gateway_methods['eth_methods']['title'],
			'description'       => '',
			'supports'          => array_filter( $this->gateway->supports, array( $this->gateway, 'supports' ) ),
            'icon'              => MPWP_PLUGIN_URL . '/assets/images/eth.png',
			'allow_saved_cards' => is_user_logged_in(),
		);
	}

	/**
	 * Add failed payment notice to the payment details.
	 *
	 * @param PaymentContext $context Holds context for the payment.
	 * @param PaymentResult  $result  Result object for the payment.
	 */
	public function mpwp_failed_payment_notice( PaymentContext $context, PaymentResult &$result ) {
		if ( 'eth_methods' === $context->payment_method ) {
			// add_action(
			// 	'mpwp_wc_gateway_process_payment_error',
			// 	function( $failed_notice ) use ( &$result ) {
			// 		$payment_details                 = $result->payment_details;
			// 		$payment_details['errorMessage'] = wp_strip_all_tags( $failed_notice );
			// 		$result->set_payment_details( $payment_details );
			// 	}
			// );
		}
	}

}