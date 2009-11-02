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
 * @package wui.modules
 * @subpackage ezpost
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
	 * The optional name of the last modified column in the table
	 * 
	 * @var string
	 */
	protected $m_sLModColumn = null;
	public function getLModColumn() { return $this->m_sLModColumn; }
	public function setLModColumn( $sValue ) { $this->m_sLModColumn = $sValue; }	
	
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
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Grab init function for setting values
	* 
	*/
	public function init()
	{
		parent::init();
	}
	
	/**
	* Populates 'created' field if new record
	* 
	* @param CEvent $oEvent
	*/
	public function beforeValidate( $sScenario = null )
	{
		if ( $this->isNewRecord )
		{
			$_sCreated = $this->getCreatedColumn();
			$_sLMod = $this->getLModColumn();
			
			if ( $_sCreated && $this->hasAttribute( $_sCreated ) )
				$this->{$_sCreated} = ( null === $this->m_sDateTimeFunction ) ? date('c') : eval('return ' . $this->m_sDateTimeFunction . ';');
			
			if ( $_sLMod && $this->hasAttribute( $_sLMod ) ) 
				$this->{$_sLMod} = ( null === $this->m_sDateTimeFunction ) ? date('c') : eval('return ' . $this->m_sDateTimeFunction . ';');
		}
			
		return parent::beforeValidate( $sScenario );
	}
	
	/***
	* Saves the row
	* 
	* @param bool $bRunValidation
	* @param array $arAttributes
	* @return boolean
	*/
	public function save( $bRunValidation = true, $arAttributes = null )
	{
		try
		{
			parent::save( $bRunValidation, $arAttributes );
			$this->commitTransaction();			
			return true;
		}
		catch ( CDbException $_ex )
		{
			$this->rollbackTransaction();
			$this->addError( '', $_ex->getMessage() );
		}
		
		return false;
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
 	* Sets lmod date and saves
 	*    
 	*/
    public function touch()
    {
		if ( $this->m_sLModColumn && $this->hasAttribute( $this->m_sLModColumn ) ) 
			$this->{$this->m_sLModColumn} = ( null === $this->m_sDateTimeFunction ) ? date('c') : eval('return ' . $this->m_sDateTimeFunction . ';');
			
		return $this->save();
	}
	
	/***
	* Begins a transaction
	* 
	*/
	public function beginTransaction()
	{
		if ( ! $this->m_oTransaction )
		{
			$this->m_oTransaction = $this->dbConnection->beginTransaction();
			return;
		}
		
		throw new CPSException( Yii::t( 'psYiiExtensions', 'Unable to start new transaction. transaction already in progress.' ) );
	}
	
	public function commitTransaction()
	{
		if ( $this->m_oTransaction ) 
		{
			$this->m_oTransaction->commit();
			$this->m_oTransaction = null;
		}
	}

	public function rollbackTransaction()
	{
		if ( $this->m_oTransaction ) 
		{
			$this->m_oTransaction->rollBack();
			$this->m_oTransaction = null;
		}
	}
}