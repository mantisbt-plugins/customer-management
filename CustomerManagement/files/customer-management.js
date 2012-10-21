var CustomerManagement = function(options) {
	this.entryPoint = options.entryPoint;
	this.csrfToken = options.csrfToken;
};

CustomerManagement.prototype.deleteCustomerGroup = function(customerGroupId, success) {
	jQuery.post(this.entryPoint,
		{'action': 'deleteGroup', 'crsfToken' : this.csrfToken, 'customerGroupId': customerGroupId}
	).done(success.call());
}

var CustomerManagementUi = {};

CustomerManagementUi.confirm = function(message) {
	return window.confirm(message);
}

CustomerManagementUi.error = function(message) {
	window.alert(error);
}