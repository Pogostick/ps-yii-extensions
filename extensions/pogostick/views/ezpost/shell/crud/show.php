<?php
/**
 * This is the template for generating the show view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
?>
<h1><?php echo $modelClass . " : <?php echo \$model->{$ID}; ?>"; ?></h1>

<p>
	<?php echo "<?php echo CHtml::link( 'Create New {$modelClass}', array( 'create' ) ); ?>"; ?> |
	<?php echo "<?php echo CHtml::link( 'Edit', array( 'update', array( 'id' => \$model->id ) ) ); ?>"; ?> |
	<?php echo "<?=CHtml::linkButton( 'Delete This {$modelClass}', array( 'submit' => array( 'delete', 'id' => \$model->id ), 'confirm' => 'Do you really want to delete this {$modelClass}?' ) ); ?> |
	<?php echo "<?php echo CHtml::link( '{$modelClass Manager}', array( 'create' ) ); ?>"; ?>
</p>	

<table class="dataGrid">
<?php foreach($columns as $name=>$column): ?>
	<tr>
		<th class="label"><?php echo "<?php echo CHtml::encode(\$model->getAttributeLabel('$name')); ?>\n"; ?></th>
	    <td><?php echo "<?php echo CHtml::encode(\$model->{$name}); ?>\n"; ?></td>
	</tr>
<?php endforeach; ?>
</table>
