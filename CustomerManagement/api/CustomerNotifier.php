<?php 
class CustomerNotifier {
	
	const SECONDS_IN_DAY = 86400;
	
	/**
	 * Notifies selected customers about changes made in bugs reports they are linked to
	 * 
	 * @param array $customer_ids the ids of the customer to notify
	 * @param string $from the start of the interval
	 * @param string $to the end of the interval
	 */
	static function notifyCustomers( $customer_ids, $from, $to ) {
		
		$fromDate = self::startOfDay(strtotime($from));
		$toDate = self::endOfDay(strtotime($to));
		
		$bugChanges = CustomerManagementDao::findAllBugChanges( $customer_ids, $fromDate, $toDate);

		trigger_error("Got " . count ( $bugChanges ) . " bug changes");
	}
	
	private static function startOfDay( $timestamp ) {
		return $timestamp / self::SECONDS_IN_DAY * self::SECONDS_IN_DAY ;
	}

	private static function endOfDay( $timestamp ) {
		return self::startOfDay( $timestamp) + ( self::SECONDS_IN_DAY - 1);
	}
}
?>