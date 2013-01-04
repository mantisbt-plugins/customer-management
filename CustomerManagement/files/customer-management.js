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
		{'action': 'deleteService', 'manage_customers_token' : this.csrfToken, 'serviceId': serviceId}
	).done(success.call());
}

CustomerManagement.prototype.deleteCustomer = function(customerId, success) {
	jQuery.post(this.entryPoint,
			{'action': 'deleteCustomer', 'manage_customers_token' : this.csrfToken, 'customerId': customerId}
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

CustomerManagement.prototype.saveCustomer = function(data, success) {
	var payload = {'action': 'saveCustomer', 'manage_customers_token' : this.csrfToken };
	
	jQuery.post(this.entryPoint, jQuery.extend(payload, data) )
		.done(success.call());
}

CustomerManagement.prototype.sendNotification = function(data, success, error) {
	var payload = {'action': 'sendNotification', 'manage_customers_token' : this.csrfToken };
	
	jQuery.post(this.entryPoint, jQuery.extend(payload, data) )
		.done(function(result) {
			if ( !result ) {
				error.call();
				return;
			}
			
			if ( result.status == 'ERROR') {
				error(result.type + " : " + result.contents);
				return;
			}
			
			success.call();
		})
		.fail(function(data) {
			error.call();
		});
}

CustomerManagement.prototype.previewNotification = function(data, success, error) {
	var payload = {'action': 'previewNotification', 'manage_customers_token' : this.csrfToken };
	
	jQuery.post(this.entryPoint, jQuery.extend(payload, data) )
		.done(function(result) {
			if ( !result ) {
				error.call();
				return;
			}
			
			if ( result.status == 'ERROR') {
				error(result.type + " : " + result.contents);
				return;
			}
			
			success.call(null, result.contents);
		})
		.fail(function(data) {
			error.call();
		});
}

var CustomerManagementUi = {};

CustomerManagementUi.confirm = function(message) {
	return window.confirm(message);
}

CustomerManagementUi.error = function(message) {
	window.alert(message);
}

var CustomerManagementBugUi = function(customerIdToServiceId) {
	this.customerIdToServiceId = customerIdToServiceId;
};

CustomerManagementBugUi.prototype.init = function(options) {
	
	var that = this;
	
	var updateBillableField = function() {
		var customerId = jQuery('#cm_plugin_customer_id').val(); 
		var serviceId = jQuery('#cm_plugin_service_id').val();
		var isBillableField = jQuery("#cm_plugin_is_billable");

		if ( customerId && serviceId) {
			var isAssociatedService = false;
			
			var servicesForThisCustomer =  that.customerIdToServiceId[customerId];
			for ( var i = 0 ; i < servicesForThisCustomer.length; i++ )
				if ( servicesForThisCustomer[i] == serviceId ) 
					isAssociatedService = true;
			
			isBillableField.prop('checked', !isAssociatedService);
		} else {
			isBillableField.prop('checked', false);
		}
	}
	
	jQuery('#cm_plugin_customer_id').change(updateBillableField);
	jQuery('#cm_plugin_service_id').change(updateBillableField);
	
	if ( options.prependCustomerName ) {
		jQuery('#cm_plugin_customer_id').parents('form').submit(function() {
			var customerName = jQuery('#cm_plugin_customer_id').find(':selected').text();
			if ( !customerName )
				return;
			var summaryField = jQuery(this).find('[name=summary]');
			var oldSummary = summaryField.val();
			summaryField.val('['+customerName+'] ' + oldSummary);
		});
	}
}