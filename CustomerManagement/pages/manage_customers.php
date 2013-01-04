<?php
require_once( 'core.php' );

access_ensure_global_level( plugin_config_get( 'manage_customers_threshold' ) );

html_page_top( plugin_lang_get( 'manage_customers' ) );

print_manage_menu( plugin_page('manage_customers') );

$groups = CustomerManagementDao::findAllGroups();
$customers = CustomerManagementDao::findAllCustomers(); 
?>
<style type="text/css">
	.ui-dialog-content label {
		display: inline-block;
		width: 60px;
		margin-left: 10px;
		vertical-align: top;
	}
	
	.ui-dialog-content input, .ui-dialog-content select {
		width: 180px;
	}
</style>
<h1><?php echo plugin_lang_get( 'manage_customers' ) ?></h1>

<div id="tabs">
	<ul>
		<li><a href="#customers"><?php echo plugin_lang_get( 'customers') ?></a></li>	
		<li><a href="#groups"><?php echo plugin_lang_get( 'customer_groups') ?></a></li>
		<li><a href="#services"><?php echo plugin_lang_get( 'services') ?></a></li>
	</ul>
	<div id="groups">
		<table class="width50">
			<thead>
				<tr <?php echo helper_alternate_class() ?>>
					<th><?php echo plugin_lang_get('name') ?></th>
					<th><?php echo plugin_lang_get('actions') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $groups as $group ) { ?>
					<tr <?php echo helper_alternate_class() ?>>
						<td><?php echo $group['name']?></td>
						<td>
							<a class="customer-group-delete" href="#" data-customer-count="<?php echo $group['customerCount'] ?>"
								data-group-id="<?php echo $group['id']?>"><?php echo plugin_lang_get( 'delete' ) ?></a>
							<a class="customer-group-edit" href="#" data-group-id="<?php echo $group['id'] ?>" data-group-name="<?php echo $group['name'] ?>"><?php echo plugin_lang_get( 'edit' ) ?></a>
						</td>
					</tr>
				<?php } ?>
				<tr <?php echo helper_alternate_class() ?>>
					<td colspan="2"><a href="#" class="customer-group-edit"><?php echo plugin_lang_get('add_new_group'); ?></a></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="services">
		<table class="width50">
			<thead>
				<tr <?php echo helper_alternate_class() ?>>
					<th><?php echo plugin_lang_get('name') ?></th>
					<th><?php echo plugin_lang_get('actions') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( CustomerManagementDao::findAllServices() as $service ) { ?>
					<tr <?php echo helper_alternate_class() ?>>
						<td><?php echo $service['name']?></td>
						<td>
							<a class="service-delete" href="#" data-customer-count="<?php echo $service['customerCount'] ?>"
								data-service-id="<?php echo $service['id']?>"><?php echo plugin_lang_get( 'delete' ) ?></a>
							<a class="service-edit" href="#" data-service-id="<?php echo $service['id'] ?>" data-service-name="<?php echo $service['name'] ?>"><?php echo plugin_lang_get( 'edit' ) ?></a>
						</td>
					</tr>
				<?php } ?>
				<tr <?php echo helper_alternate_class() ?>>
					<td colspan="2"><a href="#" class="service-edit"><?php echo plugin_lang_get('add_new_service'); ?></a></td>
				</tr>
			</tbody>
		</table>	
	</div>
	<div id="customers">
		<table class="width50">
			<thead>
				<tr <?php echo helper_alternate_class() ?>>
					<th><input type="checkbox" class="email-select-all"/></th>
					<th><?php echo plugin_lang_get('name') ?></th>
					<th><?php echo plugin_lang_get('group') ?></th>
					<th><?php echo plugin_lang_get('services') ?></th>
					<th><?php echo plugin_lang_get('actions') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $groups as $group ) { ?>
					<tr>
						<td><input class="emailSelector" data-customer-group-id="<?php echo $group['id']?>" type="checkbox"/></td>
						<td colspan="4"><?php echo $group['name']?></td>
					</tr>
					
				<?php 		
						foreach ( $customers as $customer ) {
							if ( $customer['customer_group_id'] != $group['id'] )
								continue; 
				?>
					<tr <?php echo helper_alternate_class() ?>>
						<td><input class="emailSelector" data-customer-group-id="<?php echo $group['id']?>" 
							data-customer-id="<?php echo $customer['id'] ?>" data-customer-name="<?php echo $customer['name'] ?>" type="checkbox"/></td>
						<td><?php echo $customer['name']?></td>
						<td><?php echo $customer['groupName']?></td>
						<td><?php 
							$serviceNames = array();
							$serviceIds = array();
							foreach ( $customer['services'] as $service ) {
								$serviceNames[] = $service['name'];
								$serviceIds[] = $service['id'];
							}
							
							echo implode(',', $serviceNames);
							?>
						</td>
						<td>
							<a class="customer-delete" href="#" data-customer-id="<?php echo $customer['id']?>"><?php echo plugin_lang_get( 'delete' ) ?></a>
							<a class="customer-edit" href="#" data-customer-id="<?php echo $customer['id'] ?>" data-customer-name="<?php echo $customer['name'] ?>"
								data-customer-email="<?php echo $customer['email']?>" data-group-id="<?php echo $customer['customer_group_id']; ?>" 
								data-service-id="[<?php echo implode(",", $serviceIds); ?>]"><?php echo plugin_lang_get( 'edit' ) ?></a>
						</td>
					</tr>
				<?php
						}
					} 
				?>
				<tr <?php echo helper_alternate_class() ?>>
					<td colspan="5">
						<a href="#" class="customer-edit"><?php echo plugin_lang_get('add_new_customer'); ?></a>
						<hr />
						<label><?php echo plugin_lang_get('date_from'); ?></label>
						<input type="text" class="datepicker" name="from"/>
						<label><?php echo plugin_lang_get('date_to'); ?></label>
						<input type="text" class="datepicker" name="to"/>

						<a href="#" class="email-preview"><?php echo plugin_lang_get('preview_email_notifications'); ?></a>
					</td>
				</tr>
			</tbody>
		</table>		
	</div>
