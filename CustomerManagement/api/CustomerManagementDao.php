<?php

class CustomerManagementDao {
	
	static function findAllGroups() {
		return self::toArray(db_query_bound('SELECT * FROM ' . plugin_table('group' ) . ' ORDER BY name'));
	}
	
	private static function toArray( $p_db_result ) {
		
		$result = array();
		while ( $row = db_fetch_array( $p_db_result) ) {
			$result[] = $row;
		}
		
		return $result;
	}

	static function findAllCustomers() {
		// TODO add services
		return db_query_bound('
				SELECT c.id, c.name, g.name AS group_name FROM ' . plugin_table('customer' ) . ' c 
				LEFT JOIN '. plugin_table('group') . ' g ON g.id = c.group_id 
				ORDER BY c.name');
	}
	
	static function findAllServices() {
		
		return db_query_bound('SELECT * FROM ' . plugin_table('service') . ' ORDER BY name');
	}
}