var CustomerManagement = { };
CustomerManagement.deleteCustomerGroup = function(customerGroupId, success) {
	jQuery.post('plugin.php?page=CustomerManagement/manage_customers_actions',
		{'action': 'deleteGroup', 'customerGroupId': customerGroupId}
	).done(success.call());
}