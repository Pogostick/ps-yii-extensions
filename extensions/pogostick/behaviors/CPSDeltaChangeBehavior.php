<?php
/**
 * CPSDeltaChangeBehavior.php class file.
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
 * Provides reference between saves for changed columns
 */
class CPSDeltaChangeBehavior extends CActiveRecordBehavior
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
	public function getLastAttribute( $oWhich ) { return PS::o( $this->m_arLastAttributes, $oWhich ); }
	
	/**
	* If true, comparisons will be done in a case-insensitive manner. Defaults to true.
	* @var boolean
	*/
	protected $m_bCaseInsensitive = true;
	public function getCaseInsensitive() { return $this->m_bCaseInsensitive; }
	public function setCaseInsensitive( $bValue ) { $this->m_bCaseInsensitive = $bValue; }
	
	//********************************************************************************
	//*  Event Handlers
	//********************************************************************************
	
	/**
	* After a row is pulled from the database...
	*/
	public function afterFind( CEvent $oEvent )
	{
		//	Get fresh values
		$this->m_arLastAttributes = $oEvent->sender->getAttributes();
		
		//	Let parents have a go...
		return parent::afterFind( $oEvent );
	}
	
	/**
	* Hijack the method to track changes
	* 
	* @param string $sAttribute
	* @param mixed $oValue
	* @return boolean
	*/
	public function setAttribute( $sAttribute, $oValue )
	{
		//	Set old value before we change...
		$this->m_arLastAttributes[ $sAttribute ] = $this->owner->getAttribute( $sAttribute );
		
		//	Set it and forget it!
		$this->owner->setAttribute( $sAttribute, $oValue );
	}
	
	/**
	* Returns an array of changed attributes since last save.
	* @returns array The changed set of attributes or an empty array.
	*/
	public function getChangedSet( $arAttributes = array(), $bReturnChanges = false )
	{
		$_arOut = array();
		
		foreach ( $this->m_arLastAttributes as $_sKey => $_sValue )
		{
			//	Only return asked for attributes
			if ( ! empty( $arAttributes ) && ! in_array( $_sKey, $arAttributes ) )
				continue;
				
			//	This value changed...
			if ( $_arTemp = $this->checkAttributeChange( $_sKey, $bReturnChanges ) )
				$_arOut = array_merge( $_arOut, $_arTemp );
		}
	
		return $_arOut;
	}
	
	/**
	* Returns true if the attribute(s) changed since save
	* 
	* @param string|array $oAttributes You may pass in a single attribute or an array of attributes to check
	* @returns boolean
	*/
	public function didChange( $oAttributes )
	{
		$_arCheck = $oAttributes;
		if ( ! is_array( $_arCheck ) ) $_arCheck = array( $_arCheck );

		foreach ( $_arCheck as $_sKey )
		{
			if ( $this->checkAttributeChange( $_sKey ) )
				return true;
		}
			
		return false;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* If attribute has changed, returns array of old/new values.
	* 
	* @param string $sAttribute
	* @returns array
	*/
	protected function checkAttributeChange( $sAttribute, $bReturnChanges = false )
	{
		$_arOut = array();
		$_bChanged = false;
		$_arSchema = $this->owner->getSchema();

		$_oNewValue = PS::nvl( $this->owner->getAttribute( $sAttribute ), 'NULL' );
		$_oOldValue = PS::nvl( $this->getLastAttribute( $sAttribute ), 'NULL' );

		//	Make dates look the same for string comparison
		if ( isset( $_arSchema[ $sAttribute ] ) && ( $_arSchema[ $sAttribute ]->dbType == 'date' || $_arSchema[ $sAttribute ]->dbType == 'datetime' ) )
		{
			$_oOldValue = date( 'Y-m-d H:i:s', strtotime( $_oOldValue ) );
			$_oNewValue = date( 'Y-m-d H:i:s', strtotime( $_oNewValue ) );
			$_bChanged = ( $_oOldValue != $_oNewValue );

			Yii::trace( 'Date Compare: (' . $_oOldValue . ' -> ' . $_oNewValue . ')', __METHOD__ );
		}
		else
			$_bChanged = ( $this->m_bCaseInsensitive ) ? ( 0 != strcasecmp( $_oOldValue, $_oNewValue ) ) : ( 0 != strcmp( $_sOldValue, $_sNewValue ) );

		//	Return the change...
		if ( $_bChanged ) $_arOut[ $sAttribute ] = $bReturnChanges ? array( $_oOldValue, $_oNewValue ) : $_oOldValue;
		return empty( $_arOut ) ? null : $_arOut;
	}
	
}                       
