<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * If attached to a model, fields are formatted per your configuration. Also provides a default sort for a model
 * 
 * @package 	psYiiExtensions
 * @subpackage 	behaviors
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 */
class CPSDataFormatBehavior extends CPSBaseActiveRecordBehavior
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
	
	/**
	* Default sort
	* @var string
	* @see getDefaultSort
	* @see setDefaultSort
	*/
	protected $m_sDefaultSort;

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************
	
	/**
	 * Retrieves a format
	 * @param string $sWhich
	 * @param string $sType
	 * @return string
	 */
	public function getFormat( $sWhich = 'afterFind', $sType = 'date' ) 
	{ 
		return PS::nvl( $this->m_arFormat[ $sWhich ][ $sType ], 'm/d/Y' ); 
	}
	
	/**
	 * Sets a format
	 * 
	 * @param string $sWhich
	 * @param string $sType
	 * @param string $sFormat
	 */
	public function setFormat( $sWhich = 'afterValidate', $sType = 'date', $sFormat = 'm/d/Y' ) 
	{ 
		if ( ! isset( $this->m_arFormat[ $sWhich ] ) )
			$this->m_arFormat[ $sWhich ] = array();
			
		$this->m_arFormat[ $sWhich ][ $sType ] = $sFormat; 
	}
	
	/**
	* Returns the default sort
	* @returns string
	* @see setDefaultSort
	*/
	public function getDefaultSort() { return $this->m_sDefaultSort; }

	/**
	* Sets the default sort
	* @param string $sValue
	* @see getDefaultSort
	*/
	public function setDefaultSort( $sValue ) { $this->m_sDefaultSort = $sValue; }

	//********************************************************************************
	//* Protected Methods
	//********************************************************************************
	
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
		$_sReturn = null;
		
		//	Apply formats
		switch ( $oColumn->dbType )
		{
			case 'date':
			case 'datetime':
			case 'timestamp':
				//	Handle blanks
				if ( null != $oValue && $oValue != '0000-00-00' && $oValue != '0000-00-00 00:00:00' ) 
					$_sReturn = date( $this->getFormat( $sWhich, $oColumn->dbType ), strtotime( $oValue ) );
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
		static $_sSchemaFor;
		
		$_oModel = $oEvent->sender;
		
		//	Cache for multi event speed
		if ( $_sSchemaFor != get_class( $_oModel ) )
		{
			$_arSchema = $_oModel->getMetaData()->columns;
			$_sSchemaFor = get_class( $_oModel );
		}
		
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
					$_sValue = $this->applyFormat( $_oCol, $_oModel->getAttribute( $_sName ), $sWhich );
					if ( $_sValue ) CPSLog::trace( __METHOD__, 'Apply format to ' . $_sName . ' [' . $_oModel->{$_sName} . ' -> ' . $_sValue . ']' );
					$_oModel->setAttribute( $_sName, $_sValue );
				}
			}
		}
		
		//	Papa don't preach...
		return parent::$sWhich( $oEvent );
	}
	
	//********************************************************************************
	//* Event Handlers
	//********************************************************************************
	
	/**
	* Apply any formats
	* @param CModelEvent $oEvent
	*/
	public function beforeValidate( $oEvent ) 
	{ 
		return $this->handleEvent( __FUNCTION__, $oEvent ); 
	}
	
	/**
	* Apply any formats
	* @param CEvent $oEvent
	*/
	public function afterValidate( $oEvent ) 
	{ 
		return $this->handleEvent( __FUNCTION__, $oEvent ); 
	}
	
	/**
	* Apply any formats
	* @param CEvent $oEvent
	*/
	public function beforeFind( $oEvent ) 
	{
		//	Is a default sort defined?
		if ( $this->m_sDefaultSort )
		{
			//	Is a sort defined?
			$_oCrit = $oEvent->sender->getDbCriteria();
			
			//	No sort? Set the default
			if ( ! $_oCrit->order )
				$oEvent->sender->getDbCriteria()->mergeWith( new CDbCriteria( array( 'order' => $this->m_sDefaultSort ) ) );
		}
		
		return $this->handleEvent( __FUNCTION__, $oEvent ); 
	}
	
	/**
	* Apply any formats
	* @param CEvent $oEvent
	*/
	public function afterFind( $oEvent ) 
	{ 
		return $this->handleEvent( __FUNCTION__, $oEvent ); 
	}
	
}