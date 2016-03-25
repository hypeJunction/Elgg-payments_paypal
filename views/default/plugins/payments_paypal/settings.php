<?php
$entity = elgg_extract('entity', $vars);
?>

<div>
	<label><?= elgg_echo('payments:paypal:commission') ?></label>
	<div class="elgg-text-help"><?= elgg_echo('payments:paypal:commission:help') ?></div>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[paypal_commission]',
		'value' => $entity->paypal_commission,
	));
	?>
</div>
<div>
	<label><?= elgg_echo('payments:paypal:site_fee_email') ?></label>
	<div class="elgg-text-help"><?= elgg_echo('payments:paypal:site_fee_email:help') ?></div>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[site_fee_email]',
		'value' => $entity->site_fee_email,
	));
	?>
</div>

<h3><?= elgg_echo('payments:paypal:sandbox') ?></h3>
<div>
	<label><?= elgg_echo('payments:paypal:sandbox_endpoint') ?></label>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[sandbox_endpoint]',
		'value' => $entity->sandbox_endpoint ? : 'www.sandbox.paypal.com',
	));
	?>
</div>
<div>
	<label><?= elgg_echo('payments:paypal:sandbox_username') ?></label>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[sandbox_username]',
		'value' => $entity->sandbox_username,
	));
	?>
</div>
<div>
	<label><?= elgg_echo('payments:paypal:sandbox_password') ?></label>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[sandbox_password]',
		'value' => $entity->sandbox_password,
	));
	?>
</div>
<div>
	<label><?= elgg_echo('payments:paypal:sandbox_signature') ?></label>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[sandbox_signature]',
		'value' => $entity->sandbox_signature,
	));
	?>
</div>
<div>
	<label><?= elgg_echo('payments:paypal:sandbox_app_id') ?></label>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[sandbox_app_id]',
		'value' => $entity->sandbox_app_id ? : 'APP-80W284485P519543T',
	));
	?>
</div>

<h3><?= elgg_echo('payments:paypal:live') ?></h3>

<div>
	<label><?= elgg_echo('payments:paypal:live_endpoint') ?></label>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[live_endpoint]',
		'value' => $entity->live_endpoint ? : 'www.paypal.com',
	));
	?>
</div>
<div>
	<label><?= elgg_echo('payments:paypal:live_username') ?></label>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[live_username]',
		'value' => $entity->live_username,
	));
	?>
</div>
<div>
	<label><?= elgg_echo('payments:paypal:live_password') ?></label>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[live_password]',
		'value' => $entity->live_password,
	));
	?>
</div>
<div>
	<label><?= elgg_echo('payments:paypal:live_signature') ?></label>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[live_signature]',
		'value' => $entity->live_signature,
	));
	?>
</div>
<div>
	<label><?= elgg_echo('payments:paypal:live_app_id') ?></label>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[live_app_id]',
		'value' => $entity->live_app_id,
	));
	?>
</div>