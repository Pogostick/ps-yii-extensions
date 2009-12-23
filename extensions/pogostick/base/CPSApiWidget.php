<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * The CPSApiWidget is the base class for all Pogostick widgets for Yii
 * 
 * @package 	psYiiExtensions
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.3
 * 
 * @filesource
 * @abstract
 */
abstract class CPSApiWidget extends CPSWidget
{
	//********************************************************************************
	//* Yii Override Methods
	//********************************************************************************

	/**
	* Constructor
	*
	*/
	public function __construct( $oOwner = null )
	{
		//	Call daddy
		parent::__construct( $oOwner );

		//	Attach our api behavior
		$this->attachBehavior( $this->m_sInternalName, 'pogostick.behaviors.CPSApiBehavior' );

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $this->m_sInternalName, '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'trace', $this->m_sInternalName );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Creates an array for requestMap
	*
	* @param array $arMap The map of items to insert into the array. Format is the same as {@link makeMapItem}
	* @param bool $bSetRequestMap If false, will NOT insert constructed array into {@link requestMap}
	* @returns array Returns the constructed array item ready to insert into your requestMap
	* @see makeMapItem
	*/
	protected function makeMapArray( $sApiName, $sSubApiName = null, array $arMap, $bSetRequestMap = true )
	{
		$_arFinal = array();

		foreach ( $arMap as $_sKey => $_oValue )
		{
			$_arTemp = $this->makeMapItem(
				( in_array( 'label', $_oValue ) ) ? $_oValue[ 'name' ] : $_oValue[ 'label' ],
				$_oValue[ 'name' ],
				( in_array( 'required', $_oValue ) ) ? $_oValue[ 'required' ] : false,
				( in_array( 'options', $_oValue ) ) ? $_oValue[ 'options' ] : null
			);

			$_arTemp = ( $sSubApiName != null ) ? array( $sSubApiName => $_arTemp ) : array( $_sKey, $_arTemp );

			if ( $bSetRequestMap )
				$this->requestMap[ $sApiName ] = $_arTemp;

			array_merge( $_arFinal[ $sApiName ], $_arTemp );
		}

		return( $_arFinal );
	}

	/**                                                   S
	* Creates an entry for requestMap and inserts it into the array.
	*
	* @param string $sLabel The label or friendly name of this map item
	* @param string $sParamName The actual parameter name to send to API. If not specified, will default to $sLabel
	* @param bool $bRequired Set to true if the parameter is required
	* @param array $arOptions If supplied, will merge with generated options
	* @param array $arTargetArray If supplied, will insert into array
	* @returns array Returns the constructed array item ready to insert into your requestMap
	*/
	protected function makeMapItem( $sLabel, $sParamName = null, $bRequired = false, array $arOptions = null, array $arTargetArray = null )
	{
		//	Build default settings
		$_arMapOptions = array( 'name' => ( null != $sParamName ) ? $sParamName : $sLabel, 'required' => $bRequired );

		//	Add on supplied options
		if ( null != $arOptions )
			$_arMapOptions = array_merge( $_arMapOptions, $arOptions );

		//	Insert for caller if requested
		if ( null != $arTargetArray )
			$arTargetArray[ $sLabel ] = $_arMapOptions;

		//	Return our array
		return( $_arMapOptions );
	}

	//********************************************************************************
	//* Private methods
	//********************************************************************************

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateJavascript()
	{
	}

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateHtml()
	{
	}

	//********************************************************************************
	//* Events and Handlers
	//********************************************************************************

	/**
	 * Declares events and the corresponding event handler methods.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 * @see CBehavior::events
	 */
	public function events()
	{
		return(
			array_merge(
				parent::events(),
				array(
					'onBeforeApiCall' => 'beforeApiCall',
					'onAfterApiCall' => 'afterApiCall',
					'onRequestComplete' => 'requestComplete',
				)
			)
		);
	}

	/**
	* Call to raise the onBeforeApiCall event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function beforeApiCall( $oEvent )
	{
		$this->onBeforeApiCall( $oEvent );
	}

	/**
	* Raises the onBeforeApiCall event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function onBeforeApiCall( $oEvent )
	{
		$this->raiseEvent( 'onBeforeApiCall', $oEvent );
	}

	/**
	* Call to raise the onAfterApiCall event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function afterApiCall( $oEvent )
	{
		$this->onAfterApiCall( $oEvent );
	}

	/**
	* Raises the onAfterApiCall event. $oEvent contains "raw" return data
	*
	* @param CPSApiEvent $oEvent
	*/
	public function onAfterApiCall( $oEvent )
	{
		$this->raiseEvent( 'onAfterApiCall', $oEvent );
	}

	/**
	* Call to raise the onRequestComplete event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function requestComplete( $oEvent )
	{
		$this->onRequestComplete( $oEvent );
	}

	/**
	* Raises the onRequestComplete event. $oEvent contains "processed" return data (if applicable)
	*
	* @param CPSApiEvent $oEvent
	*/
	public function onRequestComplete( $oEvent )
	{
		$this->raiseEvent( 'onRequestComplete', $oEvent );
	}

}