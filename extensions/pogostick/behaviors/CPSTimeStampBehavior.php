<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
 
/**
 * Allows you to define time stamp fields in models and automatically update them.
 * 
 * @package 	psYiiExtensions
 * @subpackage 	behaviors
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSTimeStampBehavior.php 401 2010-08-31 21:04:18Z jerryablan@gmail.com $
 * @since 		v1.0.6
 * 
 * @filesource
 * 
 * @property string $createdColumn The name of the column that holds your create date
 * @property string $createdByColumn The name of the column that holds your creating user
 * @property string $lmodColumn The name of the column that holds your last modified date
 * @property string $lmodByColumn The name of the column that holds your last modifying user
 * @property string $dateTimeFunction The name of the function to use to set dates. Defaults to date('Y-m-d H:i:s').
 */
class CPSTimeStampBehavior extends CPSBaseActiveRecordBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * The optional name of the created column in the table
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
	* The date/time function to stamp records with
	* 
	* @var string
	*/
	protected $m_sDateTimeFunction = null;
	public function getDateTimeFunction() { return $this->m_sDateTimeFunction; }
	public function setDateTimeFunction( $sValue ) { $this->m_sDateTimeFunction = $sValue; }
	
	//********************************************************************************
	//*  Event Handlers
	//********************************************************************************
	
	/**
	* Timestamps row
	* @param CModelEvent $oEvent
	*/
	public function beforeValidate( $oEvent )
	{
		//	Handle created stamp
		if ( $oEvent->sender->isNewRecord )
		{
			if ( $this->m_sCreatedColumn && $oEvent->sender->hasAttribute( $this->m_sCreatedColumn ) )
				$this->owner->setAttribute( $this->m_sCreatedColumn, ( null === $this->m_sDateTimeFunction ) ? date('Y-m-d H:i:s') : eval('return ' . $this->m_sDateTimeFunction . ';') );
					
			if ( $this->m_sCreatedByColumn && $oEvent->sender->hasAttribute( $this->m_sCreatedByColumn ) && ! $oEvent->sender->getAttribute( $this->m_sCreatedByColumn ) )
				$this->owner->setAttribute( $this->m_sCreatedByColumn, Yii::app()->user->getId() );
		}
			
		//	Handle lmod stamp
		if ( $this->m_sLModColumn && $oEvent->sender->hasAttribute( $this->m_sLModColumn ) )
				$this->owner->setAttribute( $this->m_sLModColumn, ( null === $this->m_sDateTimeFunction ) ? date('Y-m-d H:i:s') : eval('return ' . $this->m_sDateTimeFunction . ';') );
				
		//	Handle user id stamp
		if ( $this->m_sLModByColumn && $oEvent->sender->hasAttribute( $this->m_sLModByColumn ) && ! $oEvent->sender->getAttribute( $this->m_sLModByColumn ) )
			$this->owner->setAttribute( $this->m_sLModByColumn, Yii::app()->user->getId() );
				
		return parent::beforeValidate( $oEvent );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
    /**
    * Returns formatted create/lmod dates for forms
    * 
    */
    public function showDates()
    {
    	if ( ! $this->owner->isNewRecord ) return CPSHelperBase::showDates( $this->owner, $this->m_sCreatedColumn, $this->m_sLModColumn, 'D M j, Y' );
	}
	
 	/**
 	* Sets lmod date(s) and saves
 	* Will optionally touch other columns. You can pass in a single column name or an array of columns.
 	* This is useful for updating not only the lmod column but a last login date for example.
 	* Only the columns that have been touched are updated. If no columns are updated, no database action is performed.
 	* 
 	* @param mixed $additionalColumns The single column name or array of columns to touch in addition to configured lmod column
 	* @return boolean
 	*/
    public function touch( $additionalColumns = null )
    {
    	$_touchValue = ( null === $this->m_sDateTimeFunction ) ? date( 'Y-m-d H:i:s' ) : eval( 'return ' . $this->m_sDateTimeFunction . ';' );
    	$_updateList = array();
    	
    	//	Any other columns to touch?
    	if ( null !== $additionalColumns )
    	{
    		foreach ( CPSHelperBase::makeArray( $additionalColumns ) as $_attribute )
    		{
    			if ( $this->owner->hasAttribute( $_attribute ) )
    			{
    				$this->owner->setAttribute( $_attribute, $_touchValue );
    				$_updateList[] = $_attribute;
				}
    		}
		}
    	
		if ( $this->m_sLModColumn && $this->owner->hasAttribute( $this->m_sLModColumn ) ) 
		{
			$this->owner->setAttribute( $this->m_sLModColumn, $_touchValue );
    		$_updateList[] = $this->m_sLModColumn;
		}
			
		//	Only update if and what we've touched...
		return count( $_updateList ) ? $this->owner->update( $_updateList ) : true;
	}
	
}