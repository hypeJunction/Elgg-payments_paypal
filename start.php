<?php

/**
 * PayPal Adaptive Payments
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'payments_paypal_init');

/**
 * Initialize
 * @return void
 */
function payments_paypal_init() {
	elgg_register_plugin_hook_handler('route', 'payments', 'payments_paypal_route_hook');
	elgg_register_plugin_hook_handler('public_pages', 'walled_garden', 'payments_paypal_public_pages');
	elgg_register_plugin_hook_handler('site_commission', 'payments', 'payments_paypal_get_site_commission_rate');
}

/**
 * Route payment pages
 *
 * @param string $hook   "route"
 * @param string $type   "payment"
 * @param mixed  $return New route
 * @param array  $params Hook params
 * @return array
 */
function payments_paypal_route_hook($hook, $type, $return, $params) {

	$segments = (array) elgg_extract('segments', $return);

	if ($segments[0] !== 'paypal') {
		return;
	}

	switch ($segments[1]) {
		case 'success' :
			$id = get_input('transaction_id');
			system_message(elgg_echo('payments:paypal:transaction:successful'));
			forward("payments/transaction/$id");
			break;

		case 'cancel' :
			$id = get_input('transaction_id');
			register_error(elgg_echo('payments:paypal:transaction:cancelled'));
			forward("payments/transaction/$id");
			break;

		case 'ipn' :
			try {
				$processor = new hypeJunction\Payments\PayPal\IPNProcessor();
				$transaction = $processor->process();
				if ($transaction && $transaction->paypal_transaction_id) {
					return true;
				}
			} catch (Exception $ex) {
				elgg_log($ex->getMessage(), 'ERROR');
			}
			break;
	}

	return false;
}

/**
 * Add IPN processor to public pages
 * 
 * @param string $hook   "public_pages"
 * @param string $type   "walled_garden"
 * @param array  $return Public pages
 * @param array  $params Hook params
 * @return array
 */
function payments_paypal_public_pages($hook, $type, $return, $params) {
	$return[] = 'payments/paypal/ipn';
	$return[] = 'payments/paypal/ipn/.*';
	return $return;
}

/**
 * Calcualte site commission rate for a transaction
 *
 * @param string $hook   "site_commission"
 * @param string $type   "payments"
 * @param float  $return Commission rate
 * @param array  $params Hook params
 * @return float
 */
function payments_paypal_get_site_commission_rate($hook, $type, $return, $params) {

	$transaction = elgg_extract('entity', $params);
	$gateway = elgg_extract('gateway', $params);

	if (!$gateway instanceof \hypeJunction\Payments\PayPal\Adapter) {
		return;
	}

	if (!$transaction instanceof \hypeJunction\Payments\TransactionInterface) {
		return;
	}

	$merchant = $transaction->getMerchant();
	$paypal_commission = $merchant->paypal_commission;
	if ($paypal_commission === null || $paypal_commission === '') {
		$paypal_commission = elgg_get_plugin_setting('paypal_commission', 'payments_paypal');
	}

	return $paypal_commission;
}
