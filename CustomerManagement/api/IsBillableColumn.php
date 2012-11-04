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

require_once 'columns_api.php';

class IsBillableColumn extends MantisColumn {
	
	public function __construct( ) {
		plugin_push_current( 'CustomerManagement' );
	
		$this->title = plugin_lang_get( 'is_billable');
		$this->column = 'is_billable';
	
		plugin_pop_current();
	}
	
	public function display( $p_bug, $p_columns_target ) {
		plugin_push_current( 'CustomerManagement' );
	
		if ( access_has_global_level(plugin_config_get('view_customer_fields_threshold'))) {
			$bugData = CustomerManagementDao::getBugData( $p_bug->id );
			
			if( count ( $bugData ) > 0 ) {
				$isBillable = CustomerManagementDao::getService( $bugData['is_billable']);
				echo string_display_line( $isBillable ? lang_get('yes') : lang_get('no') );
			}
		}
		plugin_pop_current();
	}
}