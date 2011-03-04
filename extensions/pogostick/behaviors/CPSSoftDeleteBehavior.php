<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Provides soft-deleting of records
 * 
 * @package 	psYiiExtensions
 * @subpackage 	behaviors
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSSoftDeleteBehavior.php 378 2010-04-03 02:12:59Z jerryablan@gmail.com $
 * @since 		v1.0.6
 * 
 * @filesource
 * 
 * @property string $softDeleteColumn The attribute which indicates a soft-delete
 * @property array $softDeleteValue Two item array containing the [false,true] values for soft-deletion. Defaults to array(0,1) ('false' and 'true' respectively).
 * 
 */
class CPSSoftDeleteBehavior extends CPSBaseActiveRecordBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* If defined, all deletes are soft
	* @var string
	*/
	protected $m_sSoftDeleteColumn = null;
	
	/**
	* Soft delete indicator [false,true]
	* @var array
	*/
	protected $m_arSoftDeleteValue = array( 0, 1 );

	//********************************************************************************
	//* Properties
	//********************************************************************************
	
	/**
	* Returns the soft-delete column for this model
	* @return string
	*/
	public function getSoftDeleteColumn() { return $this->m_sSoftDeleteColumn; }
	
	/**
	* Sets the soft-delete column for this model
	* @var string
	*/
	public function setSoftDeleteColumn( $sValue ) { $this->m_sSoftDeleteColumn = $sValue; }	

	/**
	* Returns the soft-delete values for this model [false,true]
	* @return array
	*/
	public function getSoftDeleteValue() { return $this->m_arSoftDeleteValue; }
	
	/**
	* Sets the soft-delete values for this model
	* @var array $arValue The true/false values for soft-deletion.
	*/
	public function setSoftDeleteValue( $arValue ) { $this->m_arSoftDeleteValue = $arValue; }	
	
	//********************************************************************************
	//*  Event Handlers
	//********************************************************************************
	
	/**
	* Soft deletes models that have that feature
	* @params CEvent $oEvent
	* @return boolean
	*/
	public function beforeDelete( $oEvent )
	{
		//	Pass it on...
		if ( parent::beforeDelete( $oEvent ) )
		{
			//	We want to be the top of the chain...
			if ( $this->m_sSoftDeleteColumn && $oEvent->isValid && ! $oEvent->handled )
			{
				//	Perform a soft delete if this model allows
				if ( $oEvent->sender->hasAttribute( $this->m_sSoftDeleteColumn ) )
				{
					$oEvent->isValid = false;
					$oEvent->handled = true;
					$oEvent->sender->setAttribute( $this->m_sSoftDeleteColumn, $this->m_arSoftDeleteValue[ 1 ] );
					if ( ! $oEvent->sender->update( array( $this->m_sSoftDeleteColumn ) ) )
						throw new CDbException( 'Error saving soft delete row.' );
				}
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	* Insert our soft-delete criteria
	* @param CEvent $oEvent
	*/
	public function beforeFind( $oEvent )
	{
		if ( $this->m_sSoftDeleteColumn && $this->owner->hasAttribute( $this->m_sSoftDeleteColumn ) ) 
		{
			//	Merge in the soft delete indicator
			$oEvent->sender->getDbCriteria()->mergeWith(
				array( 
					'condition' => $this->m_sSoftDeleteColumn . ' = :softDeleteValue', 
					'params' => array( ':softDeleteValue' => $this->m_arSoftDeleteValue[ 0 ] ),
				)
			);
		}

		//	Pass it on...
    	return parent::beforeFind( $oEvent );
    }
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Undeletes a soft-deleted model
	* 
	* @return boolean
	*/
	public function undelete()
	{
		if ( $this->m_sSoftDeleteColumn )
		{
			//	Perform a soft delete if this model allows
			if ( $this->hasAttribute( $this->m_sSoftDeleteColumn ) )
			{
				$this->setAttribute( $this->m_sSoftDeleteColumn, $this->m_arSoftDeleteValue[ 0 ] );
				return $this->update( array( $this->m_sSoftDeleteColumn ) );
			}
		}
		
		//	Otherwise, not possible
		return false;
	}
	
}