<?php
/**
 * CPSTimeStampBehavior.php class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage behaviours
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
/**
 * Allows you to define time stamp fields in models and automatically update them.
 */
class CPSTimeStampBehavior extends CActiveRecordBehavior
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
	* 
	* @param CEvent $oEvent
	*/
	public function beforeValidate( $oEvent )
	{
		//	Handle created stamp
		if ( $oEvent->sender->isNewRecord )
		{
			if ( $this->m_sCreatedColumn && $oEvent->sender->hasAttribute( $this->m_sCreatedColumn ) ) 
					$this->owner->setAttribute( $this->m_sCreatedColumn, ( null === $this->m_sDateTimeFunction ) ? date('Y-m-d H:i:s') : eval('return ' . $this->m_sDateTimeFunction . ';') );
					
			if ( $this->m_sCreatedByColumn && $oEvent->sender->hasAttribute( $this->m_sCreatedByColumn ) )
				$this->owner->setAttribute( $this->m_sCreatedByColumn, Yii::app()->user->getId() );
		}
			
		//	Handle lmod stamp
		if ( $this->m_sLModColumn && $oEvent->sender->hasAttribute( $this->m_sLModColumn ) ) 
				$this->owner->setAttribute( $this->m_sLModColumn, ( null === $this->m_sDateTimeFunction ) ? date('Y-m-d H:i:s') : eval('return ' . $this->m_sDateTimeFunction . ';') );
				
		//	Handle user id stamp
		if ( $this->m_sLModByColumn && $oEvent->sender->hasAttribute( $this->m_sLModByColumn ) )
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
    	if ( ! $this->owner->isNewRecord ) return PS::showDates( $this->owner, $this->m_sCreatedColumn, $this->m_sLModColumn, 'D M j, Y' );
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
    			if ( $this->owner->hasAttribute( $_sColumn ) )
    			{
    				$this->owner->setAttribute( $_sColumn, $_sTouchVal );
    				$_arUpdate[] = $_sColumn;
				}
    		}
		}
    	
		if ( $this->m_sLModColumn && $this->owner->hasAttribute( $this->m_sLModColumn ) ) 
		{
			$this->owner->setAttribute( $this->m_sLModColumn, ( null === $this->m_sDateTimeFunction ) ? date('Y-m-d H:i:s') : eval('return ' . $this->m_sDateTimeFunction . ';') );
    		$_arUpdate[] = $this->m_sLModColumn;
		}
			
		//	Only update if and what we've touched...
		return count( $_arUpdate ) ? $this->owner->save() : true;
	}
	
}