<?php
/**
 * CPSSoftDeleteBehavior.php class file.
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
 * Provides soft-deleting of records
 */
class CPSSoftDeleteBehavior extends CActiveRecordBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

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
	
	//********************************************************************************
	//*  Event Handlers
	//********************************************************************************
	
	/**
	* Soft deletes models that have that feature
	* @params CEvent $oEvent
	* @returns boolean
	*/
	public function beforeDelete( CEvent $oEvent )
	{
		//	Can we?
		parent::beforeDelete( $oEvent );
		
		if ( $this->m_sSoftDeleteColumn && $oEvent->isValid && ! $oEvent->handled )
		{
			//	Perform a soft delete if this model allows
			if ( $this->hasAttribute( $this->m_sSoftDeleteColumn ) )
			{
				$this->setAttribute( $this->m_sSoftDeleteColumn, $this->m_arSoftDeleteValue[ 1 ] );
				$oEvent->handled = $this->update();
			}
		}
	}
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Undeletes a soft-deleted model
	* 
	* @returns boolean
	*/
	public function undelete()
	{
		if ( $this->m_sSoftDeleteColumn )
		{
			//	Perform a soft delete if this model allows
			if ( $this->hasAttribute( $this->m_sSoftDeleteColumn ) )
			{
				$this->setAttribute( $this->m_sSoftDeleteColumn, $this->m_arSoftDeleteValue[ 0 ] );
				return $this->update();
			}
		}
		
		//	Otherwise, not possible
		return false;
	}
	
	/**
	* Make "active" the default scope...
	* @returns array
	*/
    public function defaultScope()
    {
		if ( $this->m_sSoftDeleteColumn && $this->owner->hasAttribute( $this->m_sSoftDeleteColumn ) ) 
			return array( 'condition' => $this->m_sSoftDeleteColumn . ' = :softDeleteValue', 'params' => array( ':softDeleteValue' => $this->m_arSoftDeleteValue[ 0 ] ) );
			
    	return array();
    }
 
}
