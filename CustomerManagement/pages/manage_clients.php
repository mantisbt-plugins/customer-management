<?php
require_once( 'core.php' );

access_ensure_global_level( plugin_config_get( 'manage_customers_threshold' ) );

html_page_top( plugin_lang_get( 'manage_customers' ) );

?>

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
					<a class="delete" data-group-id="<?php echo $group['id']?>"><?php echo plugin_lang_get( 'delete' ) ?></a>
					<a class="edit" data-group-id="<?php echo $group['id'] ?>"><?php echo plugin_lang_get( 'edit' ) ?></a>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>

<?php 
html_page_bottom();
?>