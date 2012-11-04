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

class ServiceFilter extends MantisFilter {
	
	public function __construct() {
		plugin_push_current( 'CustomerManagement' );
		$this->title = plugin_lang_get('service');
		$this->field = 'service';
		$this->type = FILTER_TYPE_MULTI_INT;
		plugin_pop_current();
	
	}

	function display( $p_filter_value ) {
		plugin_push_current( 'CustomerManagement' );
		
		if ( access_has_global_level(plugin_config_get('view_customer_fields_threshold'))) {
			if( is_numeric ( $p_filter_value) ) {
				$service = CustomerManagementDao::getService( (int) $p_filter_value);
				return string_display_line( $service['name'] );
			}
		}
		plugin_pop_current();
	}
	
	
	function query( $p_filter_input ) {

		$service_id = $p_filter_input[0];
		
		if ( ! is_numeric ( $service_id ) ) {
			return;
		}
		
		plugin_push_current( 'CustomerManagement' );
		
		if ( access_has_global_level(plugin_config_get('view_customer_fields_threshold'))) {
			$t_query = CustomerManagementDao::buildFilterArrayForService( $service_id );
		}
		
		plugin_pop_current();
		
		return $t_query;
	}
	
	function options() {
		plugin_push_current( 'CustomerManagement' );
		
		if ( access_has_global_level(plugin_config_get('view_customer_fields_threshold'))) {
				$options = array();
				foreach ( CustomerManagementDao::findAllServices() as $service )
					$options[$service['id']] = $service['name'];
		}
		
		plugin_pop_current();
		
		return $options;
	}
}