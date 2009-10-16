<?php
/**
 * This is the template for generating the admin view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
?>
<h1><?php echo $modelClass; ?> Manager</h1>

<p>
<?php echo "<?php echo CHtml::link( 'Create New ' . $modelClass, array( 'create' ) ); ?>"; ?>
</p>

<table class="dataGrid">
	<tr>
		<th><?php echo "<?php echo \$sort->link( '$ID' ); ?>"; ?></th>
<?php foreach ( $columns as $column ) : ?>
		<th><?php echo "<?php echo \$sort->link( '{$column->name}' ); ?>"; ?></th>
<?php endforeach; ?>
		<th>Actions</th>
	</tr>
<?php echo "<?php foreach( \$models as \$n => \$model ) : ?>\n"; ?>
	<tr class="<?php echo "<?php echo \$n%2?'even':'odd';?>"; ?>">
		<td><?php echo "<?php echo CHtml::link( \$model->{$ID},array( 'show', 'id' => \$model->{$ID} ) ); ?>"; ?></td>
<?php foreach ( $columns as $column ) : ?>
		<td><?php echo "<?php echo CHtml::encode( \$model->{$column->name} ); ?>"; ?></td>
<?php endforeach; ?>
		<td>
		<?php echo "<?php echo CHtml::link( 'Edit', array( 'update', 'id' => \$model->{$ID} ) ); ?> | \n"; ?>
		<?php echo "<?php echo CHtml::linkButton( 'Delete', array(
			'submit' => '',
			'params' => array( 'command' => 'delete', 'id' => \$model->{$ID} ),
			'confirm' => \"Are you sure to delete this {$modelClass}?\" ) ); ?>\n"; ?>
		</td>
	</tr>
<?php echo "<?php endforeach; ?>\n"; ?>
</table>
<?php echo "<?php \$this->widget( 'CLinkPager', array( 'pages' => \$pages, 'header' => '' ) ); ?>" ?>
