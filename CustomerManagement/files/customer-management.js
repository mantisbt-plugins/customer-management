// courtesy of http://stackoverflow.com/a/1186309/112671
jQuery.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    jQuery.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

var CustomerManagement = function(options) {
	this.entryPoint = options.entryPoint;
	this.csrfToken = options.csrfToken;
};

CustomerManagement.prototype.deleteGroup = function(customerGroupId, success) {
	jQuery.post(this.entryPoint,
		{'action': 'deleteGroup', 'manage_customers_token' : this.csrfToken, 'customerGroupId': customerGroupId}
	).done(success.call());
}

CustomerManagement.prototype.deleteService = function(serviceId, success) {
	jQuery.post(this.entryPoint,
		{'action': 'deleteService', 'manage_customers_token' : this.csrfToken, 'serviceId': customerGroupId}
	).done(success.call());
}

CustomerManagement.prototype.saveGroup = function(data, success) {
	
	var payload = {'action': 'saveGroup', 'manage_customers_token' : this.csrfToken };

	jQuery.post(this.entryPoint, jQuery.extend(payload, data) )
		.done(success.call());
}

CustomerManagement.prototype.saveService = function(data, success) {
	
	var payload = {'action': 'saveService', 'manage_customers_token' : this.csrfToken };
	
	jQuery.post(this.entryPoint, jQuery.extend(payload, data) )
		.done(success.call());
}

var CustomerManagementUi = {};

CustomerManagementUi.confirm = function(message) {
	return window.confirm(message);
}

CustomerManagementUi.error = function(message) {
	window.alert(message);
}