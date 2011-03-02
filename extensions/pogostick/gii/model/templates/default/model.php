<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
/**
 * This is the template for generating a model class file.
 * The following variables are available in this template:
 * - $this: the ModelCode object
 * - $tableName: the table name for this class (prefix is already removed if necessary)
 * - $modelClass: the model class name
 * - $columns: list of table columns (name=>CDbColumnSchema)
 * - $labels: list of attribute labels (name=>label)
 * - $rules: list of validation rules
 * - $relations: list of relations (name=>relation declaration)
 * - $baseClass: the base class to extend
 *
 * @package 	psYiiExtensions
 * @subpackage 	gii.model.templates.default
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	$Id$
 * @since 		v2.0.0
 *
 * @filesource
 *
 */

//	Include our header
include Yii::getPathOfAlias( 'pogostick.templates.crud' ) . '/build_template_header.php';

foreach( $columns as $_name => $_column )
	$_search .= "\t\t\$_criteria->compare( '$_name', \$this->$_name, " . ( 'string' === $_column->type ? 'true' : null ) . ";\n";

echo <<<HTML
class $modelClass extends $baseClass
{
	//********************************************************************************
	//* Code Information
	//********************************************************************************

	/**
	* This model was generated from database component '$dbToUse'
	*
	* The followings are the available columns in table '$tableName':
	*

HTML;

	foreach ( $columns as $_column )
		echo '	* @var ' . $_column->type . ' $' . $_column->name . PHP_EOL;

echo <<<HTML
	*/

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Returns the static model of the specified AR class.
	* @return CActiveRecord the static model class
	*/
	public static function model( \$className = __CLASS__ )
	{
		return parent::model( \$className );
	}

	/**
	* @return string the associated database table name
	*/
	public function tableName()
	{
		return '$tableName';
	}

	/**
	* @return array validation rules for model attributes.
	*/
	public function rules()
	{
		return array(

HTML;

	foreach ( $rules as $_rule )
		echo '			' . $_rule . ',' . PHP_EOL;

	echo '			array( \'' . implode( ', ', array_keys( $columns ) ) . '\', \'safe\', \'on\' => \'search\' ),';

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

	foreach ( $relations as $_name => $_relation )
		echo "			'$_name' => $_relation," . PHP_EOL;

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

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 * @todo Remove columns from this method that are not searchable
	 */
	public function search()
	{
		\$_criteria = new CDbCriteria;

		return new CActiveDataProvider(
			get_class( $this ),
			array(
				'criteria' => $_criteria,
			)
		);
	}
}
HTML;
