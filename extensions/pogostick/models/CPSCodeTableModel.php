<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Provides a base class for code lookup tables in your database
 *
 * @package 	psYiiExtensions
 * @subpackage 	models
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSCodeTableModel.php 402 2010-09-11 23:00:16Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *
 * @filesource
 */
class CPSCodeTableModel extends CPSModel
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/***
	* The name of our code table DDL
	*/
	const DDL_NAME = 'code_t.sql';
	const DDL_TABLE_NAME = 'code_t';
	const DDL_DATA_NAME = 'code_install_data.sql';

	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * @var integer A value filter
	 */
	protected $_valueFilter = null;
	public function getValueFilter() { return $this->_valueFilter; }
	public function setValueFilter( $value ) { $this->_valueFilter = $value; }

	/**
	 * @var string The column name for our "active" attribute.
	 */
	protected $_activeAttributeName = 'active_ind';

	/**
	 * @var string The class name of our "code" model
	 */
	protected $_modelClass = 'Codes';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	public function init()
	{
		parent::init();

		//	Get our model class
		$this->_modelClass = self::_findModelClass();
	}

	/**
	* Installs a standard code table into the database.
	*
	* @param CDbConnection $dbConnection Defaults to PS::_a()->db
	* @param string $name The code table name. Defaults to 'code_t'
	* @return boolean
	*/
	public static function install( $dbConnection = null, $name = self::DDL_TABLE_NAME )
	{
		$_db = PS::nvl( $dbConnection, PS::_db() );

		if ( $name && $_db )
		{
			$_path = Yii::getPathOfAlias( 'pogostick.templates.ddl' );

			$_sql = file_get_contents( $_path . self::DDL_NAME );
			if ( strlen( $_sql ) )
			{
				$_sql = str_ireplace( '%%TABLE_NAME', $name, $_sql );
				$_command = $_db->createCommand( $_sql );
				if ( $_command->execute() )
				{
					$_sql = file_get_contents( $_path . self::DDL_DATA_NAME );

					//	Load some codes...
					if ( strlen( $_sql ) )
					{
						$_sql = str_ireplace( '%%TABLE_NAME', $name, $_sql );
						$_command = $_db->createCommand( $_sql );
						$_command->execute();
					}
				}

				return true;
			}
		}

		return false;
	}

	/**
	* Get code rows
	* @param int $codeId Specific code ID to retrieve
	* @param string $codeType Retrieves all codes that are of this type
	* @param string $codeAbbreviation Retrieves all codes that are of this abbreviation. If type specicifed, further filters return set
	* @param string $sortOrder Optional sort order of result set. Defaults to PK (i.e. id)
	* @return array|string Depending on the parameters supplied, either returns a code row, an array of code rows, or a string.
	*/
	protected static function getCodes( $codeId = null, $codeType = null, $codeAbbreviation = null, $sortOrder = null, $activeCodesOnly = true, $valueFilter = null )
	{
		$_criteria = null;
		$_modelClass = self::_findModelClass();
		$_model = call_user_func( array( $_modelClass, 'model' ) );

		if ( null === $sortOrder ) $sortOrder = $_model->primaryKey;

		//	Get a single code...
		if ( null !== $codeId ) return $_model->valueFilter( $valueFilter )->active( $activeCodesOnly )->findByPk( $codeId );

		$_condition = array();
		$_params = array();

		//	Get a specific code by type/abbr
		if ( null !== $codeType )
		{
			$_condition[] = 'code_type_text = :code_type_text';
			$_params[':code_type_text'] = $codeType;
		}

		if ( null !== $codeAbbreviation )
		{
			$_condition[] = 'code_abbr_text = :code_abbr_text';
			$_params[':code_abbr_text'] = $codeAbbreviation;
		}

		//	No conditions? Bail...
		if ( empty( $_condition ) )
			return null;
		
		$_criteria = array(
			'condition' => implode( ' AND ', $_condition ),
			'params' => $_params,
			'order' => PS::nvl( $sortOrder ),
		);

		return $_model->valueFilter( $valueFilter )->active( $activeCodesOnly )->findAll( $_criteria );
	}

	/**
	* @return array validation rules for model attributes.
	*/
	public function rules()
	{
		return array(
			array( 'code_type_text', 'length', 'max' => 60 ),
			array( 'code_abbr_text', 'length', 'max' => 60 ),
			array( 'code_desc_text', 'length', 'max' => 255 ),
			array( 'assoc_text', 'length', 'max' => 255 ),
			array( 'id, active_ind, code_type_text, code_abbr_text, code_desc_text, create_date, lmod_date', 'required' ),
			array( 'id, active_ind, parnt_code_id', 'numerical', 'integerOnly' => true ),
			array( 'assoc_value_nbr', 'numerical' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Id',
			'code_type_text' => 'Type',
			'code_abbr_text' => 'Abbreviation',
			'code_desc_text' => 'Description',
			'parnt_code_id' => 'Parent Code Id',
			'assoc_value_nbr' => 'Associated Number',
			'assoc_text' => 'Associated Text',
			'active_ind' => 'Active',
			'create_date' => 'Created On',
			'lmod_date' => 'Modified On',
		);
	}

	//********************************************************************************
	//* Statics
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
	* Find a code by type
	*
	* @param string $codeType
	* @return array
	* @static
	*/
	public static function findAllByType( $codeType, $sortOrder = 'code_desc_text', $valueFilter = null )
	{
		return self::getCodes( null, $codeType, null, $sortOrder, true, $valueFilter );
	}

	/**
	* Find a code by type
	*
	* @param string $codeType
	* @return array
	* @static
	*/
	public static function findAllByAbbreviation( $codeAbbreviation, $codeType = null, $sortOrder = 'code_desc_text' )
	{
		return self::getCodes( null, $codeType, $codeAbbreviation, $sortOrder );
	}

	/**
	* Finds a single code by code_id
	*
	* Duplicates findByPk, but wanted to be consistent.
	*
	* @param integer $iCodeId
	* @return CActiveRecord
	* @static
	*/
	public static function findById( $iCodeId )
	{
		return self::getCodes( $iCodeId );
	}

	/**
	* Retrieves the associated value for a code
	*
	* @param int $codeId
	* @return double
	*/
	public function valueFilter( $value = null )
	{
		//	Nothing? Move along...
		if ( null === $value )
			return $this;
		
		//	Not an array? Make one...
		if ( ! is_array( $value ) ) $value = array( $value );

		//	No items?
		if ( empty( $value ) )
			return $this;
		
		//	Make string...
		if ( count( $value ) == 1 )
			$_condition = '( assoc_value_nbr is null or assoc_value_nbr = ' . $value[0] . ')';
		else
			$_condition = '( assoc_value_nbr is null or assoc_value_nbr in (' . implode( ',', $value ) . '))';

		$this->getDbCriteria()->mergeWith( array( 'condition' => $_condition ) );
		return $this;
	}

	/**
	* Returns a code's description
	*
	* @param int $codeId
	* @return string
	*/
	public static function getCodeDescription( $codeId )
	{
		$_code = self::getCodes( $codeId );
		return $_code ? $_code->code_desc_text : null;
	}

	/**
	* Returns a code's abbreviation
	*
	* @param int $codeId
	* @return string
	*/
	public static function getCodeAbbreviation( $codeId )
	{
		$_code = self::getCodes( $codeId );
		return $_code ? $_code->code_abbr_text : null;
	}

	/**
	* Returns a code's id given a type and abbreviation
	*
	* @param string $codeType
	* @param string $codeAbbreviation
	* @return integer
	*/
	public static function getCodeFromAbbreviation( $codeType, $codeAbbreviation )
	{
		$_codeList = self::getCodes( null, $codeType, $codeAbbreviation );
		return ! empty( $_codeList )  ? $_codeList[0]->id : null;
	}

	/**
	* Retrieves the associated text for a code
	*
	* @param int $codeId
	* @return string
	*/
	public static function getAssociatedText( $codeId )
	{
		$_code = self::getCodes( $codeId );
		return $_code ? $_code->assoc_text : null;
	}

	/**
	* Retrieves the associated value for a code
	*
	* @param int $codeId
	* @return double
	*/
	public static function getAssociatedValue( $codeId )
	{
		$_code = self::getCodes( $codeId );
		return $_code ? $_code->assoc_value_nbr : null;
	}

	/**
	* Returns all rows based on type/abbr given
	* If it's a specific request, (i.e. type & abbr given) a single row is returned.
	*
	* @param string $codeType
	* @param string $codeAbbreviation
	* @return int|CPSCodeTableModel|array
	*/
	public static function lookup( $codeType, $codeAbbreviation = null, $returnIdOnly = true )
	{
		$_code = self::getCodes( null, $codeType, $codeAbbreviation );
		return $_code ? ( $returnIdOnly ? $_code->id : $_code ) : null;
	}

	/**
	 * Only return active code records as designated by the active_ind
	 * @return CPSCodeTableModel
	 */
	public function active( $activeOnly = true )
	{
		if ( $activeOnly && $this->hasAttribute( $this->_activeAttributeName ) ) $this->getDbCriteria()->mergeWith( array( 'condition' => 'active_ind = :active_ind', 'params' => array( ':active_ind' => 1 ) ) );
		return $this;
	}

	/**
	 * Finds and sets the model class for this object.
	 * If your model class is not "Codes" and you're not running PHP 5.3.x+, override
	 * this method to provide your own model name.
	 * 
	 * @return string The name of the model class
	 */
	protected static function _findModelClass()
	{
		$_modelClass = 'Codes';

		//	Can't really use get_called_class with 5.2...
		if ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 )
			$_modelClass = get_called_class();

		return $_modelClass;
	}
}