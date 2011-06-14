<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Provides reference between saves for changed columns
 * 
 * @package 	psYiiExtensions
 * @subpackage 	behaviors
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSDeltaChangeBehavior.php 396 2010-07-27 17:36:55Z jerryablan@gmail.com $
 * @since 		v1.0.6
 * 
 * @filesource
 * 
 * @property array $lastAttributes The attributes when the model was fresh
 * @property boolean $caseInsensitive Changes are compared in a case-insensitive manner if true. Defaults to true.
 */
class CPSDeltaChangeBehavior extends CPSBaseActiveRecordBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* Access to prior data after a save
	* @var array
	*/
	protected $m_arLastAttributes = array();
	public function getLastAttributes() { return $this->m_arLastAttributes; }
	public function getLastAttribute( $oWhich ) { return CPSHelperBase::o( $this->m_arLastAttributes, $oWhich ); }
	
	/**
	* If true, comparisons will be done in a case-insensitive manner. Defaults to true.
	* @var boolean
	*/
	protected $m_bCaseInsensitive = true;
	public function getCaseInsensitive() { return $this->m_bCaseInsensitive; }
	public function setCaseInsensitive( $bValue ) { $this->m_bCaseInsensitive = $bValue; }
	
	/**
	* Caches change state.
	* @var boolean
	*/
	protected $m_bIsDirty = false;
	
	//********************************************************************************
	//*  Event Handlers
	//********************************************************************************
	
	/**
	* After a row is pulled from the database...
	* @param CEvent $oEvent
	*/
	public function afterFind( $oEvent )
	{
//		CPSLog::trace( __METHOD__, 'afterFind event raised' );

		//	Get fresh values
		$this->m_arLastAttributes = $oEvent->sender->getAttributes();
		$this->m_bIsDirty = false;
		
		//	Let parents have a go...
		return parent::afterFind( $oEvent );
	}

	/**
	* After a row is saved to the database...
	* @param CEvent $oEvent
	*/
	public function afterSave( $oEvent )
	{
//		CPSLog::trace( __METHOD__, 'afterSave event raised' );

		//	Get fresh values
		$this->m_arLastAttributes = $oEvent->sender->getAttributes();
		$this->m_bIsDirty = false;

		//	Let parents have a go...
		return parent::afterSave( $oEvent );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Reverts a model back to its state before changes were made.
	* @return boolean True if reverted.
	*/
	public function revert()
	{
		$this->owner->setAttributes( $this->m_arLastAttributes );
	}

	/**
	* Returns an array of changed attributes since last save.
	* @return array The changed set of attributes or an empty array.
	* @see didChange
	*/
	public function getChangedSet( $arAttributes = array(), $bReturnChanges = false )
	{
		$_arOut = array();

		if ( $this->m_bIsDirty )
		{		
			foreach ( $this->m_arLastAttributes as $_sKey => $_sValue )
			{
				//	Only return asked for attributes
				if ( ! empty( $arAttributes ) && ! in_array( $_sKey, $arAttributes ) )
					continue;
					
				//	This value changed...
				if ( $_arTemp = $this->checkAttributeChange( $_sKey, $bReturnChanges ) )
					$_arOut = array_merge( $_arOut, $_arTemp );
			}
		}
	
		return $_arOut;
	}
	
	/**
	* Returns true if the attribute(s) changed since save
	* 
	* @param string|array $oAttributes You may pass in a single attribute or an array of attributes to check
	* @return boolean
	* @see getChangedSet
	*/
	public function didChange( $oAttributes )
	{
		if ( ! $this->m_bIsDirty )
		{
			$_arCheck = $oAttributes;
			if ( ! is_array( $_arCheck ) ) $_arCheck = array( $_arCheck );

			foreach ( $_arCheck as $_sKey => $_oValue )
			{
				if ( $this->checkAttributeChange( $_sKey ) )
				{
					$this->m_bIsDirty = true;
					break;
				}
			}
		}
			
		//	Return
		return $this->m_bIsDirty;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* If attribute has changed, returns array of old/new values.
	* 
	* @param string $sAttribute
	* @return array
	*/
	protected function checkAttributeChange( $sAttribute, $bReturnChanges = false, $bQuickCheck = false )
	{
		$_arOut = array();
		$_bChanged = false;
		$_arSchema = ( $this->owner instanceof CPSModel ) ? $this->owner->getSchema() : $this->owner->getMetaData()->columns;

		//	Get old and new values
		$_oNewValue = CPSHelperBase::nvl( $this->owner->getAttribute( $sAttribute ), 'NULL' );
		$_oOldValue = CPSHelperBase::nvl( $this->getLastAttribute( $sAttribute ), 'NULL' );

		//	Make dates look the same for string comparison
		if ( isset( $_arSchema[ $sAttribute ] ) && ( $_arSchema[ $sAttribute ]->dbType == 'date' || $_arSchema[ $sAttribute ]->dbType == 'datetime' ) )
			$_bChanged = ( strtotime( $_oOldValue ) != strtotime( $_oNewValue ) );
		else
			$_bChanged = ( $this->m_bCaseInsensitive ) ? ( 0 != strcasecmp( $_oOldValue, $_oNewValue ) ) : ( 0 != strcmp( $_sOldValue, $_sNewValue ) );

		//	Record the change...
		if ( $_bChanged ) 
		{
			//	Set our global dirty flag
			if ( ! $this->m_bIsDirty ) $this->m_bIsDirty = true;
			
			//	Just wanna know?
			if ( $bQuickCheck ) return true;
				
			//	Store info
			$_arOut[ $sAttribute ] = $bReturnChanges ? array( $_oOldValue, $_oNewValue ) : $_oOldValue;
		}
		
		//	Return
		return empty( $_arOut ) ? ( $bQuickCheck ? false : null ) : $_arOut;
	}
	
}