<?php
/*
 * This file was generated by the psYiiExtensions scaffolding package.
 * 
 * @copyright Copyright &copy; 2009 My Company, LLC.
 * @link http://www.example.com
 */

/**
 * Post file
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
class Post extends BaseModel
{
	//********************************************************************************
	//* Code Information
	//********************************************************************************
	
	/**
	* This model was generated from database component 'db'
	*
	* The followings are the available columns in table 'post_t':
	*
	* @var integer $id
	* @var integer $author_id
	* @var string $title_text
	* @var string $content_text
	* @var string $content_display_text
	* @var string $tags_text
	* @var integer $status_nbr
	* @var integer $comment_count_nbr
	* @var string $create_date
	* @var string $lmod_date
	*/
	 
 	//********************************************************************************
 	//* Constants
 	//********************************************************************************
 	
 	const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ARCHIVED = 2;
 
	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	* The array of status labels
	* @returns array
	*/
	protected $m_arStatusOptions = array(
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_PUBLISHED => 'Published',
        self::STATUS_ARCHIVED => 'Archived',
	);
 	public function getStatusOptions() { return $this->m_arStatusOptions; }
 	/**
 	* Get the status text
 	*/
 	public function getStatusText()
    {
        return PS::o( $this->m_arStatusOptions, $this->status_nbr, 'Unknown (' . $this->status_nbr . ')' );
    }

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
		return self::getTablePrefix() . 'post_t';
	}

	/**
	* @return array validation rules for model attributes.
	*/
	public function rules()
	{
		return array(
			array( 'title_text', 'length', 'max' => 128 ),
			array( 'title_text, content_text, status_nbr', 'required' ),
			array( 'status_nbr, comment_count_nbr', 'numerical', 'integerOnly' => true ),
			array( 'tags_text', 'match', 'pattern'=>'/^[\w\s,]+$/', 'message' => 'Tags can only contain word characters.' ),
		);
	}

	/**
	* @return array relational rules.
	*/
	public function relations()
	{
		return array(
			'comments' => array( self::HAS_MANY, 'Comment', 'post_id', 'order' => '??.create_date desc' ),
			'author' => array( self::BELONGS_TO, 'User', 'author_id' ),
			'tags' => array( self::MANY_MANY, 'Tag', 'PostTag( post_id, tag_id )' ),
	        'tagFilter' => array( self::MANY_MANY, 'Tag', 'post_tag_asgn_t( post_id, tag_id )',
	        	'alias' => 'tagFilter',
	        	'together' => true,
	        	'joinType' => 'INNER JOIN',
	        	'condition' => 'tagFilter.tag_name_text = :tag_name_text'
	        ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Id',
			'author_id' => 'Author',
			'title_text' => 'Title',
			'content_text' => 'Contents',
			'content_display_text' => 'Content',
			'tags_text' => 'Tags',
			'status_nbr' => 'Status',
			'statusText' => 'Status',
			'comment_count_nbr' => '# of Comments',
			'create_date' => 'Created On',
			'lmod_date' => 'Modified On',
		);
	}

 	/**
 	* Returns an array of tags
 	* @returns array
 	*/
	public function getTagArray()
	{
		//	Break tag string into a set of tags
		return array_unique(
			preg_split( '/\s*,\s*/', trim( $this->tags_text ), -1, PREG_SPLIT_NO_EMPTY )
		);
	}
	
	//********************************************************************************
	//* Scopes
	//********************************************************************************
	
	/**
	* Scope to return published posts.
	* @returns Post
	*/
	public function published()
	{
		$_oCrit = new CDbCriteria();
		$_oCrit->condition = 'status_nbr = :status_nbr';
		$_oCrit->params = array( ':status_nbr' => self::STATUS_PUBLISHED );

		$this->getDbCriteria()->mergeWith( $_oCrit );
		
		return $this;
	}
	
	//********************************************************************************
	//* Event Handlers
	//********************************************************************************
	
	/**
	* Before we validate...
	* @param string $sScenario
	*/
	protected function beforeValidate( $sScenario )
	{
		if ( $this->isNewRecord ) $this->author_id = Yii::app()->user->id;
		$this->content_display_text = PS::markdownTransform( $this->content_text );
		return parent::beforeValidate( $sScenario );
	}
	
}