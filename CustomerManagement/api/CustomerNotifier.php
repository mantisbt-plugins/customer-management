<?php 
class CustomerNotifier {
	
	const SECONDS_IN_DAY = 86400;
	
	/**
	 * Notifies selected customers about changes made in bugs reports they are linked to
	 * 
	 * @param array $customer_ids the ids of the customer to notify
	 * @param string $from the start of the interval
	 * @param string $to the end of the interval
	 * 
	 * @return array notified customers
	 */
	static function notifyCustomers( $customer_ids, $from, $to ) {
		
		$notifiedCustomerIds = array();
		
		$fromDate = self::startOfDay(strtotime($from));
		$toDate = self::endOfDay(strtotime($to));
		
		$bugChanges = CustomerManagementDao::findAllBugChanges( $customer_ids, $fromDate, $toDate);

		foreach ( $customer_ids as $customer_id ) {
			
			$changesForCustomer = array();
			foreach ( $bugChanges as $bugChange ) {
				if ( $bugChange['customer_id'] == $customer_id ) {
					$changesForCustomer[] = array(
							'bug' => bug_get( $bugChange['bug_id'])
					);
				}
			}
			
			if ( count($changesForCustomer) > 0 ) {
				$text = "The following updates have been made between ${from} and ${to}:\n\n";
				$text .= "Resolved bugs: ";
				
				foreach ( $changesForCustomer as $changeForCustomer) {
					
					$text .= ' - ' .$changeForCustomer['bug']->summary ."\n";
				}
				
				$customer = CustomerManagementDao::getCustomer($customer_id);
				
				$email = new EmailData();
				$email->email = $customer['email'];
				$email->subject = 'MantisBT notification';
				$email->body = $text;
				$email->metadata['priority'] = config_get( 'mail_priority' );
				$email->metadata['charset'] = 'utf-8';
				
				email_send($email);
				
				$notifiedCustomerIds[] = $customer_id;
			}
		}
	}
	
	private static function startOfDay( $timestamp ) {
		return $timestamp / self::SECONDS_IN_DAY * self::SECONDS_IN_DAY ;
	}

	private static function endOfDay( $timestamp ) {
		return self::startOfDay( $timestamp) + ( self::SECONDS_IN_DAY - 1);
	}
}
?>