<?php
/*
 * This file was generated by the psYiiExtensions scaffolding package.
 * 
 * @copyright Copyright &copy; 2009 My Company, LLC.
 * @link http://www.example.com
 */

/**
 * PostTag file
 * 
 * @package 	blog
 * @subpackage 	
 * 
 * @author 		Web Master <webmaster@example.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 */
class PostTag extends BaseModel
{
	//********************************************************************************
	//* Code Information
	//********************************************************************************
	
	/**
	* This model was generated from database component 'db'
	*
	* The followings are the available columns in table 'post_tag_asgn_t':
	*
	* @var integer $post_id
	* @var integer $tag_id
	* @var string $create_date
	*/
	 
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
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
		return self::getTablePrefix() . 'post_tag_asgn_t';
	}

	/**
	* @return array validation rules for model attributes.
	*/
	public function rules()
	{
		return array(
			array( 'create_date', 'required' ),
		);
	}

	/**
	* @return array relational rules.
	*/
	public function relations()
	{
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'post_id' => 'Post',
			'tag_id' => 'Tag',
			'create_date' => 'Create Date',
		);
	}

	/**
	 * @return array customized tooltips (attribute=>tip)
	 */
	public function attributeTooltips()
	{
		return array(
			'post_id' => 'Post',
			'tag_id' => 'Tag',
			'create_date' => 'Create Date',
		);
	}
}
