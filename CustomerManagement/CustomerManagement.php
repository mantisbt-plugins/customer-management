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
			'MantisCore' => '1.2.0',
			'jQuery'     => '1.4.3'
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
					name C(128) NOTNULL
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
				)
		);
	}
	

	function config() {
		return array(
				"manage_customers_threshold" => ADMINISTRATOR
		);
	}

	function hooks() {
		return array(
				"EVENT_MENU_MANAGE" => "menu_manage"
		);
	}
	
	function init() {
		require_once 'api/CustomerManagementDao.php';
	}
	
	public function menu_manage($event, $user_id) {
		if (access_has_global_level(plugin_config_get("manage_customers_threshold"))) {
			$page = plugin_page("manage_customers");
			$label = plugin_lang_get("manage_customers");
			return '<a href="' . string_html_specialchars( $page ) . '">' . $label . '</a>';
		}
	}	
}

