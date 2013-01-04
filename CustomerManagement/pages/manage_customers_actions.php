<?php
require_once 'core/json_api.php';
set_error_handler('json_error_handler');

access_ensure_global_level( plugin_config_get( 'manage_customers_threshold' ) );
form_security_validate('manage_customers');

$contents = '';

switch ( $_POST['action'] ) {
	case 'deleteGroup':
		CustomerManagementDao::deleteGroup(gpc_get_int('customerGroupId'));
		break;
	case 'deleteService':
		CustomerManagementDao::deleteService(gpc_get_int('serviceId'));
		break;
	case 'deleteCustomer':
		CustomerManagementDao::deleteCustomer(gpc_get_int('customerId'));
		break;
	case 'saveGroup':
		CustomerManagementDao::saveGroup(gpc_get_int('id', null), gpc_get_string('name'));
		break;
	case 'saveService':
		CustomerManagementDao::saveService(gpc_get_int('id', null), gpc_get_string('name'));
		break;
	case 'saveCustomer':
		CustomerManagementDao::saveCustomer(gpc_get_int('id', null), gpc_get_string('name'), 
			gpc_get_int('customer_group_id'), gpc_get_string('email'), gpc_get_int_array('service_id', array()));
		break;
	case 'sendNotification':
		CustomerNotifier::notifyCustomers(gpc_get_int_array('customer_id'), 
			gpc_get_string('from'), gpc_get_string('to'));
		break;
	case 'previewNotification':
		$contents = CustomerNotifier::buildNotificationEmails(gpc_get_int_array('customer_id'),
			gpc_get_string('from'), gpc_get_string('to'));
		break;
}

echo json_output_response($contents);
?>