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
	 * The associated database table name prefix
	 * @var string
	 */
	protected $m_sTablePrefix = null;
	public function getTablePrefix() { return $this->m_sTablePrefix; }
	public function setTablePrefix( $sValue ) { $this->m_sTablePrefix = $sValue; }	
	
	/**
	 * The optional name of the created column in the table
	 * 
	 * @var string
	 */
	protected $m_sCreatedColumn = null;
	public function getCreatedColumn() { return $this->m_sCreatedColumn; }
	public function setCreatedColumn( $sValue ) { $this->m_sCreatedColumn = $sValue; }
	
	/**
	 * The optional name of the created by user id column in the table
	 * 
	 * @var string
	 */
	protected $m_sCreatedByColumn = null;
	public function getCreatedByColumn() { return $this->m_sCreatedByColumn; }
	public function setCreatedByColumn( $sValue ) { $this->m_sCreatedByColumn = $sValue; }
	
	/**
	 * The optional name of the last modified column in the table
	 * 
	 * @var string
	 */
	protected $m_sLModColumn = null;
	public function getLModColumn() { return $this->m_sLModColumn; }
	public function setLModColumn( $sValue ) { $this->m_sLModColumn = $sValue; }	
	
	/**
	 * The optional name of the modified by user id column in the table
	 * 
	 * @var string
	 */
	protected $m_sLModByColumn = null;
	public function getLModByColumn() { return $this->m_sLModByColumn; }
	public function setLModByColumn( $sValue ) { $this->m_sLModByColumn = $sValue; }	
	
	/**
	* If defined, all deletes are soft
	* 
	* @var string
	*/
	protected $m_sSoftDeleteColumn = null;
	public function getSoftDeleteColumn() { return $this->m_sSoftDeleteColumn; }
	public function setSoftDeleteColumn( $sValue ) { $this->m_sSoftDeleteColumn = $sValue; }	
	
	/**
	* Soft delete indicator (false,true)
	* 
	* @var array
	*/
	protected $m_arSoftDeleteValue = array( 0, 1 );
	public function getSoftDeleteValue() { return $this->m_arSoftDeleteValue; }
	public function setSoftDeleteValue( $arValue ) { $this->m_arSoftDeleteValue = $arValue; }	
	
	/**
	* The date/time function to stamp records with
	* 
	* @var string
	*/
	protected $m_sDateTimeFunction = null;
	public function getDateTimeFunction() { return $this->m_sDateTimeFunction; }
	public function setDateTimeFunction( $sValue ) { $this->m_sDateTimeFunction = $sValue; }
	
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
				'psDataFormat' => array(
					'class' => 'pogostick.behaviors.CPSDataFormatBehavior',
				),
			)
		);
	}

	/**
	* Grab our name
	* 
	* @param string $sClassName
	*/
	public function afterConstruct()
	{
		$this->m_sModelName = get_class( $this );
		parent::afterConstruct();
	}
	
	/**
	* Populates 'created' field if new record
	* 
	* @param CEvent $oEvent
	*/
	public function beforeValidate( $sScenario = null )
	{
		//	Handle created stamp
		if ( $this->isNewRecord )
		{
			if ( ( $_sCreated = $this->getCreatedColumn() ) && $this->hasAttribute( $_sCreated ) )
				$this->{$_sCreated} = ( null === $this->m_sDateTimeFunction ) ? date('Y-m-d H:i:s') : eval('return ' . $this->m_sDateTimeFunction . ';');
				
			//	Handle user id stamp
			if ( ( $_sCreatedBy = $this->getLModByColumn() ) && $this->hasAttribute( $_sCreatedBy ) ) 
				$this->{$_sCreatedBy} = Yii::app()->user->getId();
		}
			
		//	Handle lmod stamp
		if ( ( $_sLMod = $this->getLModColumn() ) && $this->hasAttribute( $_sLMod ) ) 
			$this->{$_sLMod} = ( null === $this->m_sDateTimeFunction ) ? date('Y-m-d H:i:s') : eval('return ' . $this->m_sDateTimeFunction . ';');
				
		//	Handle user id stamp
		if ( ( $_sLModBy = $this->getLModByColumn() ) && $this->hasAttribute( $_sLModBy ) ) 
			$this->{$_sLModBy} = Yii::app()->user->getId();
				
		return parent::beforeValidate( $sScenario );
	}
	
	/**
	* Soft deletes models that have that feature
	* 
	* @returns boolean
	*/
	public function delete()
	{
		//	Perform a soft delete if this model allows
		if ( $_sCol = $this->m_sSoftDeleteColumn )
		{
			if ( $this->hasAttribute( $_sCol ) )
			{
				$this->{$_sCol} = $this->m_arSoftDeleteValue[ 1 ];
				return $this->update();
			}
		}
		
		//	Otherwise a hard deletee
		return parent::delete();
	}

	/**
	* Undeletes a soft-deleted model
	* 
	* @returns boolean
	*/
	public function undelete()
	{
		//	Was soft deleted? Reverse
		if ( $_sCol = $this->m_sSoftDeleteColumn )
		{
			if ( $this->hasAttribute( $_sCol ) && $this->{$_sCol} )
			{
				$this->{$_sCol} = $this->m_arSoftDeleteValue[ 0 ];
				return $this->update();
			}
		}
		
		//	Otherwise, not possible
		return false;
	}

	/**
	* Make "active" the default scope...
	* 
	*/
    public function defaultScope()
    {
		if ( ( $_sCol = $this->m_sSoftDeleteColumn ) && $this->hasAttribute( $_sCol ) ) 
			return array( 'condition' => $this->tableName() . '.' . $_sCol . ' = :softDeleteValue', 'alias' => $this->tableName(), 'params' => array( ':softDeleteValue' => $this->m_arSoftDeleteValue[ 0 ] ) );
			
    	return array();
    }
 
 	/**
 	* Sets lmod date(s) and saves
 	* Will optionally touch other columns. You can pass in a single column name or an array of columns.
 	* This is useful for updating not only the lmod column but a last login date for example.
 	* Only the columns that have been touched are updated. If no columns are updated, no database action is performed.
 	* 
 	* @param mixed $oOtherCols The single column name or array of columns to touch in addition to configured lmod column
 	* @returns boolean
 	*/
    public function touch( $oOtherCols = null )
    {
    	$_sTouchVal = ( null === $this->m_sDateTimeFunction ) ? date('Y-m-d H:i:s') : eval('return ' . $this->m_sDateTimeFunction . ';');
    	$_arUpdate = array();
    	
    	//	Any other columns to touch?
    	if ( null !== $oOtherCols )
    	{
    		foreach ( CPSHelp::makeArray( $oOtherCols ) as $_sColumn )
    		{
    			if ( $this->hasAttribute( $_sColumn ) )
    			{
    				$this->{$_sColumn} = $_sTouchVal;
    				$_arUpdate[] = $_sColumn;
				}
    		}
		}
    	
		if ( $this->m_sLModColumn && $this->hasAttribute( $this->m_sLModColumn ) ) 
		{
			$this->{$this->m_sLModColumn} = ( null === $this->m_sDateTimeFunction ) ? date('Y-m-d H:i:s') : eval('return ' . $this->m_sDateTimeFunction . ';');
    		$_arUpdate[] = $this->m_sLModColumn;
		}
			
		//	Only update if and what we've touched...
		return count( $_arUpdate ) ? $this->update( $_arUpdate ) : true;
	}
	
	/***
	* Begins a transaction
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
	
	/**
	* Returns the text label for the specified attribute.
	* @param string the attribute name
	* @return string the attribute label
	* @see generateAttributeLabel
	* @see attributeLabels
	*/
	public function getAttributeLabel( $sAttribute )
	{
		//	Cache for speed...
		static $_arLabel = null;
		if ( null === $_arLabel ) $_arLabel = $this->attributeLabels();
		return PS::o( $_arLabel, $sAttribute, $this->generateAttributeLabel( $sAttribute ) );
	}

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
				if ( isset( $_arAttributes[ $_sKey ] ) || $this->hasProperty( $_sKey ) )
					$this->{$_sKey} = $_oValue;
			}
		}
	}

	/**
	* Returns whether or not this model has children
	* Override as necessary
	* 
	* @returns boolean
	*/
	public function hasChildren()
	{
		return false;
	}
	
	/**
	* Named scope to return child models
	* Override as necessary
	* 
	* @param integer $iParentId Default to null
	*/
	public function childOf( $iParentId = null )
	{
		return $this;
	}
	
	/**
	* Outputs a string of UL/LI tags of an array of models suitable
	* for menu structures
	* 
	* @param array $arModel
	* @param array $arOptions
	* @return string
	*/
	public function asUnorderedList( $arModel = array(), $arOptions = array() )
	{
		static $_bInit;
		static $_sValColumn;
		static $_sKeyColumn;
		static $_sChildrenRelation;
		static $_bLinkText;

		$_sClass = $_sId = $_sOut = null;
		
		if ( ! $_bInit )
		{
			$_sId = PS::o( $arOptions, 'id', null, true );
			$_bLinkText = PS::o( $arOptions, 'linkText', true, true );
			$_sKeyColumn = PS::o( $arOptions, 'keyColumn', 'id', true );
			$_sValColumn = PS::o( $arOptions, 'valueColumn', null, true );
			$_sChildrenRelation = PS::o( $arOptions, 'childrenRelation', 'children', true );
			$_sClass = PS::o( $arOptions, 'class', null, true );

			$_bInit = true;
		}

		//	If no model array was specified, get top level
		if ( array() == $arModel )
			$arModel = $this->childOf( null )->findAll();

		if ( ! empty( $arModel ) )
		{
			//	Loop...
			foreach ( $arModel as $_oModel )
			{
				//	Does this model have relational kids?
				$_bHasKids = in_array( $_sChildrenRelation, array_keys( $_oModel->relations() ) ) && $_oModel->hasChildren();
				$_sOut .= PS::tag( 'li', array(), ( $_bLinkText ? PS::link( PS::encode( $_oModel->{$_sValColumn} ), '#' ) : PS::encode( $_oModel->{$_sValColumn} ) ) . ( $_bHasKids ? $_oModel->asUnorderedList( $_oModel->{$_sChildrenRelation}, $arOptions ) : null ) );
			}
		}
		
		$_arOpts = array();
		if ( $_sId ) $_arOpts['id'] = $_sId;
		if ( $_sClass ) $_arOpts['class'] = $_sClass;

		return PS::tag( 'ul', $_arOpts, $_sOut );
	}
	
}