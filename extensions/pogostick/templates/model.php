<?php
/**
 * This is the template for generating a model class file.
 * The following variables are available in this template:
 * - $className: the class name
 * - $tableName: the table name
 * - $columns: a list of table column schema objects
 * - $rules: a list of validation rules (string)
 * - $labels: a list of labels (string)
 * - $relations: a  list of relations (string)
 */

echo <<<HTML
<?php
/**
 * $className class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC.
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage models
 * @since v1.0.6
 * @version SVN: \$Revision\$
 * @modifiedby \$LastChangedBy\$
 * @lastmodified  \$Date\$
 */
class $className extends CPSModel
{
	/**
	* The followings are the available columns in table '$tableName':

HTML;
	
	foreach ( $columns as $column ) echo '	* @var ' . $column->type . ' $' . $column->name . "\n";

echo <<<HTML
	*/
	 
	/**
	* Returns the static model of the specified AR class.
	* @return CActiveRecord the static model class
	*/
	public static function model( \$sClassName = __CLASS__ )
	{
		return parent::model( \$sClassName );
	}
	
	/**
	* @return string the associated database table name
	*/
	public function tableName()
	{
		return self::getTablePrefix() . '$tableName';
	}

	/**
	* @return array validation rules for model attributes.
	*/
	public function rules()
	{
		return array(

HTML;

	foreach ( $rules as $rule ) echo '			' . $rule . ",\n";
	
echo <<<HTML
		);
	}

	/**
	* @return array relational rules.
	*/
	public function relations()
	{
		return array(

HTML;

	foreach ( $relations as $name => $relation ) echo "			'$name' => $relation,\n";
	
echo <<<HTML
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(

HTML;

	foreach ( $labels as $column => $label ) echo "			'$column' => '$label',\n";
	
echo <<<HTML
		);
	}

	/**
	 * @return array customized tooltips (attribute=>tip)
	 */
	public function attributeTooltips()
	{
		return array(

HTML;

	foreach ( $labels as $column => $label ) echo "			'$column' => '$label',\n";
	
echo <<<HTML
		);
	}
}

HTML;
