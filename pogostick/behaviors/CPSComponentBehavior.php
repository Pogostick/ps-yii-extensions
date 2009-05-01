<?php
/**
 * CPSComponentBehavior class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSComponentBehavior provides base component behaviors to other classes
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Behaviors
 * @filesource
 * @since 1.0.4
 */
class CPSComponentBehavior extends CPSOptionsBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Constructor
	*
	*/
	public function __construct()
	{
		//	Log
		Yii::log( 'constructed psComponentBehavior object for [' . get_parent_class() . ']' );

		//	Set up our base settings
		$this->addOptions( self::getBaseOptions() );
	}

	/**
	* Allows for single behaviors
	*
	*/
	private function getBaseOptions()
	{
		return(
			array(
				'baseUrl' => array( 'value' => '', 'type' => 'string' ),
				'checkOptions' => array( 'value' => true, 'type' => 'boolean' ),
				'validOptions' => array( 'value' => array(), 'type' => 'array' ),
				'options' => array( 'value' => array(), 'type' => 'array' ),
				'checkCallbacks' => array( 'value' => true, 'type' => 'boolean' ),
				'validCallbacks' => array( 'value' => array(), 'type' => 'array' ),
				'callbacks' => array( 'value' => array(), 'type' => 'array' ),
			)
		);
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Inserts or updates the base options with the ones apssed in
	*
	* @param array $arOptions
	*/
	public function mergeOptions( array $arOptions )
	{
		foreach ( $arOptions as $_sKey => $_oValue )
			$this->setOption( $_sKey, $_oValue, true );
	}
	/**
    * Check the options against the valid ones
    *
    * @param array $value user's options
    * @param array $validOptions valid options
    */
	public function checkOptions( array $arOptions = null, array $arValidOptions = null )
 	{
		if ( ! isset( $arValidOptions ) )
			$arValidOptions = $this->validOptions;

		if ( ! isset( $arOptions ) )
			$arOptions = $this->options;

		foreach ( $arOptions as $_sKey => $_oValue )
		{
			if ( is_array( $arValidOptions ) && ! array_key_exists( $_sKey, $arValidOptions ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" is not a valid option', array( '{x}' => $_sKey ) ) );

			$_sType = gettype( $_oValue );
			$_oVOType = $arValidOptions[ $_sKey ][ 'type' ];

			if ( ( ! is_array( $_oVOType ) && ( $_sType != $_oVOType ) ) || ( is_array( $_oVOType ) && ! in_array( $_sType, $_oVOType ) ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" must be of type "{y}"', array( '{x}' => $_sKey, '{y}' => ( is_array( $_oVOType ) ) ? implode( ', ', $_oVOType ) : $_oVOType ) ) );

			if ( array_key_exists( 'valid', $arValidOptions[ $_sKey ] ) )
			{
				$_arValid = $arValidOptions[ $_sKey ][ 'valid' ];

				if ( is_array( $_arValid[ 'valid' ] ) && ! in_array( $_oValue, $_arValid ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" must be one of: "{y}"', array( '{x}' => $_sKey, '{y}' => implode( ', ', $_arValid ) ) ) );
			}

			if ( ( $_sType == 'array' ) && array_key_exists( 'elements', $arValidOptions[ $_sKey ] ) )
				$this->checkOptions( $_oValue, $arValidOptions[ $_sKey ][ 'elements' ] );
		}

		//	Now validate them...
		return( $this->validateOptions( $arOptions, $arValidOptions ) );
	}

	/**
	* Generates the options for the widget
	*
	* @param array $arOptions
	* @return string
	*/
	public function makeOptions( array $arOptions = null )
	{
		$_arOptions = ( $arOptions == null ) ? $this->getOption( 'options' ) : $arOptions;

		foreach ( $this->getOption( 'callbacks' ) as $_sKey => $_oValue )
		{
			if ( ! empty( $_oValue ) )
				$_arOptions[ "cb_{$_sKey}" ] = $_sKey;
		}

		//	Get all the options merged...
		$_arToEncode = array();

		foreach( $_arOptions as $_oOption )
		{
			//	Ignore private options
			if ( isset( $_oOption[ 'private' ] ) && true == $_oOption[ 'private' ] )
				continue;

			$_arToEncode[ $_oOption[ 'name' ] ] = $_oOption[ 'value' ];
		}

		if ( sizeof( $_arToEncode ) > 0 )
		{
			$_sEncodedOptions = CJavaScript::encode( $_arToEncode );

			//	Fix up the callbacks...
			foreach ( $this->getOption( 'callbacks' ) as $_sKey => $_oValue )
			{
				if ( ! empty( $_oValue ) )
				{
					if ( 0 == strncasecmp( $_oValue, 'function(', 9 ) )
						$_sEncodedOptions = str_replace( "'cb_{$_sKey}':'{$_sKey}'", "{$_sKey}:{$_oValue}", $_sEncodedOptions );
					else
						$_sEncodedOptions = str_replace( "'cb_{$_sKey}':'{$_sKey}'", "{$_sKey}:'{$_oValue}'", $_sEncodedOptions );
				}
			}

			return( $_sEncodedOptions );
		}

		return( null );
	}

	/**
	* Validates that required options have been specified...
	*
	* @param mixed $arOptions
	* @param mixed $arValidOptions
	*/
	public function validateOptions( array $arOptions , array $arValidOptions )
	{
		foreach ( $arOptions as $_sKey => $_oValue )
		{
			//	Is it a valid option?
			if ( ! array_key_exists( $_sKey, $arValidOptions ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" is not a valid option', array( '{x}' => $_sKey ) ) );

			$_oCurOption = $arOptions[ $_sKey ];
			$_oCurValidOption = $arValidOptions[ $_sKey ];

			if ( isset( $_oCurValidOption[ 'required' ] ) && $_oCurValidOption[ 'required' ] && ( ! $_oCurOption || empty( $_oCurOption ) ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" is a required option', array( '{x}' => $_sKey ) ) );
		}

		return( true );
	}

   /**
    *
    * @param array $value user's callbacks
    * @param array $validCallbacks valid callbacks
    */
	public function checkCallbacks( array $arCallbacks = null, array $arValidCallbacks = null )
	{
		if ( ! empty( $arValidCallbacks ) && is_array( $arValidCallbacks ) )
		{
			foreach ( $arCallbacks as $_sKey => $_oValue )
			{
				if ( ! in_array( $_sKey, $arValidWidgetCallbacks ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" must be one of: {y}', array( '{x}' => $_sKey, '{y}' => implode( ', ', $arValidCallbacks ) ) ) );
			}
		}
	}

}