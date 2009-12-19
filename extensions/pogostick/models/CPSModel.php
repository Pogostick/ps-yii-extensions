<?php
/**
 * CPSModel class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC.
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage models
 * @since v1.0.0
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
/**
 * CPSModel provides base functionality for models
 * 
 * @property-read string $modelName The class name of the model
 */
class CPSModel extends CActiveRecord
{
	//********************************************************************************
	//* Members
	//********************************************************************************

	/**
	* Our schema, cached for speed
	* @var array
	*/
	protected $m_arSchema;
	public function getSchema() { return $this->m_arSchema ? $this->m_arSchema : $this->m_arSchema = $this->getMetaData()->columns; }
		
	/**
	 * The associated database table name prefix
	 * @var string
	 */
	protected $m_sTablePrefix = null;
	public function getTablePrefix() { return $this->m_sTablePrefix; }
	public function setTablePrefix( $sValue ) { $this->m_sTablePrefix = $sValue; }	
	
	/***
	* Current transaction if any
	* 
	* @var CDbTransaction
	*/
	protected $m_oTransaction = null;
	public function getTransaction() { return $this->m_oTransaction; }
	public function setTransaction( $oValue ) { $this->m_oTransaction = $oValue; }
	public function hasTransaction() { return isset( $this->m_oTransaction ) ? $this->m_oTransaction->active : false; }
	
	/**
	* Attribute labels cache
	* @var array
	*/
	protected $m_arAttributeLabels = array();

	/**
	* Returns all attribute latbels and populates cache
	* @returns array
	* @see CPSModel::attributeLabels
	*/
	public function getAttributeLabels() { return $this->m_arAttributeLabels ? $this->m_arAttributeLabels : $this->m_arAttributeLabels = $this->attributeLabels(); }

	/**
	* Returns the text label for the specified attribute.
	* @param string $sAttribute The attribute name
	* @return string the attribute label
	* @see generateAttributeLabel
	* @see getAttributeLabels
	*/
	public function getAttributeLabel( $sAttribute )
	{
		return PS::o( $this->getAttributeLabels(), $sAttribute, $this->generateAttributeLabel( $sAttribute ) );
	}

	/**
	* Get's the name of the model class
	* 
	* @var string
	*/
	protected $m_sModelName = null;
	public function getModelName() { return $this->m_sModelName; }
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/***
	* Sets our default behaviors
	* 
	*/
	public function behaviors()
	{
		return array_merge(
			parent::behaviors(),
			array(
				//	Date/time formatter
				'psDataFormat' => array(
					'class' => 'pogostick.behaviors.CPSDataFormatBehavior',
				),
			)
		);
	}

	/***
	* Begins a database transaction
	* @throws CDbException
	*/
	public function beginTransaction()
	{
		if ( ! $this->m_oTransaction )
		{
			$this->m_oTransaction = $this->dbConnection->beginTransaction();
			return;
		}
		
		throw new CDbException( Yii::t( 'psYiiExtensions', 'Unable to start new transaction. transaction already in progress.' ) );
	}
	
	/**
	* Commits the current transaction if any
	* 
	*/
	public function commitTransaction()
	{
		if ( $this->m_oTransaction ) 
		{
			$this->m_oTransaction->commit();
			$this->m_oTransaction = null;
		}
	}

	/**
	* Rolls back the current transaction, if any...
	* 
	*/
	public function rollbackTransaction()
	{
		if ( $this->m_oTransaction ) 
		{
			$this->m_oTransaction->rollBack();
			$this->m_oTransaction = null;
		}
	}
	
	/***
	* Returns the errors on this model in a single string suitable for logging.
	* 
	* @param string $sAttribute Attribute name. Use null to retrieve errors for all attributes.
	* @returns string
	*/
	public function getErrorsForLogging( $sAttribute = null )
	{
		$_sOut = null;
		$_i = 1;
		
		if ( $_arErrors = $this->getErrors( $sAttribute ) )
		{
			foreach ( $_arErrors as $_sAttribute => $_arError )
				$_sOut .= $_i++ . '. [' . $_sAttribute . '] : ' . implode( '|', $_arError ) . '; ';
		}
		
		return $_sOut;
	}
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Override of CModel::setAttributes
	* Populates member variables as well.
	* 
	* @param array $arValues
	* @param string $sScenario
	*/
	public function setAttributes( $arValues = array(), $sScenario = '' )
	{
		if ( '' === $sScenario ) $sScenario = $this->getScenario();
		
		if ( is_array( $arValues ) )
		{
			$_arAttributes = array_flip( $this->getSafeAttributeNames( $sScenario ) );
			
			foreach ( $arValues as $_sKey => $_oValue )
			{
				$_bIsAttribute = isset( $_arAttributes[ $_sKey ] );

				if ( $_bIsAttribute || ( $this->hasProperty( $_sKey ) && $this->canSetProperty( $_sKey ) ) )
					$this->setAttribute( $_sKey, $_oValue );
			}
		}
	}

	//********************************************************************************
	//* Event Handlers
	//********************************************************************************
	
	/**
	* Grab our name
	* @param string $sClassName
	*/
	public function afterConstruct()
	{
		$this->m_sModelName = get_class( $this );
		parent::afterConstruct();
	}
	
}