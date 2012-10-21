<?php

form_security_validate('manage_customers');

switch ( $_POST['action'] ) {
	case 'deleteGroup':
		CustomerManagementDao::deleteGroup(gpc_get_int('customerGroupId'));
		break;
	case 'deleteService':
		CustomerManagementDao::deleteService(gpc_get_int('serviceId'));
		break;
	case 'saveGroup':
		CustomerManagementDao::saveGroup(gpc_get_int('id', null), gpc_get_string('name'));
		break;
	case 'saveService':
		CustomerManagementDao::saveService(gpc_get_int('id', null), gpc_get_string('name'));
		break;
}
?>