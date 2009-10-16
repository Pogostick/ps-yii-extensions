<?php
/**
 * This is the template for generating the list view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
?>
<h1><?php echo $modelClass; ?> List</h1>
<p>
	<?php echo "<?php echo CHtml::link( 'Create New {$modelClass}', array( 'create' ) ); ?>"; ?> |
	<?php echo "<?php echo CHtml::link( '{$modelClass} Manager}', array( 'create' ) ); ?>"; ?>
</p>	

<?php echo "<?php \$this->widget('CLinkPager',array('pages'=>\$pages)); ?>" ?>

<?php echo "<?php foreach(\$models as \$n=>\$model): ?>\n"; ?>
<div class="item">
<?php echo "<?php echo CHtml::encode(\$model->getAttributeLabel('{$ID}')); ?>"; ?>:
<?php echo "<?php echo CHtml::link(\$model->{$ID},array('show','id'=>\$model->{$ID})); ?>"; ?>
<br/>
<?php foreach($columns as $column): ?>
<?php echo "<?php echo CHtml::encode(\$model->getAttributeLabel('{$column->name}')); ?>"; ?>:
<?php echo "<?php echo CHtml::encode(\$model->{$column->name}); ?>"; ?>
<br/>
<?php endforeach; ?>
</div>
<?php echo "<?php endforeach; ?>\n"; ?>
<br/>
<?php echo "<?php \$this->widget('CLinkPager',array('pages'=>\$pages)); ?>" ?>
