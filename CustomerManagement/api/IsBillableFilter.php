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

class IsBillableFilter extends MantisFilter {
	
	public function __construct() {
		plugin_push_current( 'CustomerManagement' );
		$this->title = plugin_lang_get('is_billable');
		$this->field = 'is_billable';
		$this->type = FILTER_TYPE_MULTI_INT;
		plugin_pop_current();
	
	}

	function display( $p_filter_value ) {
		plugin_push_current( 'CustomerManagement' );
		
		if ( access_has_global_level(plugin_config_get('view_customer_fields_threshold'))) {
			return string_display_line( $p_filter_value == 1 ? lang_get('yes') : lang_get('no') );
		}
		plugin_pop_current();
	}
	
	
	function query( $p_filter_input ) {

		$is_billable = $p_filter_input[0];
		if ( !is_numeric( $is_billable) ) {
			return;
		}
		
		plugin_push_current( 'CustomerManagement' );
		
		if ( access_has_global_level(plugin_config_get('view_customer_fields_threshold'))) {
			$t_query = CustomerManagementDao::buildFilterArrayForIsBillable( $is_billable == 1 ? 1 : 0 );
		}
		
		plugin_pop_current();
		
		return $t_query;
	}
	
	function options() {
		
		plugin_push_current( 'CustomerManagement' );
		
		if ( access_has_global_level(plugin_config_get('view_customer_fields_threshold'))) {
			$options = array(
				 1 => lang_get('yes'),
				 2 => lang_get('no') // 0 conflicts with META_FILTER_ANY
			);
		}
		
		plugin_pop_current();

		return $options;
	}
}