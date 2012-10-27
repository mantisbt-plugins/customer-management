<?php
class CustomerManagementViewHelper {
	
	static function getCustomerSelect($selected = -1) {
		
		$customers = CustomerManagementDao::findAllCustomers();
		$output = '<select id="cm_plugin_customer_id" name="cm_plugin_customer_id"><option value=""></option>';
		foreach ( $customers as $customer )
			$output .= "<option value={$customer['id']}>{$customer['name']}</option>";
		$output .= '</select>';

		return $output;
	}
	
	static function getServiceSelect($selected = -1) {
		
		$services = CustomerManagementDao::findAllServices();
		$output = '<select id="cm_plugin_service_id" name="cm_plugin_service_id"><option value=""></option>';
		foreach ( $services as $service )
			$output .= "<option value={$service['id']}>{$service['name']}</option>";
		$output .= '</select>';

		return $output;
	}

	static function getBillableCheckbox($checked = false) {
		
		return '<input type="checkbox" name="cm_plugin_is_billable" id="cm_plugin_is_billable" disabled="disabled">';
	}
}