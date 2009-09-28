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
	 * @var string
	 */
	protected $m_sCreatedColumn = null;
	public function getCreatedColumn() { return $this->m_sCreatedColumn; }
	public function setCreatedColumn( $sValue ) { $this->m_sCreatedColumn = $sValue; }
	
	/**
	 * The optional name of the last modified column in the table
	 * @var string
	 */
	protected $m_sLModColumn = null;
	public function getLModColumn() { return $this->m_sLModColumn; }
	public function setLModColumn( $sValue ) { $this->m_sLModColumn = $sValue; }	
	
	/**
	* If defined, all deletes are soft
	* @var string
	*/
	protected $m_sSoftDeleteColumn = null;
	public function getSoftDeleteColumn() { return $this->m_sSoftDeleteColumn; }
	public function setSoftDeleteColumn( $sValue ) { $this->m_sSoftDeleteColumn = $sValue; }	
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Populates 'created' field if new record
	* 
	* @param CEvent $oEvent
	*/
	public function onBeforeSave( $oEvent )
	{
		if ( $_sCol = $this->getCreatedColumn() && $this->isNewRecord && $this->hasAttribute( $_sCol ) ) $this->{$_sCol} = date('c');
		return parent::onBeforeSave( $oEvent );
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
			return parent::save( $bRunValidation, $arAttributes );
		}
		catch ( CDbException $_ex )
		{
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
				$this->{$_sCol} = 1;
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
				$this->{$_sCol} = 0;
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
		if ( $_sCol = $this->m_sSoftDeleteColumn && $this->hasAttribute( $_sCol ) ) 
			return array( 'condition' => $this->tableName() . '.' . $_sCol . ' = 0', 'alias' => $this->tableName() );
			
    	return array();
    }
    
}