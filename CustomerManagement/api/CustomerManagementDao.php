<?php

class CustomerManagementDao {
	
	static function findAllGroups() {
		return self::toArray(db_query_bound('
				SELECT g.*, COUNT(c.id) as customerCount FROM ' . plugin_table('group' ) . ' g
				LEFT JOIN ' . plugin_table('customer') . ' c
				ON g.id = c.customer_group_id
				GROUP BY g.id
				ORDER BY g.name')
		);
	}
	
	private static function toArray( $p_db_result ) {
		
		$result = array();
		while ( $row = db_fetch_array( $p_db_result) ) {
			$result[] = $row;
		}
		
		return $result;
	}

	static function findAllCustomers() {

		$customers = self::toArray(db_query_bound('
				SELECT c.*, g.name AS groupName FROM ' . plugin_table('customer' ) . ' c 
				LEFT JOIN '. plugin_table('group') . ' g ON g.id = c.customer_group_id 
				ORDER BY c.name'));
		
		$customers_to_services = self::toArray(db_query_bound('
				SELECT c2s.*, s.name FROM  ' . plugin_table('customers_to_services') . ' c2s
				LEFT JOIN ' . plugin_table('service') . ' s ON c2s.service_id = s.id
				'));
		
		foreach ( $customers as & $customer ) {
			$customer['services'] = array();
			foreach ( $customers_to_services as $customer_to_service )  {
				if ( $customer['id'] == $customer_to_service['customer_id']) {
					$customer['services'][] = array(
							'id' => $customer_to_service['service_id'],
							'name' => $customer_to_service['name']
					);
				}
			}
		}
		
		return $customers;
	}
	
	static function findAllServices() {
		return self::toArray(db_query_bound('
				SELECT s.*, COUNT(c2s.service_id) as customerCount FROM ' . plugin_table('service' ) . ' s
				LEFT JOIN ' . plugin_table('customers_to_services') . ' c2s
				ON s.id = c2s.service_id
				GROUP BY s.id
				ORDER BY s.name')
		);
	}
	
	static function deleteGroup( $groupId ) {
		return db_query_bound('DELETE FROM ' . plugin_table('group') . ' WHERE id = ? ', array ( $groupId ));
	}
	
	static function deleteService( $serviceId ) {
		error_log("deleting service $serviceId");
		db_query_bound('DELETE FROM ' . plugin_table('customers_to_services') . ' WHERE service_id = ?', array( $serviceId ) );
		return db_query_bound('DELETE FROM ' . plugin_table('service') . ' WHERE id = ? ', array ( $serviceId ));
	}
	
	static function deleteCustomer( $customerId ) {
		self::deleteCustomersToServices( $customerId );
		return db_query_bound('DELETE FROM ' . plugin_table('customer') . ' WHERE id = ? ', array ( $customerId ));
	}

	private static function deleteCustomersToServices( $customerId ) {
		db_query_bound('DELETE FROM ' . plugin_table('customers_to_services') . ' WHERE customer_id = ?', array( $customerId ) );
	}
	
	static function saveGroup( $id, $name ) {
		if ( $id == null )
			db_query_bound('INSERT INTO ' . plugin_table('group') . '(name) VALUES (?)', array($name) );
		else
			db_query_bound('UPDATE ' . plugin_table('group') . ' SET name = ? WHERE id = ? ', array($name, $id));
	}

	static function saveService( $id, $name ) {
		if ( $id == null )
			db_query_bound('INSERT INTO ' . plugin_table('service') . '(name) VALUES (?)', array($name) );
		else
			db_query_bound('UPDATE ' . plugin_table('service') . ' SET name = ? WHERE id = ? ', array($name, $id));
	}

	static function saveCustomer( $id, $name, $customerGroupId, $email, $serviceIds ) {
		if ( $id == null ) {
			db_query_bound('INSERT INTO ' . plugin_table('customer') . ' (name, customer_group_id, email) VALUES (?, ?, ?)', array($name, $customerGroupId, $email) );
			$id = db_insert_id( plugin_table('customer'));
		} else {
			db_query_bound('UPDATE ' . plugin_table('customer') . ' SET name = ?, customer_group_id = ?, email = ? WHERE id = ? ', array($name, $customerGroupId, $email, $id));
			self::deleteCustomersToServices( $id );			
		}
		
		foreach ( $serviceIds as $serviceId )
			db_query_bound('
				INSERT INTO ' . plugin_table('customers_to_services') . ' (customer_id, service_id) 
				VALUES (?,?)', array($id, $serviceId));
	}
	
	static function saveBugData( $bugId, $customerId, $serviceId, $isBillable ) {
		
		self::deleteBugData($bugId);
		db_query_bound('
				INSERT INTO ' . plugin_table('bug_data') . ' 
				(bug_id, customer_id, service_id, is_billable) 
				VALUES(?, ?, ?, ?)', array( $bugId, $customerId, $serviceId, $isBillable ));
	}

	static function deleteBugData( $bugId ) {
		db_query_bound('DELETE FROM ' . plugin_table('bug_data') . ' WHERE bug_id = ?', array( $bugId ));
	}

	static function getBugData( $bugId ) {
		$rows = self::toArray(db_query_bound('SELECT * FROM ' . plugin_table('bug_data') . ' WHERE bug_id = ?', array( $bugId)));
		return self::first($rows, null);
	}
	
	private static function first( $array, $default) {
		
		if ( count($array) == 1)
			return $array[0];
		
		return $default;
	}
	
	static function isServiceBillable ( $customerId, $serviceId ) {
		$res = db_fetch_array(db_query_bound('
				SELECT COUNT(*) AS count FROM ' . plugin_table('customers_to_services') . ' 
				WHERE customer_id = ? AND service_id = ?', array ( $customerId, $serviceId) ));
		
		return $res[0]['count'] == 0;
	}
	
	static function getCustomer( $customerId ) {
		$rows = self::toArray(db_query_bound('SELECT * FROM ' . plugin_table('customer') . ' WHERE id = ?', array( $customerId) ));
		return self::first($rows, null);
	}

	static function getService( $serviceId ) {
		$rows = self::toArray(db_query_bound('SELECT * FROM ' . plugin_table('service') . ' WHERE id = ?', array( $serviceId) ));
		return self::first($rows, null);
	}
	
	/**
	 * @param array $customer_ids
	 * @param date $from
	 * @param date $to
	 * @return array ('bug_id', 'customer_id')
	 */
	static function findAllChangedBugIds( $customer_ids, $from, $to ) {
		
		if ( count ( $customer_ids ) == 0 )
			return array();
		
		return self::toArray(db_query_bound(
				'SELECT DISTINCT h.bug_id, customer_id FROM ' . db_get_table('mantis_bug_history_table') . ' h
				LEFT JOIN ' . plugin_table('bug_data') . ' d ON h.bug_id = d.bug_id
				WHERE h.date_modified BETWEEN ? AND ? 
				AND customer_id IN ('.implode( ',', $customer_ids).')',
				array($from, $to)
		));
	}
	
	static function buildFilterArrayForCustomer( $customer_id ) {

		$bug_table = db_get_table('mantis_bug_table');
		$data_table = plugin_table('bug_data');
		return array(
			'join' => "LEFT JOIN $data_table ON $bug_table.id = $data_table.bug_id",
			'where' => "$data_table.customer_id = $customer_id",
		);
	}
}