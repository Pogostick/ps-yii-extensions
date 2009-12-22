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
 * @property string $softDeleteColumn The attribute which indicates a soft-delete
 * @property array $softDeleteValue Two item array containing the [false,true] values for soft-deletion. Defaults to array(0,1) ('false' and 'true' respectively).
 */
class CPSSoftDeleteBehavior extends CActiveRecordBehavior
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
	* @returns string
	*/
	public function getSoftDeleteColumn() { return $this->m_sSoftDeleteColumn; }
	
	/**
	* Sets the soft-delete column for this model
	* @var string
	*/
	public function setSoftDeleteColumn( $sValue ) { $this->m_sSoftDeleteColumn = $sValue; }	

	/**
	* Returns the soft-delete values for this model [false,true]
	* @returns array
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
	* @returns boolean
	*/
	public function beforeDelete( CEvent $oEvent )
	{
		//	Pass it on...
		parent::beforeDelete( $oEvent );
		
		//	We want to be the top of the chain...
		if ( $this->m_sSoftDeleteColumn && $oEvent->isValid && ! $oEvent->handled )
		{
			//	Perform a soft delete if this model allows
			if ( $oEvent->sender->hasAttribute( $this->m_sSoftDeleteColumn ) )
			{
				$oEvent->isValid = false;
				$oEvent->handled = true;
				$oEvent->sender->setAttribute( $this->m_sSoftDeleteColumn, $this->m_arSoftDeleteValue[ 1 ] );
				if ( ! $oEvent->sender->save() )
					throw new CDbException( 'Error saving soft delete row.' );
			}
		}
	}
	
	/**
	* Insert our soft-delete criteria
	* @param CEvent $oEvent
	*/
	public function beforeFind( CEvent $oEvent )
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
				return $this->save();
			}
		}
		
		//	Otherwise, not possible
		return false;
	}
	
}