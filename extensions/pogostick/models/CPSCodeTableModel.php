<?php
/**
 * CPSCodeTableModel file
 * 
 * Provides a base class for code lookup tables in your database
 *
 * @filesource
 * @author Jerry Ablan <jablan@pogostick.com>
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage models
 * @since v1.0.6
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
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
	//* Public Methods
	//********************************************************************************
	
	/**
	* Installs a standard code table into the database.
	* 
	* @param CDbConnnection $oDB Defaults to Yii::app()->db
	* @param string $sName The code table name. Defaults to 'code_t'
	* @returns boolean
	*/
	public static function install( $oDB = null, $sName = self::DDL_TABLE_NAME )
	{
		$_oDB = PS::nvl( $oDB, Yii::app()->db );
		
		if ( $sName && $_oDB )
		{
			$_sSQL = file_get_contents( Yii::getPathOfAlias( 'pogostick.templates.ddl' ) . self::DDL_NAME );
			if ( strlen( $_sSQL ) )
			{
				$_sSQL = str_ireplace( '%%TABLE_NAME', $sName, $_sSQL );
				$_oCmd = $_oDB->createCommand( $_sSQL );
				if ( $_oCmd->execute() )
				{
					$_sSQL = file_get_contents( Yii::getPathOfAlias( 'pogostick.templates.ddl' ) . self::DDL_DATA_NAME );
					
					//	Load some codes...
					if ( strlen( $_sSQL ) )
					{
						$_sSQL = str_ireplace( '%%TABLE_NAME', $sName, $_sSQL );
						$_oCmd = $_oDB->createCommand( $_sSQL );
						$_oCmd->execute();
					}
				}
				
				return true;
			}
		}
		
		return false;
	}
	
	/**
	* Get code rows
	* 
	* @param int $iId Specific code ID to retrieve
	* @param string $sType Retrieves all codes that are of this type
	* @param string $sAbbr Retrieves all codes that are of this abbreviation. If type specicifed, further filters return set
	* @param string $sOrder Optional sort order of result set. Defaults to PK (i.e. id)
	* @return array|string Depending on the parameters supplied, either returns a code row, an array of code rows, or a string.
	*/
	protected static function getCodes( $iId = null, $sType = null, $sAbbr = null, $sOrder = null )
	{
		//	Can't really use get_called_class with 5.2...
		if ( version_compare( PHP_VERSION, '5.3.0' ) < 0 )
		{
			if ( ! $_sModelClass = $this->modelName )
				throw new CDbException( 'You must set the $modelName property before calling this method.' );
		}
		else
			$_sModelClass = get_called_class();

		$_arCrit = null;
		$sOrder = PS::nvl( $sOrder, $_sModelClass::model()->getMetaData()->primaryKey );

		//	Get a single code...
		if ( null !== $iId ) return $_sModelClass::model()->findByPk( $iId );
		
		//	Get a specific code by type/abbr
		if ( null !== $sType && null !== $sAbbr )
		{
			$_arCrit = array(
				'condition' => 'code_type_text = :code_type_text and code_abbr_text = :code_abbr_text',
				'params' => array( ':code_type_text' => $sType, ':code_abbr_text' => $sAbbr ),
				'order' => PS::nvl( $sOrder ),
			);

			return $_sModelClass::model()->find( $_arCrit );
		}

		//	Codes By Type
		if ( null !== $sType && null === $sAbbr )
		{
			$_arCrit = array(
				'condition' => 'code_type_text = :code_type_text',
				'params' => array( ':code_type_text' => $sType ),
				'order' => PS::nvl( $sOrder ),
			);
		}
		//	Codes By Abbreviation
		else if ( null === $sType && null !== $sAbbr )
		{
			$_arCrit = array(
				'condition' => 'code_abbr_text = :code_abbr_text',
				'params' => array( ':code_abbr_text' => $sAbbr ),
				'order' => PS::nvl( $sOrder ),
			);
		}
		
		return $_arCrit ? $_sModelClass::model()->findAll( $_arCrit ) : null;
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
			array( 'id, code_type_text, code_abbr_text, code_desc_text, create_date, lmod_date', 'required' ),
			array( 'id, parnt_code_id', 'numerical', 'integerOnly' => true ),
			array( 'assoc_value_nbr', 'numerical' ),
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
			'id' => 'Id',
			'code_type_text' => 'Code Type Text',
			'code_abbr_text' => 'Code Abbr Text',
			'code_desc_text' => 'Code Desc Text',
			'parnt_code_id' => 'Parnt Code',
			'assoc_value_nbr' => 'Assoc Value Nbr',
			'assoc_text' => 'Assoc Text',
			'create_date' => 'Create Date',
			'lmod_date' => 'Lmod Date',
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
	* @param string $sType
	* @return array
	* @static
	*/
	public static function findAllByType( $sType, $sOrder = 'code_desc_text' )
	{
		return self::getCodes( null, $sType, null, $sOrder );
	}

	/**
	* Find a code by type
	* 
	* @param string $sType
	* @return array
	* @static
	*/
	public static function findAllByAbbreviation( $sAbbr, $sType = null, $sOrder = 'code_desc_text' )
	{
		return self::getCodes( null, $sType, $sAbbr, $sOrder );
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
	* Returns a code's description
	* 
	* @param int $iId
	* @return string
	*/
	public static function getCodeDescription( $iId )
	{
		$_oCode = self::getCodes( $iId );
		return $_oCode ? $_oCode->code_desc_text : null;
	}
	
	/**
	* Returns a code's abbreviation
	* 
	* @param int $iId
	* @return string
	*/
	public static function getCodeAbbreviation( $iId )
	{
		$_oCode = self::getCodes( $iId );
		return $_oCode ? $_oCode->code_abbr_text : null;
	}
	
	/**
	* Retrieves the associated text for a code
	* 
	* @param int $iId
	* @return string
	*/
	public static function getAssociatedText( $iId )
	{
		$_oCode = self::getCodes( $iId );
		return $_oCode ? $_oCode->assoc_text : null;
	}
	
	/**
	* Retrieves the associated value for a code
	* 
	* @param int $iId
	* @return double
	*/
	public static function getAssociatedValue( $iId )
	{
		$_oCode = self::getCodes( $iId );
		return $_oCode ? $_oCode->assoc_value_nbr : null;
	}
	
	/**
	* Returns all rows based on type/abbr given
	* If it's a specific request, (i.e. type & abbr given) a single row is returned.
	* 
	* @param string $sType
	* @param string $sAbbr
	* @return int|CPSCodeTableModel|array
	*/
	public static function lookup( $sType, $sAbbr = null, $bReturnIdOnly = true )
	{
		$_oCode = self::getCodes( null, $sType, $sAbbr );
		return $_oCode ? ( $bReturnIdOnly ? $_oCode->id : $_oCode ) : null;
	}
	
}