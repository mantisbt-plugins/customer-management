<?php 

switch ( $_POST['action'] ) {
	case 'deleteGroup':
		CustomerManagementDao::deleteGroup(gpc_get_int('customerGroupId'));
		break;
}
?>