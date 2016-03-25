<?php

namespace hypeJunction\Payments\PayPal;

use ElggFile;
use hypeJunction\Payments\Transaction;
use PayPal\IPN\PPIPNMessage;

/**
 * @property Transaction $transaction
 * @property string      $transaction_id
 */
class IPNProcessor {

	/**
	 * Process PayPal IPN payload
	 * @return Transaction|false
	 */
	public function process() {


		$postData = file_get_contents('php://input');

		$ipn = new PPIPNMessage($postData, Adapter::getConfig());

		$data = array();
		$fixArr = preg_replace("/\b(\w+)\\%5B(\w+)\\%5D\.([^=]*)[=]([^\&]*)([\&$])\b/", "$1%5B$2%5D%5B$3%5D=$4$5", $postData);
		parse_str($fixArr, $data);

		if (isset($data['payment_status'])) {
			$payment_status = $data['payment_status'];
			$txn_id = $data['txn_id'];
		} else if ($data['transaction']) {
			foreach ($data['transaction'] as $transaction) {
				if ($transaction['is_primary_receiver'] === 'true' || sizeof($data['transaction']) == 1) {
					$payment_status = $transaction['status'];
					$txn_id = $transaction['id'];
					$refund_amount = $transaction['refund_amount'];
				}
			}
		}

		$ia = elgg_set_ignore_access(true);

		$transaction_id = get_input('transaction_id');
		$transaction = Transaction::getFromId($transaction_id);
		if ($transaction) {
			$transaction->payment_method = 'paypal';
			$transaction->paypal_transaction_id = $txn_id;

			switch (strtolower($payment_status)) {

				case 'created' :
				case 'pending' :
				case 'processed' :
					$transaction->setStatus(Transaction::STATUS_PAYMENT_PENDING);
					break;

				case 'completed' :
					$transaction->setStatus(Transaction::STATUS_PAID);
					break;

				case 'refunded' :
					$transaction->setStatus(Transaction::STATUS_REFUNDED);
					break;

				case 'partially_refunded' :
					list($currency, $value) = explode(' ', $refund_amount);
					$money = \SebastianBergmann\Money\Money::fromString($value, $currency);
					$params = array('refund_amount' => $money->getAmount());
					$transaction->setStatus(Transaction::STATUS_PARTIALLY_REFUNDED, $params);
					break;

			}

			$details = $transaction->getDetails();
			$ipn = !empty($details['_paypal_ipn']) ? $details['_paypal_ipn'] : array();
			$ts = time();
			$ipn["$ts"] = $data;

			$transaction->setDetails('_paypal_ipn', $ipn);
			$transaction->save();
		}

		elgg_set_ignore_access($ia);

		$file = new ElggFile();
		$file->owner_guid = elgg_get_site_entity()->guid;
		$file->setFilename('logs/paypal/ipn/' . time() . '-' . _elgg_services()->crypto->getRandomString(10) . '.json');
		$file->open('write');
		$file->write(json_encode(array(
			'rawData' => $data,
			'transaction' => $transaction ? $transaction->toObject() : false,
		)));
		$file->close();

		return $transaction;
	}

}
