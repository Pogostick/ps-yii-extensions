<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @filesource
 */

/**
 * If attached to a model, fields are formatted per your configuration. Also provides a default sort for a model
 *
 * @package 	psYiiExtensions
 * @subpackage 	behaviors
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSDataFormatBehavior.php 383 2010-05-18 03:58:13Z jerryablan@gmail.com $
 * @since 		v1.0.6
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
	protected $_dateFormat = array(
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
	protected $_defaultSort;

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	 * Retrieves a format
	 * @param string $which
	 * @param string $type
	 * @return string
	 */
	public function getFormat( $which = 'afterFind', $type = 'date' )
	{
		return CPSHelperBase::nvl( $this->_dateFormat[ $which ][ $type ], 'm/d/Y' );
	}

	/**
	 * Sets a format
	 *
	 * @param string $which
	 * @param string $type
	 * @param string $sFormat
	 */
	public function setFormat( $which = 'afterValidate', $type = 'date', $sFormat = 'm/d/Y' )
	{
		if ( ! isset( $this->_dateFormat[ $which ] ) )
			$this->_dateFormat[ $which ] = array();

		$this->_dateFormat[ $which ][ $type ] = $sFormat;
	}

	/**
	* Returns the default sort
	* @return string
	* @see setDefaultSort
	*/
	public function getDefaultSort() { return $this->_defaultSort; }

	/**
	* Sets the default sort
	* @param string $sValue
	* @see getDefaultSort
	*/
	public function setDefaultSort( $sValue ) { $this->_defaultSort = $sValue; }

	//********************************************************************************
	//* Protected Methods
	//********************************************************************************

	/**
	* Applies the requested format to the value and returns it.
	* Override this method to apply additional format types.
	*
	* @param CDbColumnSchema $column
	* @param mixed $value
	* @param string $which
	* @return mixed
	*/
	protected function applyFormat( $column, $value, $which = 'view' )
	{
		$_result = null;

		//	Apply formats
		switch ( $column->dbType )
		{
			case 'date':
			case 'datetime':
			case 'timestamp':
				//	Handle blanks
				if ( null != $value && $value != '0000-00-00' && $value != '0000-00-00 00:00:00' )
					$_result = date( $this->getFormat( $which, $column->dbType ), strtotime( $value ) );
				break;

			default:
				$_result = $value;
				break;
		}

		return $_result;
	}

	/**
	* Process the data and apply formats
	*
	* @param string $which
	* @param CEvent $event
	*/
	protected function handleEvent( $which, CEvent $event )
	{
		static $_schema;
		static $_schemaFor;

		$_model = $event->sender;

		//	Cache for multi event speed
		if ( $_schemaFor != get_class( $_model ) )
		{
			$_schema = $_model->getMetaData()->columns;
			$_schemaFor = get_class( $_model );
		}

		//	Not for us? Pass it through...
		if ( isset( $this->_dateFormat[ $which ] ) )
		{
			//	Is it safe?
			if ( ! $_schema )
			{
				$_model->addError( null, 'Cannot read schema for data formatting' );
				return false;
			}

			//	Scoot through and update values...
			foreach ( $_schema as $_name => $_column )
			{
				if ( ! empty( $_name ) && $_model->hasAttribute( $_name ) && isset( $_schema[ $_name ], $this->_dateFormat[ $which ][ $_column->dbType ] ) )
				{
					$_value = $this->applyFormat( $_column, $_model->getAttribute( $_name ), $which );
//					if ( $_value ) CPSLog::trace( __METHOD__, 'Apply format to ' . $_name . ' [' . $_model->{$_name} . ' -> ' . $_value . ']' );
					$_model->setAttribute( $_name, $_value );
				}
			}
		}

		//	Papa don't preach...
		return parent::$which( $event );
	}

	//********************************************************************************
	//* Event Handlers
	//********************************************************************************

	/**
	* Apply any formats
	* @param CModelEvent event parameter
	*/
	public function beforeValidate( $event )
	{
		return $this->handleEvent( __FUNCTION__, $event );
	}

	/**
	* Apply any formats
	* @param CEvent $event
	*/
	public function afterValidate( $event )
	{
		return $this->handleEvent( __FUNCTION__, $event );
	}

	/**
	* Apply any formats
	* @param CEvent $event
	*/
	public function beforeFind( $event )
	{
		//	Is a default sort defined?
		if ( $this->_defaultSort )
		{
			//	Is a sort defined?
			$_criteria = $event->sender->getDbCriteria();

			//	No sort? Set the default
			if ( ! $_criteria->order )
			{
				$event->sender->getDbCriteria()->mergeWith( 
					new CDbCriteria( 
						array( 
							'order' => $this->_defaultSort,
						)
					)
				);
			}
		}

		return $this->handleEvent( __FUNCTION__, $event );
	}

	/**
	* Apply any formats
	* @param CEvent $event
	*/
	public function afterFind( $event )
	{
		return $this->handleEvent( __FUNCTION__, $event );
	}

}