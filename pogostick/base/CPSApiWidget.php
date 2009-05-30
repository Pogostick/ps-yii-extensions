<?php
/**
 * CPSApiWidget class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.gnu.org/licenses/gpl.html
 */

/**
 * The CPSApiWidget is the base class for all Pogostick widgets for Yii
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Base
 * @filesource
 * @abstract
 * @since 1.0.3
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
	* @abstract
	* @return string
	*/
	abstract protected function generateJavascript();

	/**
	* Generates the javascript code for the widget
	*
	* @abstract
	* @return string
	*/
	abstract protected function generateHtml();

	//********************************************************************************
	//* Event Handlers
	//********************************************************************************

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
		$this->raiseEvent( 'onBeforeApiCall', $oEvent );
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