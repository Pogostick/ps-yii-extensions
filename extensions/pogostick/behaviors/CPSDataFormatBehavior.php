<?php
/**
 * CPSDataFormatBehavior.php class file.
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
 * If attached to a model, fields are formatted per your configuration.
 */
class CPSDataFormatBehavior extends CActiveRecordBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/***
	* Holds the default/configured formats for use when populating fields
	* 
	* array( 
	* 	'evemt' => array(        		//	The event to apply format in
	* 		'dataType' => <format>		//	The format for the display
	* 		'method' => <function>		//	The function to call for formatting
	* 	),								//		Send array(object,method) for class methods
	* 	'evemt' => array(        		//	The event to apply format in
	* 		'dataType' => <format>		//	The format for the display
	* 		'method' => <function>		//	The function to call for formatting
	* 	),								//		Send array(object,method) for class methods
	* 	...
	* 
	* @var array
	*/
	protected $m_arFormat = array(
		'afterFind' => array(
			'date' => 'm/d/Y',
			'datetime' => 'm/d/Y H:i:s',
		),
		'afterValidate' => array(
			'date' => 'Y-m-d',
			'datetime' => 'Y-m-d H:i:s',
		),
	);
	public function getFormat( $sWhich = 'afterFind', $sType = 'date' ) { return PS::nvl( $this->m_arFormat[ $sWhich ][ $sType ], 'm/d/Y' ); }
	public function setFormat( $sWhich = 'afterValidate', $sType = 'date', $sFormat = 'm/d/Y' ) { $this->m_arFormat[ $sWhich ][ $sType ] = $sFormat; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Apply any formats
	* 
	* @param CEvent $oEvent
	*/
	public function beforeValidate( $oEvent ) { return $this->handleEvent( __FUNCTION__, $oEvent ); }
	public function afterValidate( $oEvent ) { return $this->handleEvent( __FUNCTION__, $oEvent ); }
	public function beforeFind( $oEvent ) { return $this->handleEvent( __FUNCTION__, $oEvent ); }
	public function afterFind( $oEvent ) { return $this->handleEvent( __FUNCTION__, $oEvent ); }
	
	/**
	* Applies the requested format to the value and returns it.
	* Override this method to apply additional format types.
	* 
	* @param CDbColumnSchema $oColumn
	* @param mixed $oValue
	* @param string $sWhich
	* @returns mixed
	*/
	protected function applyFormat( $oColumn, $oValue, $sWhich = 'view' )
	{
		//	Apply formats
		switch ( $oColumn->dbType )
		{
			case 'date':
			case 'datetime':
				//	Handle blanks
				if ( null == $oValue || $oValue == '0000-00-00' || $oValue == '0000-00-00 00:00:00' ) 
					$_sReturn = null;
				else
					$_sReturn = date( $this->getFormat( $sWhich, $oColumn->dbType ), strtotime( $oValue ) );
					
//				echo 'Formatted: ' . $_sReturn . '<BR/>';
				break;
				
			default:
				$_sReturn = $oValue;
				break;
		}
		
		return $_sReturn;
	}
	
	/**
	* Process the data and apply formats
	* 
	* @param string $sWhich
	* @param CEvent $oEvent
	*/
	protected function handleEvent( $sWhich, CEvent $oEvent )
	{
		static $_arSchema;
		$_oModel = $oEvent->sender;
		
		//	Cache for multi event speed
		if ( ! $_arSchema ) $_arSchema = $_oModel->getMetaData()->columns;
		
		//	Not for us? Pass it through...
		if ( isset( $this->m_arFormat[ $sWhich ] ) )
		{
			//	Is it safe?
			if ( ! $_arSchema )
			{
				$_oModel->addError( null, 'Cannot read schema for data formatting' );
				return false;
			}
				
			//	Scoot through and update values...
			foreach ( $_arSchema as $_sName => $_oCol )
			{
				if ( ! empty( $_sName ) && $_oModel->hasAttribute( $_sName ) && isset( $_arSchema[ $_sName ], $this->m_arFormat[ $sWhich ][ $_oCol->dbType ] ) )
				{
//					echo 'About to apply... ';
					$_oModel->{$_sName} = $this->applyFormat( $_oCol, $_oModel->{$_sName}, $sWhich );
				}
			}
		}
		
		//	Papa don't preach...
		return parent::$sWhich( $oEvent );
	}
}