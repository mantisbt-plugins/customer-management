<?php
require_once( 'core.php' );

access_ensure_global_level( plugin_config_get( 'manage_customers_threshold' ) );

html_page_top( plugin_lang_get( 'manage_customers' ) );

print_manage_menu( plugin_page('manage_customers') );
?>
<h1><?php echo plugin_lang_get( 'manage_customers' ) ?></h1>

<div id="tabs">
	<ul>
		<li><a href="#groups"><?php echo plugin_lang_get( 'customer_groups') ?></a></li>
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
</div>
<form id="group-form" style="display: none" title="<?php echo plugin_lang_get('edit_group'); ?>">
	<input type="hidden" name="id" />
	<label for="name">Name</label> <input type="text" name="name"/> <br />
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
  });
</script>
<?php 
html_page_bottom();
?>