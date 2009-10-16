<?php
/**
 * This is the template for generating the create view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
?>
<h1>Edit <?php echo $modelClass." <?php echo \$model->{$ID}; ?>"; ?></h1>
<p>
<?php echo "<?php echo CHtml::link( 'Cancel', array( 'admin' ) ); ?> |"; ?>
<?php echo "<?php echo CHtml::link( '{$modelClass} Manager', array( 'admin' ) ); ?>"; ?>
</p>

<?php echo "<?php echo \$this->renderPartial( '_form', array(
	'model' => \$model,
	'_oModel' => \$model,
	'_bUpdate' => true,
	'update' => true,
)); ?>"; ?>
