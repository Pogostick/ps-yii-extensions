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
	* The database application component associated with this object, defaults to db
	* 
	* @staticvar CDbConnection
	*/
	protected static $m_oDB = null;
	public function getDbConnection() { return self::$m_oDB ? self::$m_oDB : parent::getDbConnection(); }
	public static function setDbConnection( CDbConnection $oValue ) { self::$m_oDB = $oValue; }
	
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
	* The date/time function to stamp records with
	* 
	* @var string
	*/
	protected $m_sDateTimeFunction = null;
	public function getDateTimeFunction() { return $this->m_sDateTimeFunction; }
	public function setDateTimeFunction( $sValue ) { $this->m_sDateTimeFunction = $sValue; }
	
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
		if ( $_sCol = $this->getCreatedColumn() && $this->isNewRecord && $this->hasAttribute( $_sCol ) ) 
			$this->{$_sCol} = ( null !== $this->m_sDateTimeFunction ) ? date('c') : eval('return ' . $this->m_sDateTimeFunction . ';');
			
		if ( $_sCol = $this->getLModColumn() && $this->hasAttribute( $_sCol ) ) 
			$this->{$_sCol} = ( null !== $this->m_sDateTimeFunction ) ? date('c') : eval('return ' . $this->m_sDateTimeFunction . ';');
			
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
    
    public function touch()
    {
		if ( $this->m_sLModColumn && $this->hasAttribute( $this->m_sLModColumn ) ) 
			$this->{$this->m_sLModColumn} = ( null !== $this->m_sDateTimeFunction ) ? date('c') : eval('return ' . $this->m_sDateTimeFunction . ';');
			
		return $this->save();
	}
    
}