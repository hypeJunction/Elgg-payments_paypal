<?php

return array(

	'payments:paypal' => 'PayPal',
	
	'payments:paypal:commission' => 'Commission (in %)',
	'payments:paypal:commission:help' => 'Unless specified otherwise in individual merchant settings, this commission rate will be withdrawn from payments made to merchants using PayPal',

	'payments:paypal:site_fee_email' => 'PayPal Email to receive payments',
	'payments:paypal:site_fee_email:help' => 'Specify which PayPal email account is going to receive the commission from sales',

	'payments:paypal:sandbox' => 'Sandbox API credentials',
	'payments:paypal:sandbox_endpoint' => 'Sandbox endpoint',
	'payments:paypal:sandbox_username' => 'Sandbox username',
	'payments:paypal:sandbox_password' => 'Sandbox password',
	'payments:paypal:sandbox_signature' => 'Sandbox signature',
	'payments:paypal:sandbox_app_id' => 'Sandbox App ID',

	'payments:paypal:live' => 'Live API credentials',
	'payments:paypal:live_endpoint' => 'Live endpoint',
	'payments:paypal:live_username' => 'Live username',
	'payments:paypal:live_password' => 'Live password',
	'payments:paypal:live_signature' => 'Live signature',
	'payments:paypal:live_app_id' => 'Live App ID',

	'payments:paypal:transaction:successful' => 'PayPal Payment successfully completed',
	'payments:paypal:transaction:cancelled' => 'PayPal Payment was not completed',

	'payments:method:paypal' => 'PayPal',
);