</div>
<form id="group-form" style="display: none" title="<?php echo plugin_lang_get('edit_group'); ?>">
	<input type="hidden" name="id" />
	<br />
	<label for="name"><?php echo plugin_lang_get('name') ?></label> <input type="text" name="name"/> <br />
</form>
<form id="service-form" style="display: none" title="<?php echo plugin_lang_get('edit_service'); ?>">
	<input type="hidden" name="id" />
	<br />
	<label for="name"><?php echo plugin_lang_get('name') ?></label> <input type="text" name="name"/> <br />
</form>
<form id="customer-form" style="display: none" title="<?php echo plugin_lang_get('edit_customer'); ?>">
	<input type="hidden" name="id" />
	<br />
	<label for="name"><?php echo plugin_lang_get('name') ?></label> <input type="text" name="name"/> <br />
	<label for="name"><?php echo plugin_lang_get('email') ?></label> <input type="text" name="email"/> <br />
	<label for="customer_group_id"><?php echo plugin_lang_get('group') ?></label> <select name="customer_group_id">
	<?php foreach ( CustomerManagementDao::findAllGroups() as $group ) { ?>
		<option value="<?php echo $group['id']; ?>"><?php echo $group['name']?></option>
	<?php } ?>
	</select> <br />
	<label for="service_id[]"><?php echo plugin_lang_get('services') ?></label> <select name="service_id[]" multiple="multiple">
	<?php foreach ( CustomerManagementDao::findAllServices() as $service ) { ?>
		<option value="<?php echo $service['id']; ?>"><?php echo $service['name']?></option>
	<?php } ?>	
	</select> 
