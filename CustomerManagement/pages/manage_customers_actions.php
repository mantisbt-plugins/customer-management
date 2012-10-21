<?php

form_security_validate('manage_customers');

switch ( $_POST['action'] ) {
	case 'deleteGroup':
		CustomerManagementDao::deleteGroup(gpc_get_int('customerGroupId'));
		break;
	case 'saveGroup':
		CustomerManagementDao::saveGroup(gpc_get_int('id', null), gpc_get_string('name'));
		break;
}
?>