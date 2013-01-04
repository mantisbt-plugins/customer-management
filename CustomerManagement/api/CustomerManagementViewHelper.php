<?php
class CustomerManagementViewHelper {
	
	static function getCustomerSelect($selected = -1) {
		
		$customers = CustomerManagementDao::findAllCustomers();
		$output = '<select id="cm_plugin_customer_id" name="cm_plugin_customer_id"><option value=""></option>';
		foreach ( $customers as $customer ){
			$selected_attr = $selected == $customer['id'] ? ' selected="selected" ' : '';
			$output .= "<option value=\"{$customer['id']}\" $selected_attr>{$customer['name']}</option>";
		}
		$output .= '</select>';

		return $output;
	}
	
	static function getServiceSelect($selected = -1) {
		
		$services = CustomerManagementDao::findAllServices();
		$output = '<select id="cm_plugin_service_id" name="cm_plugin_service_id"><option value=""></option>';
		foreach ( $services as $service ) {
			$selected_attr = $selected == $service['id'] ? ' selected="selected" ' : '';
			$output .= "<option value=\"{$service['id']}\" $selected_attr>{$service['name']}</option>";
		}
		$output .= '</select>';

		return $output;
	}

	static function getBillableCheckbox($checked = false) {
		
		$checked_attr = $checked ? ' checked="checked" ' : '';
 		
		return '<input type="checkbox" name="cm_plugin_is_billable" id="cm_plugin_is_billable" disabled="disabled" '.$checked_attr .'>';
	}
	
	static function getInvoiceInput($invoice = '') {
		return '<input type="text" name="cm_plugin_invoice" id="cm_plugin_invoice" value="' . string_display_line($invoice) . '">';
	}
}