<?php

form_security_validate('manage_customers');

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
			gpc_get_int('customer_group_id'), gpc_get_int_array('service_id'), array());
		break;
}
?>