<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link      http://www.pogostick.com Pogostick, LLC.
 * @license   http://www.pogostick.com/licensing
 * @filesource
 */

/**
 * The CPSApiWidget is the base class for all Pogostick API widgets for Yii
 *
 * @package           psYiiExtensions
 * @subpackage        base.components
 *
 * @author            Jerry Ablan <jablan@pogostick.com>
 * @version           SVN: $Id: CPSApiWidget.php 405 2010-10-21 21:44:02Z jerryablan@gmail.com $
 * @since             v1.0.3
 * @abstract
 */
abstract class CPSApiWidget extends CPSWidget
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Preinitialize
	 */
	public function preinit()
	{
		//	Call daddy
		parent::preinit();

		//	Attach our api behavior
		$this->attachBehavior( 'pye-api', 'pogostick.behaviors.CPSComponentBehavior' );

		//	Attach our events
		$this->attachEventHandler( 'onBeforeApiCall', array( $this, 'beforeApiCall' ) );
		$this->attachEventHandler( 'onAfterApiCall', array( $this, 'afterApiCall' ) );
		$this->attachEventHandler( 'onRequestComplete', array( $this, 'requestComplete' ) );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Creates an array for requestMap
	 *
	 * @param array $apiMap        The map of items to insert into the array. Format is the same as {@link makeMapItem}
	 * @param bool  $setRequestMap If false, will NOT insert constructed array into {@link requestMap}
	 *
	 * @return array Returns the constructed array item ready to insert into your requestMap
	 * @see makeMapItem
	 */
	protected function makeMapArray( $apiName, $subApiName = null, array $apiMap, $setRequestMap = true )
	{
		$_parameters = array();

		foreach ( $apiMap as $_key => $_value )
		{
			$_mapItem = $this->makeMapItem(
				( in_array( 'label', $_value ) ) ? $_value['name'] : $_value['label'],
				$_value['name'],
				( in_array( 'required', $_value ) ) ? $_value['required'] : false,
				( in_array( 'options', $_value ) ) ? $_value['options'] : null
			);

			$_mapItem = ( $subApiName != null ) ? array( $subApiName => $_mapItem ) : array( $_key, $_mapItem );

			if ( $setRequestMap )
			{
				$this->requestMap[$apiName] = $_mapItem;
			}

			array_merge( $_parameters[$apiName], $_mapItem );
		}

		return ( $_parameters );
	}

	/**                                                   S
	 * Creates an entry for requestMap and inserts it into the array.
	 *
	 * @param string $itemLabel     The label or friendly name of this map item
	 * @param string $parameterName The actual parameter name to send to API. If not specified, will default to $itemLabel
	 * @param bool   $required      Set to true if the parameter is required
	 * @param array  $options       If supplied, will merge with generated options
	 * @param array  $target        If supplied, will insert into array
	 *
	 * @return array Returns the constructed array item ready to insert into your requestMap
	 */
	protected function makeMapItem( $itemLabel, $parameterName = null, $required = false, $options = array(), array &$target = null )
	{
		//	Build default settings
		$_mappingOptions = array(
			'name'     => ( null != $parameterName ) ? $parameterName : $itemLabel,
			'required' => $required
		);

		//	Add on supplied options
		if ( !empty( $options ) )
		{
			$_mappingOptions = array_merge( $_mappingOptions, $options );
		}

		//	Insert for caller if requested
		if ( null != $target )
		{
			$target[$itemLabel] = $_mappingOptions;
		}

		//	Return our array
		return ( $_mappingOptions );
	}

	//********************************************************************************
	//* Event Handlers
	//********************************************************************************

	/**
	 * Raises the onBeforeApiCall event
	 *
	 * @param CPSApiEvent $event
	 */
	public function onBeforeApiCall( $event )
	{
		$this->raiseEvent( 'onBeforeApiCall', $event );
	}

	/**
	 * Base event handler
	 *
	 * @param CPSApiEvent $event
	 */
	public function beforeApiCall( $event )
	{
	}

	/**
	 * Raises the onAfterApiCall event. $event contains "raw" return data
	 *
	 * @param CPSApiEvent $event
	 */
	public function onAfterApiCall( $event )
	{
		$this->raiseEvent( 'onAfterApiCall', $event );
	}

	/**
	 * Base event handler
	 *
	 * @param CPSApiEvent $event
	 */
	public function afterApiCall( $event )
	{
	}

	/**
	 * Raises the onRequestComplete event. $event contains "processed" return data (if applicable)
	 *
	 * @param CPSApiEvent $event
	 */
	public function onRequestComplete( $event )
	{
		$this->raiseEvent( 'onRequestComplete', $event );
	}

	/**
	 * Base event handler
	 *
	 * @param CPSApiEvent $event
	 */
	public function requestComplete( $event )
	{
	}

}