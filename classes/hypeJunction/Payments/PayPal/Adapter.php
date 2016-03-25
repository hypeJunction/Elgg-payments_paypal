<?php

namespace hypeJunction\Payments\PayPal;

use ElggFile;
use Exception;
use hypeJunction\Payments\GatewayInterface;
use hypeJunction\Payments\Transaction;
use hypeJunction\Payments\TransactionInterface;
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\PayRequest;
use PayPal\Types\AP\Receiver;
use PayPal\Types\AP\ReceiverList;
use PayPal\Types\Common\RequestEnvelope;
use SebastianBergmann\Money\Money;

class Adapter implements GatewayInterface {

	/**
	 * Pay
	 *
	 * @param Transaction $transaction Transaction object
	 * @return string
	 */
	public function getPaymentUrl(TransactionInterface $transaction) {

		$transaction->setStatus(Transaction::STATUS_PAYMENT_PENDING);
		
		$payRequest = new PayRequest();

		$receivers = $this->getReceiverList($transaction);
		$payRequest->receiverList = new ReceiverList($receivers);

		$requestEnvelope = new RequestEnvelope("en_US");
		$payRequest->requestEnvelope = $requestEnvelope;
		$payRequest->actionType = "PAY";
		$payRequest->currencyCode = $transaction->getCurrency();
		$payRequest->cancelUrl = elgg_normalize_url(elgg_http_add_url_query_elements('payments/paypal/cancel', [
			'transaction_id' => $transaction->transaction_id,
		]));
		$payRequest->returnUrl = elgg_normalize_url(elgg_http_add_url_query_elements('payments/paypal/success', [
			'transaction_id' => $transaction->transaction_id,
		]));
		$payRequest->ipnNotificationUrl = elgg_normalize_url(elgg_http_add_url_query_elements('payments/paypal/ipn', [
			'transaction_id' => $transaction->transaction_id,
		]));

		$adaptivePaymentsService = new AdaptivePaymentsService($this->getConfig());
		$result = $adaptivePaymentsService->Pay($payRequest);

		if (!$result->payKey) {
			$file = new ElggFile();
			$file->owner_guid = elgg_get_site_entity()->guid;
			$file->setFilename('logs/paypal/paykey/' . time() . '-' . _elgg_services()->crypto->getRandomString(10) . '.json');
			$file->open('write');
			$file->write(json_encode($result));
			$file->close();
			throw new Exception("PayPal paykey key could not be obtained: " . $result->error->message);
		}

		if (elgg_get_plugin_setting('environment', 'payments', 'sandbox') == 'production') {
			$endpoint = elgg_get_plugin_setting('live_endpoint', 'payments_paypal');
		} else {
			$endpoint = elgg_get_plugin_setting('sandbox_endpoint', 'payments_paypal');
		}
		return elgg_http_add_url_query_elements("https://$endpoint/cgi-bin/webscr", array(
			'cmd' => '_ap-payment',
			'paykey' => $result->payKey,
		));
	}

	/**
	 * Prepare receiver list
	 *
	 * @param TransactionInterface $transaction Transaction object
	 * @return Receiver[]
	 */
	protected function getReceiverList(TransactionInterface $transaction) {

		$merchant = $transaction->getMerchant();

		$paypal_commission = $transaction->getCommissionRate($this);
		$site_email = elgg_get_plugin_setting('site_fee_email', 'payments_paypal');

		$total = $transaction->getAmount();
		$currency = $transaction->getCurrency();
		$commission = 0;

		$receivers = array();

		if ($paypal_commission && $site_email) {
			$money = new Money($total, $currency);
			$percentage = $money->extractPercentage((float) $paypal_commission)['percentage'];
			/* @var $percentage Money */

			$commission = $percentage->getAmount();

			$site_receiver = new Receiver();
			$site_receiver->amount = (new Money($commission, $currency))->getConvertedAmount();
			$site_receiver->email = $site_email;
			$merchant_receiver->primary = "false";
			$receivers[1] = $site_receiver;
		}

		$merchant_receiver = new Receiver();
		$merchant_receiver->amount = (new Money($total, $currency))->getConvertedAmount();
		$merchant_receiver->email = $merchant instanceof \ElggSite ? $site_email : $merchant->paypal_email;
		if (isset($receivers[1])) {
			$merchant_receiver->primary = "true";
		}
		$receivers[0] = $merchant_receiver;

		return $receivers;
	}

	/**
	 * Returns config array
	 * @return array
	 */
	public static function getConfig() {
		$plugin = elgg_get_plugin_from_id('payments_paypal');
		$settings = $plugin->getAllSettings();

		$mode = elgg_get_plugin_setting('environment', 'payments', 'sandbox');

		if ($mode == 'production') {
			return array(
				'mode' => 'live',
				'acct1.UserName' => elgg_extract('live_username', $settings),
				'acct1.Password' => elgg_extract('live_password', $settings),
				'acct1.Signature' => elgg_extract('live_signature', $settings),
				'acct1.AppId' => elgg_extract('live_app_id', $settings),
			);
		} else {
			return array(
				'mode' => 'sandbox',
				'acct1.UserName' => elgg_extract('sandbox_username', $settings),
				'acct1.Password' => elgg_extract('sandbox_password', $settings),
				'acct1.Signature' => elgg_extract('sandbox_signature', $settings),
				'acct1.AppId' => elgg_extract('sandbox_app_id', $settings),
			);
		}
	}

}
