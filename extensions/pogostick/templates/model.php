<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * This is the template for generating a model class file.
 * The following variables are available in this template:
 * - $className: the class name
 * - $tableName: the table name
 * - $columns: a list of table column schema objects
 * - $rules: a list of validation rules (string)
 * - $labels: a list of labels (string)
 * - $relations: a  list of relations (string)
 * - $baseClass: the base class to extend
 *
 * @package 	psYiiExtensions
 * @subpackage 	templates
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: model.php 322 2009-12-23 23:51:37Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 */

//	Include our header 
include( Yii::getPathOfAlias( 'pogostick.templates.crud' ) . '/build_template_header.php' );

echo <<<HTML
class $className extends $baseClass
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
	
	foreach ( $columns as $column ) echo '	* @var ' . $column->type . ' $' . $column->name . "\n";

echo <<<HTML
	*/
	 
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
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
