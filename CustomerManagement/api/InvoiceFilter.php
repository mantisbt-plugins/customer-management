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

class InvoiceFilter extends MantisFilter {
	
	public function __construct() {
		plugin_push_current( 'CustomerManagement' );
		$this->title = plugin_lang_get('invoice');
		$this->field = 'invoice';
		$this->type = FILTER_TYPE_STRING;
		plugin_pop_current();
	
	}

	function display( $p_filter_value ) {
		plugin_push_current( 'CustomerManagement' );
		
		if ( access_has_global_level(plugin_config_get('view_customer_fields_threshold'))) {
			return string_display_line( $p_filter_value );
		}
		plugin_pop_current();
	}
	
	
	function query( $p_filter_input ) {

		$invoice = $p_filter_input;
		if ( is_blank( $invoice ) ) {
			return;
		}
		
		plugin_push_current( 'CustomerManagement' );
		
		if ( access_has_global_level(plugin_config_get('view_customer_fields_threshold'))) {
			$t_query = CustomerManagementDao::buildFilterArrayForInvoice( $invoice );
		}
		
		plugin_pop_current();
		
		return $t_query;
	}
}