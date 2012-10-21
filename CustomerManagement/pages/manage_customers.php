<?php
require_once( 'core.php' );

access_ensure_global_level( plugin_config_get( 'manage_customers_threshold' ) );

html_page_top( plugin_lang_get( 'manage_customers' ) );

?>
<h1><?php echo plugin_lang_get( 'manage_customers' ) ?></h1>
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
					<a class="customer-group-edit" href="#" data-group-id="<?php echo $group['id'] ?>"><?php echo plugin_lang_get( 'edit' ) ?></a>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<script type="text/javascript" src="<?php echo plugin_file('customer-management.js'); ?>"></script>
<script>
	jQuery('.customer-group-delete').click(function() {

		if ( jQuery(this).data('customerCount') > 0 ) {
			window.alert('Unable to delete group since at least one customer is linked to it.');
			return;
		}
		
		if ( !window.confirm("Are you sure you want to delete this customer group?") ) 
			return;
		
		CustomerManagement.deleteCustomerGroup(jQuery(this).data('group-id'), function() {
			window.location.reload();
		});
	});
</script>
<?php 
html_page_bottom();
?>