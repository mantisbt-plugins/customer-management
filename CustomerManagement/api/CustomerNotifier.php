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

		foreach ( self::buildNotificationEmails($customer_ids, $from, $to) as $email) {
			email_send($email);
		}
	}
	
	/**
	 * Builds notification emails for the selected customers about changes made in bugs reports they are linked to
	 *
	 * @param array $customer_ids the ids of the customer to notify
	 * @param string $from the start of the interval
	 * @param string $to the end of the interval
	 *
	 * @return array notified customers
	 */
	static function buildNotificationEmails($customer_ids, $from, $to) {
		
		$emails = array();
		
		lang_push( plugin_config_get('email_notification_language'));
		
		$fromDate = self::startOfDay(strtotime($from));
		$toDate = self::endOfDay(strtotime($to));
		
		$changedBugIds = CustomerManagementDao::findAllChangedBugIds( $customer_ids, $fromDate, $toDate);
		
		$dateFormat = config_get( 'short_date_format' );
		
		foreach ( $customer_ids as $customer_id ) {
				
			$changesForCustomer = array();
			foreach ( $changedBugIds as $changedBugId) {
				if ( $changedBugId['customer_id'] == $customer_id ) {
					$changesForCustomer[] = array(
							'bug' => bug_get( $changedBugId['bug_id'])
					);
				}
			}
				
			if ( count($changesForCustomer) > 0 ) {
		
				$counter = 0;
				$text = '';
				foreach ( $changesForCustomer as $changeForCustomer) {
					$counter++;
						
					$bugId = $changeForCustomer['bug']->id;
						
					$text .= $counter .'. ';
					$text .= sprintf(plugin_lang_get('email_notification_bug_header'), $changeForCustomer['bug']->id,
							$changeForCustomer['bug']->summary , date( $dateFormat, $changeForCustomer['bug']->date_submitted ),
							get_enum_element('status', $changeForCustomer['bug']->status));
					$text .= "\n";
		
					$reporterName = user_get_name($changeForCustomer['bug']->reporter_id);
					$reporterEmail = user_get_email($changeForCustomer['bug']->reporter_id);
						
					$text .= sprintf(plugin_lang_get('email_notification_bug_reported_by'), $reporterName, $reporterEmail);
					$text .= "\n";
		
					$text .= sprintf(plugin_lang_get('email_notification_bug_description'), $changeForCustomer['bug']->description);
					$text .= "\n\n";
				}
		
				$customer = CustomerManagementDao::getCustomer($customer_id);
		
				$email = new EmailData();
				$email->email = $customer['email'];
				$email->subject = sprintf(plugin_lang_get('email_notification_title'), $customer['name'], $from, $to);
				$email->body = $text;
				$email->metadata['priority'] = config_get( 'mail_priority' );
				$email->metadata['charset'] = 'utf-8';
		
				array_push($emails, $email);
			}
		}
		
		lang_pop();
		
		return $emails;
	}
	
	private static function startOfDay( $timestamp ) {
		return $timestamp / self::SECONDS_IN_DAY * self::SECONDS_IN_DAY ;
	}

	private static function endOfDay( $timestamp ) {
		return self::startOfDay( $timestamp) + ( self::SECONDS_IN_DAY - 1);
	}
}
?>