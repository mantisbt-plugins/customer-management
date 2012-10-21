<?php
require_once( 'core.php' );

access_ensure_global_level( plugin_config_get( 'manage_customers_threshold' ) );

html_page_top( plugin_lang_get( 'manage_customers' ) );

print_manage_menu( plugin_page('manage_customers') );
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
					<th>Name</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( CustomerManagementDao::findAllGroups() as $group ) { ?>
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
					<th>Name</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( CustomerManagementDao::findAllServices() as $service ) { ?>
					<tr <?php echo helper_alternate_class() ?>>
						<td><?php echo $service['name']?></td>
						<td>
							<a class="service-delete" href="#" data-customer-count="<?php echo $service['customerCount'] ?>"
								data-service-id="<?php echo $service['id']?>"><?php echo plugin_lang_get( 'delete' ) ?></a>
							<a class="service-edit" href="#" data-group-id="<?php echo $service['id'] ?>" data-service-name="<?php echo $service['name'] ?>"><?php echo plugin_lang_get( 'edit' ) ?></a>
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
					<th>Name</th>
					<th>Group</th>
					<th>Services</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( CustomerManagementDao::findAllCustomers() as $customer ) { ?>
					<tr <?php echo helper_alternate_class() ?>>
						<td><?php echo $customer['name']?></td>
						<td><?php echo $customer['groupName']?></td>
						<td><?php 
							$serviceNames = array();
							foreach ( $customer['services'] as $service )
								$serviceNames[] = $service['name'];
							
							echo implode(',', $serviceNames);
							?>
						</td>
						<td>
							<a class="customer-delete" href="#" data-customer-id="<?php echo $customer['id']?>"><?php echo plugin_lang_get( 'delete' ) ?></a>
							<a class="customer-edit" href="#" data-customer-id="<?php echo $customer['id'] ?>" data-customer-name="<?php echo $customer['name'] ?>"><?php echo plugin_lang_get( 'edit' ) ?></a>
						</td>
					</tr>
				<?php } ?>
				<tr <?php echo helper_alternate_class() ?>>
					<td colspan="4"><a href="#" class="customer-edit"><?php echo plugin_lang_get('add_new_customer'); ?></a></td>
				</tr>
			</tbody>
		</table>		
	</div>
</div>
<form id="group-form" style="display: none" title="<?php echo plugin_lang_get('edit_group'); ?>">
	<input type="hidden" name="id" />
	<br />
	<label for="name">Name</label> <input type="text" name="name"/> <br />
</form>
<form id="service-form" style="display: none" title="<?php echo plugin_lang_get('edit_service'); ?>">
	<input type="hidden" name="id" />
	<br />
	<label for="name">Name</label> <input type="text" name="name"/> <br />
</form>
<form id="customer-form" style="display: none" title="<?php echo plugin_lang_get('edit_customer'); ?>">
	<input type="hidden" name="id" />
	<br />
	<label for="name">Name</label> <input type="text" name="name"/> <br />
	<label for="customer_group_id">Group</label> <select name="customer_group_id">
	<?php foreach ( CustomerManagementDao::findAllGroups() as $group ) { ?>
		<option value="<?php echo $group['id']; ?>"><?php echo $group['name']?></option>
	<?php } ?>
	</select> <br />
	<label for="services">Services</label> <select name="services" multiple="multiple">
	<?php foreach ( CustomerManagementDao::findAllServices() as $service ) { ?>
		<option value="<?php echo $service['id']; ?>"><?php echo $service['name']?></option>
	<?php } ?>	
	</select> 
</form>
<script type="text/javascript" src="<?php echo plugin_file('customer-management.js'); ?>"></script>
<script>
jQuery(document).ready(function($) {

	$('#tabs').tabs();

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

		api.deleteService($(this).data('group-id'), function() {
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

		var form = $('#customer-form');
		form.find('input[name=id]').val(id);
		form.find('input[name=name]').val(name);
		
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
  });
</script>
<?php 
html_page_bottom();
?>