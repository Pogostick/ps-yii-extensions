/**
 * <?=$className?> class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC.
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage models
 * @since v1.0.6
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
class <?=$className?> extends CPSModel
{
	/**
	* The followings are the available columns in table '<?php echo $tableName; ?>':
<?php foreach($columns as $column): ?>
	* @var <?php echo $column->type.' $'.$column->name."\n"; ?>
<?php endforeach; ?>
	*/
	 
	/**
	* Returns the static model of the specified AR class.
	* @return CActiveRecord the static model class
	*/
	public static function model( $sClassName = __CLASS__ )
	{
		return parent::model( $sClassName );
	}
	
	/**
	* @return string the associated database table name
	*/
	public function tableName()
	{
		return self::getTablePrefix() . '<?php echo $tableName; ?>';
	}

	/**
	* @return array validation rules for model attributes.
	*/
	public function rules()
	{
		return array(
<?php foreach($rules as $rule): ?>
			<?php echo $rule.",\n"; ?>
<?php endforeach; ?>
		);
	}

	/**
	* @return array relational rules.
	*/
	public function relations()
	{
		return array(
<?php foreach($relations as $name=>$relation): ?>
			<?php echo "'$name' => $relation,\n"; ?>
<?php endforeach; ?>
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
<?php foreach ( $labels as $label ): ?>
			<?php echo $label.",\n"; ?>
<?php endforeach; ?>
		);
	}

	/**
	 * @return array customized tooltips (attribute=>tip)
	 */
	public function attributeTooltips()
	{
		return array(
<?php foreach ( $labels as $label ): ?>
			<?php echo $label.",\n"; ?>
<?php endforeach; ?>
		);
	}
}