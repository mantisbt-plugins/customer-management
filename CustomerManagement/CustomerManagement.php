<?php
# Copyright (c) 2012 Robert Munteanu (robert@lmn.ro)

# Customer management for MantisBT is free software: 
# you can redistribute it and/or modify it under the terms of the GNU
# General Public License as published by the Free Software Foundation, 
# either version 2 of the License, or (at your option) any later version.
#
# Customer management plugin for MantisBT is distributed in the hope 
# that it will be useful, but WITHOUT ANY WARRANTY; without even the 
# implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
# See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Customer management plugin for MantisBT.  
# If not, see <http://www.gnu.org/licenses/>.

class CustomerManagementPlugin extends MantisPlugin {

	function register() {
		$this->name = 'Customer Management';
		$this->description = 'Allows customers to be defined and notified about issues opened on their behalf.';

		$this->version = '1.0.0';
		$this->requires = array(
			'MantisCore' => '1.2.11',
			'jQuery'     => '1.4.3',
			'jQueryUI'     => '1.8.0'
		);

		$this->author	= 'Robert Munteanu';
		$this->contact	= 'robert@lmn.ro';
		$this->url		= 'http://github.com/mantisbt-plugins/customer-management';
	}
	
	function schema() {
		
		$t_customer_service_table = plugin_table("customer_service");
		$t_customer_table = plugin_table("customer");
		
		return array(
				// version 1.0.0
				array("CreateTableSQL", array(plugin_table("group"), "
					id I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
					name C(128) NOTNULL
				")),
				array("CreateTableSQL", array(plugin_table("service"), "
					id I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
					name C(128) NOTNULL
				")),
				array("CreateTableSQL", array(plugin_table("customer"), "
					id I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
					customer_group_id I NOTNULL UNSIGNED,
					name C(128) NOTNULL,
					email C(128) NOTNULL
				")),
				array("CreateTableSQL", array(plugin_table("customers_to_services"), "
					customer_id I NOTNULL UNSIGNED,
					service_id I NOTNULL UNSIGNED
				")),
				array("CreateIndexSQL",
						array("idx_cust_to_group",plugin_table("customer"),"customer_group_id")
				),
				array("CreateIndexSQL",
						array("idx_c2s_to_cust",plugin_table("customers_to_services"), "customer_id")
				),
				array("CreateIndexSQL",
						array("idx_c2s_to_service",plugin_table("customers_to_services"), "service_id")
				),
				array("CreateTableSQL", array(plugin_table("bug_data"), "
					bug_id I NOTNULL UNSIGNED,
					customer_id I NOTNULL UNSIGNED,
					service_id I NOTNULL UNSIGNED,
					is_billable L NOTNULL
				")),
				array("AddColumnSQL", array(plugin_table("bug_data"),"
					invoice C(128)
				"))
				
		);
	}

	function config() {
		return array(
				"manage_customers_threshold" => ADMINISTRATOR,
				"view_customer_fields_threshold" => DEVELOPER,
				"edit_customer_fields_threshold" => DEVELOPER,
				"email_notification_language" => "english",
				"auto_prepend_customer_name_to_summary" => OFF,
				"display_invoice_field_status_threshold" => ASSIGNED,
				"require_invoice_field_status_threshold" => RESOLVED
		);
	}

	function hooks() {
		return array(
				"EVENT_LAYOUT_RESOURCES" => "resources",
				"EVENT_MENU_MANAGE" => "menu_manage",
				"EVENT_REPORT_BUG_FORM_TOP" => "prepare_bug_report",
				"EVENT_UPDATE_BUG_FORM" => "prepare_bug_update",
				"EVENT_UPDATE_BUG_STATUS_FORM" => "prepare_bug_status_update",
				"EVENT_UPDATE_BUG" => "save_bug",
				"EVENT_REPORT_BUG" => "save_bug",
				"EVENT_VIEW_BUG_DETAILS" => "view_bug_details",
				"EVENT_BUG_DELETED" => "delete_bug",
				"EVENT_FILTER_COLUMNS" => "add_columns",
				"EVENT_FILTER_FIELDS" => "filter_fields"
		);
	}
	
	function init() {
		require_once 'api/CustomerManagementDao.php';
		require_once 'api/CustomerManagementViewHelper.php';
		require_once 'api/CustomerNotifier.php';
		require_once 'api/CustomerColumn.php';
		require_once 'api/CustomerGroupColumn.php';
		require_once 'api/ServiceColumn.php';
		require_once 'api/IsBillableColumn.php';
		require_once 'api/CustomerFilter.php';
		require_once 'api/CustomerGroupFilter.php';
		require_once 'api/ServiceFilter.php';
		require_once 'api/IsBillableFilter.php';
		require_once 'api/InvoiceColumn.php';
		require_once 'api/InvoiceFilter.php';
	}
	
	function resources() {
		return 
			'<script type="text/javascript" src="' . plugin_file('customer-management.js').'"></script>' .
			'<link rel="stylesheet" type="text/css" href="' . plugin_file('customer-management.css').'"></link>';
	}
	
	public function menu_manage($event, $user_id) {
		if (access_has_global_level(plugin_config_get("manage_customers_threshold"))) {
			$page = plugin_page("manage_customers");
			$label = plugin_lang_get("manage_customers");
			return '<a href="' . string_html_specialchars( $page ) . '">' . $label . '</a>';
		}
	}
	
	public function prepare_bug_report ( $event, $project_id ) {
		$this->prepage_bug_report_internal(true);
	}

	public function prepare_bug_update ( $event, $bug_id ) {
		$this->prepage_bug_report_internal(false, $bug_id);
	}
	
	public function prepare_bug_status_update( $event, $bug_id ) {
		$this->prepage_bug_report_internal(true, $bug_id);
	}
	
	private function prepage_bug_report_internal( $verticalLayout, $bug_id = 0 ) {
		if ( !access_has_global_level(plugin_config_get('edit_customer_fields_threshold'))) {
			return;
		}
		
		$customer_id = 0;
		$service_id = 0;
		$is_billable = 0;
		$invoice = '';
		$show_invoice = false;
		
		if ( $bug_id ) {
			$bug = bug_get( $bug_id );
			$show_invoice = $bug->status >= plugin_config_get('display_invoice_field_status_threshold');
			
			$bug_data = CustomerManagementDao::getBugData( $bug_id );
			if ( $bug_data ) {
				$customer_id = $bug_data['customer_id'];
				$service_id = $bug_data['service_id'];
				$is_billable = $bug_data['is_billable'] == 1;
				$invoice = $bug_data['invoice'];
			}
		}
		
		$customer_label = plugin_lang_get('customer');
		$service_label = plugin_lang_get('service');
		$is_billable_label = plugin_lang_get('is_billable');
		$invoice_label = plugin_lang_get('invoice');
		
		$class = helper_alternate_class();
		$class2 = helper_alternate_class();
		$class3 = helper_alternate_class();
		$class4 = helper_alternate_class();

		$customer_select = CustomerManagementViewHelper::getCustomerSelect( $customer_id);
		$service_select = CustomerManagementViewHelper::getServiceSelect( $service_id );
		$is_billable_checkbox = CustomerManagementViewHelper::getBillableCheckbox( $is_billable);
		$invoice_input = CustomerManagementViewHelper::getInvoiceInput( $invoice);
		
		$customers = CustomerManagementDao::findAllCustomers();
		$customersToServices = array();
		
		foreach ( $customers as $customer ) {
			$serviceIds = array();
			foreach ( $customer['services'] as $service ) 
				$serviceIds[] = $service['id'];
			
			$customersToServices[$customer['id']] = $serviceIds;
		}
		
		$customersToServicesJson = json_encode( $customersToServices );
		$prependCustomerName = ( $bug_id === 0  && plugin_config_get('auto_prepend_customer_name_to_summary') ) ;
		
		if ( $verticalLayout ) {
		
		$row = <<<EOD
<tr $class>
	<td class="category" width="30%">
		$customer_label
	</td>
	<td width="70%">
		$customer_select
	</td>
</tr>
<tr $class2>
	<td class="category" width="30%">
		$service_label
	</td>
	<td width="70%">
		$service_select
	</td>
</tr>
<tr $class3>
	<td class="category" width="30%">
		$is_billable_label
	</td>
	<td width="70%">
		$is_billable_checkbox
	</td>
</tr>
EOD;
		
			if ( $show_invoice ) {
				$row .= <<<EOD
<tr $class4>
	<td class="category" width="30%">
		$invoice_label
	</td>
	<td width="70%">
		$invoice_input
	</td>
</tr>
EOD;
			}
		} else {
			$row = <<<EOD
<tr $class>
	<td class="category">
		$customer_label
	</td>
	<td>
		$customer_select
	</td>
	<td class="category">
		$service_label
	</td>
	<td>
		$service_select
	</td>
	<td class="category">
		$is_billable_label
	</td>
	<td>
		$is_billable_checkbox
	</td>
</tr>
EOD;
			if ( $show_invoice ) {
				$row .= <<<EOD
<tr $class2>
	<td class="category">
		$invoice_label
	</td>
	<td>
		$invoice_input
	</td>
	<td class="category">
		&nbsp;
	</td>
	<td>
		&nbsp;
	</td>
	<td class="category">
		&nbsp;
	</td>
	<td>
		&nbsp;
	</td>
</tr>
EOD;
			}
		}
		
		$row .= <<<EOD
<script type="text/javascript">
var customerManagementBugUi = new CustomerManagementBugUi($customersToServicesJson);
customerManagementBugUi.init({'prependCustomerName' : '$prependCustomerName'});
</script>		
EOD;
		
		echo $row;
		
	}
	
	public function save_bug( $p_event, $p_bug_data,  $p_bug_id ) {
		
		if ( !access_has_global_level(plugin_config_get('edit_customer_fields_threshold'))) {
			return;
		}
		
		$customer_id = gpc_get_int('cm_plugin_customer_id', null);
		$service_id = gpc_get_int('cm_plugin_service_id', null);
		$is_billable = CustomerManagementDao::isServiceBillable( $customer_id, $service_id );
		$invoice = gpc_get_string('cm_plugin_invoice', null);
		
		if ( $customer_id ) {
			
			if ( $is_billable && is_blank($invoice) 
					&& $p_bug_data->status >= plugin_config_get('require_invoice_field_status_threshold') ) {
				error_parameters(plugin_lang_get('invoice'));
				trigger_error(ERROR_EMPTY_FIELD, ERROR);
			}
			
			$bug_data = CustomerManagementDao::getBugData( $p_bug_id );
			
			CustomerManagementDao::saveBugData($p_bug_id, $customer_id, $service_id, $is_billable, $invoice );

			if ( $bug_data )
				history_log_event_direct($p_bug_id, 'invoice', $bug_data['invoice'], $invoice);
		}
			
		return $p_bug_data;
	}
	public function view_bug_details( $p_event, $p_bug_id ) {
		
		if ( !access_has_global_level(plugin_config_get('view_customer_fields_threshold'))) {
			return;
		}
		
		$bug_data = CustomerManagementDao::getBugData( $p_bug_id );
		if ( !$bug_data )
			return;
		
		$class = helper_alternate_class();
		$class2 = helper_alternate_class();
		$customer_label = plugin_lang_get('customer');
		$service_label = plugin_lang_get('service');
		$is_billable_label = plugin_lang_get('is_billable');
		$invoice_label = plugin_lang_get('invoice');
		
		$show_invoice = bug_get_field( $p_bug_id, 'status') >= plugin_config_get('display_invoice_field_status_threshold');
		
		$customer = CustomerManagementDao::getCustomer( $bug_data['customer_id']);
		$service = CustomerManagementDao::getService( $bug_data['service_id']);
		$is_billable = $bug_data['is_billable'] ? lang_get('yes') : lang_get('no');
		
		if ( $bug_data ) {
			$row = <<<EOD
<tr $class>
	<td class="category">$customer_label</td>
	<td>${customer['name']}</td>
	<td class="category">$service_label</td>
	<td>${service['name']}</td>
	<td class="category">$is_billable_label</td>
	<td>$is_billable</td>
</tr>
EOD;
			if ( $show_invoice ) {
				$row .= <<<EOD
<tr $class>
	<td class="category">$invoice_label</td>
	<td>${bug_data['invoice']}</td>
	<td class="category">&nbsp;</td>
	<td>&nbsp;</td>
	<td class="category">&nbsp;</td>
	<td>&nbsp;</td>
</tr>				
EOD;
			}
			echo $row;
		}
	}
	
	public function delete_bug( $p_event, $p_bug_id ) {
	
		CustomerManagementDao::deleteBugData( $p_bug_id );
	}

	function add_columns() {
		return array( 'CustomerColumn', 'ServiceColumn', 'IsBillableColumn', 'CustomerGroupColumn', 'InvoiceColumn' );
	}
	
	function filter_fields() {
		return array( 'CustomerFilter', 'ServiceFilter', 'IsBillableFilter', 'CustomerGroupFilter', 'InvoiceFilter');
	}
}