</form>
<script>
jQuery(document).ready(function($) {

	$('#tabs').tabs();

	// use ISO 8601 date format ; we don't have a proper bridge to the MantisBT date format yet
	$('.datepicker').datepicker( {'maxDate': 0, 'dateFormat' : 'yy-mm-dd'});

	var api = new CustomerManagement({
		'entryPoint' : '<?php echo plugin_page('manage_customers_actions') ; ?>',
		'csrfToken' : '<?php echo form_security_token('manage_customers') ; ?>'
	});
	var ui = CustomerManagementUi;
	
	$('.customer-group-delete').click(function() {

		if ( $(this).data('customerCount') > 0 ) {
			ui.error("<?php echo plugin_lang_get('unable_to_delete_group_has_customers'); ?>");
			return;
		}

		if ( !ui.confirm("<?php echo plugin_lang_get('confirm_delete_group'); ?>") )
			return;

		api.deleteGroup($(this).data('group-id'), function() {
			window.location.reload();
		});
	});

	$('.service-delete').click(function() {

		if ( $(this).data('customerCount') > 0 ) {
			ui.error("<?php echo plugin_lang_get('unable_to_delete_service_has_customers'); ?>");
			return;
		}

		if ( !ui.confirm("<?php echo plugin_lang_get('confirm_delete_service'); ?>") )
			return;

		api.deleteService($(this).data('service-id'), function() {
			window.location.reload();
		});
	});

	$('.customer-delete').click(function() {

		if ( !ui.confirm("<?php echo plugin_lang_get('confirm_delete_customer'); ?>") )
			return;

		api.deleteCustomer($(this).data('customer-id'), function() {
			window.location.reload();
		});
	});
	
	$('.customer-group-edit').click(function() {

		var id = $(this).data('group-id');
		var name = $(this).data('group-name');

		var form = $('#group-form');
		form.find('input[name=id]').val(id);
		form.find('input[name=name]').val(name);
		
		form.dialog({
			'modal' : true,
			buttons: {
				'<?php echo plugin_lang_get('save'); ?>' : function() {
					api.saveGroup(form.serializeObject(), function() {
						window.location.reload();
					});
				},
				'<?php echo plugin_lang_get('cancel'); ?>' : function() {
					$(this).dialog('close');
					form.get(0).reset();
				}
			}
		});
	});

	$('.service-edit').click(function() {

		var id = $(this).data('service-id');
		var name = $(this).data('service-name');

		var form = $('#service-form');
		form.find('input[name=id]').val(id);
		form.find('input[name=name]').val(name);
		
		form.dialog({
			'modal' : true,
			buttons: {
				'<?php echo plugin_lang_get('save'); ?>' : function() {
					api.saveService(form.serializeObject(), function() {
						window.location.reload();
					});
				},
				'<?php echo plugin_lang_get('cancel'); ?>' : function() {
					$(this).dialog('close');
					form.get(0).reset();
				}
			}
		});
	});

	$('.customer-edit').click(function() {

		var id = $(this).data('customer-id');
		var name = $(this).data('customer-name');
		var groupId = $(this).data('group-id');
		var serviceId = $(this).data('service-id');
		var email = $(this).data('customer-email');

		var form = $('#customer-form');
		form.find('input[name=id]').val(id);
		form.find('input[name=name]').val(name);
		form.find('select[name=customer_group_id]').val(groupId);
		form.find('select[name="service_id[]"]').val(serviceId);
		form.find('input[name=email]').val(email);
				
		form.dialog({
			'modal' : true,
			buttons: {
				'<?php echo plugin_lang_get('save'); ?>' : function() {
					api.saveCustomer(form.serializeObject(), function() {
						window.location.reload();
					});
				},
				'<?php echo plugin_lang_get('cancel'); ?>' : function() {
					$(this).dialog('close');
					form.get(0).reset();
				}
			}
		});
	});

	$('.emailSelector').click(function() {

		// just a customer id, allow default behaviour
		if ( $(this).data('customer-id') )
			return;

		// customer group id switch , propagate to all matching customers
		if ( $(this).data('customer-group-id') ) {
			$(".emailSelector[data-customer-group-id=" + $(this).data('customer-group-id') + "]" )
				.prop('checked', $(this).prop('checked') );
		}
	});

	$('.email-select-all').click(function() {
		$('.emailSelector').prop('checked', true);
		return false;
	});

	$('.email-preview').click(function() {

		var ids = [];
		
		$('.emailSelector:checked').each(function(index, value) {

			var customerId = $(value).data('customer-id');
			if ( customerId )
				ids.push(customerId);
		});

		if ( ids.length == 0 )  {
			ui.error("<?php echo plugin_lang_get('no_entries_selected_for_notification'); ?>");
			return;
		}

		var from = $('#customers').find('[name=from]');
		var to = $('#customers').find('[name=to]');

		var errors = [];
		if ( !from.val() )
			errors.push('<?php echo plugin_lang_get('notification_from_date_required'); ?>');
		if ( !to.val() )
			errors.push('<?php echo plugin_lang_get('notification_to_date_required'); ?>');

		if ( errors.length != 0 ) {
			ui.error(errors.join("\n"));
			return;
		}

		var fromAsDate = new Date(from.val());
		var toAsDate = new Date(to.val());

		if ( fromAsDate > toAsDate ) {
			ui.error('<?php echo plugin_lang_get('notification_from_date_must_be_before_end_date'); ?>');
			return;
		}

		var errorWithDefault = function(message) {
			if ( !message) 
				message = '<?php echo plugin_lang_get('unspecified_error') ?>';

			ui.error(message);
		}

		var payload = {
			'from' : from.val(),
			'to' : to.val(),
			'customer_id[]' : ids
		}
		
		api.previewNotification(payload, function(emails) { 
			var prop;
			var notification = $('<div>').attr('title', '<?php echo plugin_lang_get('email_preview') ?>');

			for ( prop in emails ) {

				var email = emails[prop];

				$('<h3>').text(email['subject']).appendTo(notification);
				$('<pre>').text(email['body']).appendTo(notification);
			}

			var printUrl = '<?php echo plugin_page('print_notifications.php') ?>';
			printUrl += '&from=' + payload.from;
			printUrl += '&to=' + payload.to;
			for ( var index in ids ) {
				printUrl += '&customer_id[]=' + ids[index];
			}
			

			var actionBar = $('<div>').attr('class','action-bar');
			$('<a>').attr('href',printUrl).attr('target','_blank').text('<?php echo plugin_lang_get('print') ?>').appendTo(actionBar);
			$('<a>').attr('class', 'email-send').text('<?php echo plugin_lang_get('send') ?>').click(function() {
				if ( !ui.confirm('<?php echo plugin_lang_get('send_notification_confirm'); ?>') )
					return;

				api.sendNotification(payload, function() {
					window.location.reload();
				}, errorWithDefault);
			
			}).appendTo(actionBar);
			actionBar.appendTo(notification);
 
			notification.dialog({ 'width': '80%' });

			
		}, errorWithDefault);

		return false;
	});
  });
</script>
<?php 
html_page_bottom();
?>