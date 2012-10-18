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

		$this->version = '1.1.0';
		$this->requires = array(
			'MantisCore' => '1.2.0',
		);

		$this->author	= 'Robert Munteanu';
		$this->contact	= 'robert@lmn.ro';
		$this->url		= 'http://github.com/mantisbt-plugins/customer-management';
	}
}

