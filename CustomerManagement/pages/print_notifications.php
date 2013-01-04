<?php
require_once( 'core.php' );

access_ensure_global_level( plugin_config_get( 'manage_customers_threshold' ) );

$emails = CustomerNotifier::buildNotificationEmails(gpc_get_int_array('customer_id'),
			gpc_get_string('from'), gpc_get_string('to'));

foreach ( $emails as $email ) {
	echo '<h3>' . $email->subject. '</h3>';
	echo '<pre>' . $email->body . '</pre>';
}