<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

//	Our requirements
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'PS.php' );

/**
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id: CPSOptionHelper.php 368 2010-01-18 01:55:44Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *
 * @filesource
 */
class CPSOptionHelper implements IPSBase
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * A key for use when mapping callbacks
	 */
	const	CB_KEY	= '__pscb_';

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Check to see if the value follows a callback function pattern.
	* Basically we want to check for values that should *NOT* be quoted.
	*
	* @param string $sValue
	* @return boolean
	*/
	public static function isScriptCallback( $sValue )
	{
		return is_string( $sValue ) &&
			(
				$sValue === 'true' ||
				$sValue === 'false' ||
				0 == strncasecmp( $sValue, 'function(', 9 ) ||
				0 == strncasecmp( $sValue, 'new Date(', 9 ) ||
				0 == strncasecmp( $sValue, 'jQuery(', 7 ) ||
				0 == strncasecmp( $sValue, '$(', 2 )
			);
	}

	/**
	* Parses a rule pattern string
	*
	* @param string $sKey The key
	* @param mixed $oPattern The pattern string or array
	* @param boolean $bPrivate Forces this option to be private
	* @return array
	* @static
	*/
	public static function parseRulePattern( $oPattern, $bPrivate = false )
	{
		//	Seed our rules array...
		$_arRules = self::defaultRuleSet( $bPrivate );

		if ( null !== $oPattern )
		{
			//	Split up pattern (type{:defaultValue{:externalName{:isRequired{:allowedValue(s)}}}})
			$_arPattern = ( ! is_array( $oPattern ) ) ? explode( ':', $oPattern ) : $oPattern;

			//	Type is required, but we can default to string.
			$_arRules[ CPSOption::RPT_TYPE ] = CPSHelperBase::o( $_arPattern, 0, CPSHelperBase::o( $_arRules, CPSOption::RPT_TYPE, 'string' ) );

			//	Fix up types...
			switch ( $_arRules[ CPSOption::RPT_TYPE ] )
			{
				case 'int':
					$_arRules[ CPSOption::RPT_TYPE ] = 'integer';
					break;

				case 'bool':
					$_arRules[ CPSOption::RPT_TYPE ] = 'boolean';
					break;
			}

			//	Default value (default default value is null)
			if ( $_arRules[ CPSOption::RPT_DEFAULT ] = CPSHelperBase::o( $_arPattern, 1, CPSHelperBase::o( $_arRules, CPSOption::RPT_DEFAULT ) ) )
			{
				//	Evalute default if we have one...
				if ( $_arRules[ CPSOption::RPT_TYPE ] !== 'string' && ! empty( $_arRules[ CPSOption::RPT_DEFAULT ] ) )
					$_arRules[ CPSOption::RPT_DEFAULT ] = eval( 'return ' . $_arRules[ CPSOption::RPT_DEFAULT ] . ';' );
			}

			//	Different name for external use?
			$_arRules[ CPSOption::RPT_EXTERNAL_NAME ] = CPSHelperBase::o( $_arPattern, 2, CPSHelperBase::o( $_arRules, CPSOption::RPT_EXTERNAL_NAME ) );

			//	Required value? Must eval to bool(true)
			$_arRules[ CPSOption::RPT_REQUIRED ] = ( bool )( eval( 'return ' . CPSHelperBase::o( $_arPattern, 3, CPSHelperBase::o( $_arRules, CPSOption::RPT_REQUIRED, false ) ) . ';' ) === true );

			//	And finally the allowed values. If string contains '|', will be transformed to an array
			$_arRules[ CPSOption::RPT_ALLOWED ] = ( is_string( $_oAllowed = CPSHelperBase::o( $_arPattern, 4, CPSHelperBase::o( $_arRules, CPSOption::RPT_ALLOWED ) ) ) && false !== strpos( $_oAllowed, '|' ) ) ? explode( '|', $_oAllowed ) : $_oAllowed;

			//	Clean rules
			foreach ( $_arRules as $_sKey => $_oValue ) if ( null === $_arRules[ $_sKey ] ) unset( $_arRules[ $_sKey ] );
		}

		//	and return...
		return $_arRules;
	}

	/**
	 * This is the default rule set for an option
	 */
	public static function defaultRuleSet( $bPrivate = false )
	{
		return array(
			CPSOption::RPT_TYPE => 'string',
			CPSOption::RPT_DEFAULT => null,
			CPSOption::RPT_EXTERNAL_NAME => null,
			CPSOption::RPT_REQUIRED => false,
			CPSOption::RPT_ALLOWED => null,
			CPSOption::RPT_PRIVATE => $bPrivate,
		);
	}

	/***
	 * Validates all options in a collection
	 * @param ICPSOptionContainer $oContainer
	 */
	public static function validateOptions( IPSOptionContainer &$oContainer )
	{
		//	Get the option collection and validate
		foreach ( $oContainer as $_oOption )
			self::validateOption( $_oOption );
	}

	/**
	 * Validates an option value against its rules. If $oValue is null, stored value is used.
	 * @param CPSOption $oOption
	 * @param mixed $oValue Optional value to use for validation purposes
	 * @throws CPSOptionException
	 */
	public static function validateOption( CPSOption &$oOption, $oValue = null )
	{
		//	We all start innocent
		$_bPassed = true;

		//	Get the value to test
		$_oValue = CPSHelperBase::nvl( $oValue, $oOption->getValue( true ) );

		//	Check required values...
		if ( ! $_bPassed = ( $oOption->getIsRequired() && null === $_oValue ) )
			throw new CPSOptionException( "Option \"{$oOption->getName()}\" is required." );

		//	Fix up types...
		$_arType = $oOption->getOptionType();
		if ( ! is_array( $_arType ) ) $_arType = array( $_arType );

		//	Allowed type?
		foreach ( $_arType as $_sKey => $_sType )
		{
			//	Check type...
			switch ( $_sType )
			{
				case 'bool':
				case 'boolean':
					$_bPassed = is_bool( $_oValue );
					$_sType = 'boolean';
					break;

				case 'int':
				case 'integer':
					$_bPassed = is_int( $_oValue );
					$_sType = 'integer';
					break;

				case 'float':
					$_bPassed = is_float( $_oValue );
					break;

				case 'double':
					$_bPassed = is_double( $_oValue );
					break;

				case 'array':
					$_bPassed = is_array( $_oValue );
					break;

				case 'object':
					$_bPassed = is_object( $_oValue ) || $_oValue === null;
					break;

				case 'string':
					$_bPassed = is_string( $_oValue ) || $_oValue === null;
					break;

				default:
					//	If we get here, we have unknown or user-defined type. If not null, test it.
					if ( null !== $_sType && 'NULL' !== $_sType ) $_bPassed = ( $_oValue instanceof $_sType );
					break;
			}

			if ( ! $oOption->isValidType( $_sType ) )
				throw new CPSOptionException( Yii::t( __CLASS__, 'Invalid type "{y}" specified for "{x}"', array( '{y}' => $_sType, '{x}' => $_sKey ) ), 1  );

			if ( ! $_bPassed )
			{
				$_sValType = gettype( $_oValue );
				throw new CPSOptionException( "Option \"{$oOption->getName()}\" expects values to be of type \"{$_sType}\". \"{$_sValType}\" given." );
			}

			$oOption->setRule( CPSOption::RPT_TYPE, $_sType );
		}
	}

	/***
	 * Makes an array of key=>value pairs in an array.
	 *
	 * @param array $arOptions The options to use as a source
	 * @param integer $iFormat
	 * @param boolean $bNoCheck
	 * @return mixed
	 */
	public static function makeOptions( IPSOptionContainer $oContainer, $bPublicOnly = true, $iFormat = CPSHelperBase::OF_JSON, $bNoCheck = false )
	{
		//	Check them first...
		if ( ! $bNoCheck ) self::validateOptions( $oContainer );

		//	Get our public callbacks...
		$_arCallbacks = $oContainer->getValue( 'callbacks', array() );

		//	Remove it
		$oContainer->unsetOption( 'callbacks' );

		//	Our output array
		$_arOut = array();
		$_sEncodedOptions = null;

		//	Tag callbacks for special processing afterwards
		foreach ( $_arCallbacks as $_sKey => $_oValue )
		{
			if ( ! empty( $_oValue ) )
				$_arOut[ "__pscb_{$_sKey}" ] = $_sKey;
		}

		//	Now build our final array...
		$_arRaw = $oContainer->getRawOptions( true );

		foreach( $_arRaw as $_sKey => $_oOption )
		{
			//	Skip nulls...
			$_oValue = $_oOption->getValue();

			if ( ! empty( $_oValue ) || '0' === $_oValue )
			{
				$_sExtName = $_oOption->getExternalName();

				//	Check for callbacks in the inner array (.i.e. "buttons" from jqUI dialog)
				if ( is_array( $_oValue ) )
				{
					foreach ( $_oValue as $_sSubKey => $_oSubValue )
					{
						if ( self::isScriptCallback( $_oSubValue ) )
						{
							$_arCallbacks[ $_sSubKey ] = $_oSubValue;
							$_arOut[ $_sKey ][ "__pscb_{$_sSubKey}" ] = $_sSubKey;
						}
						else
						{
							if ( ! isset( $_arOut[ $_sExtName ] ) || ! is_array( $_arOut[ $_sExtName ] ) ) $_arOut[ $_sExtName ] = array();
							$_arOut[ $_sExtName ][ $_sSubKey ] = $_oSubValue;
						}
					}
				}
				else
				{
					if ( self::isScriptCallback( $_oValue ) )
					{
						$_arCallbacks[ $_sKey ] = $_oValue;
						$_arOut[ "__pscb_{$_sKey}" ] = $_sKey;
					}
					else
						$_arOut[ $_sExtName ] = $_oValue;
				}
			}
		}

		if ( count( $_arOut ) )
		{
			switch ( $iFormat )
			{
				case CPSHelperBase::OF_JSON:
					$_sEncodedOptions = CJSON::encode( $_arOut );
					break;

				case CPSHelperBase::OF_HTTP:
					foreach ( $_arOut as $_sKey => $_sValue )
					{
						if ( ! empty( $_sValue ) )
							$_sEncodedOptions .= '&' . $_sKey . '=' . urlencode( $_sValue );
					}
					break;

				case CPSHelperBase::OF_ASSOC_ARRAY:
//					if ( ! empty( $_arCallbacks ) )
//						throw new CPSOptionException( 'Cannot use type "ASSOC_ARRAY" when callbacks are present.' );

					$_sEncodedOptions = $_arOut;
					break;
			}

			//	Fix up the callbacks and return...
			return self::fixCallbacks( $_sEncodedOptions, $_arCallbacks );
		}

		//	Nada
		return null;
	}

	/**
	* Checks a single option against its pattern.
	*
	* @param string $sKey
	* @param mixed $oValue
	* @throws CException
	* @return bool
	*/
	public static function checkOption( CPSOption $oOption )
	{
		//	Required and missing? Bail
		if ( null === ( $_oValue = $oOption->getValue() ) && $oOption->isRequired )
			throw new CPSOptionException( Yii::t( __CLASS__, '"{x}" is a required option', array( '{x}' => $oOption->name ) ), 1 );

		//	Check if this is a valid value for this option
		if ( null !== ( $_arValid = $oOption->getAllowed() ) )
		{
			if ( null !== $oValue && ! in_array( $_oValue, $_arValid ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" must be one of: "{y}"', array( '{x}' => $sKey, '{y}' => implode( ', ', $_arValid ) ) ), 1  );
		}

		//	Looks clean....
		return true;
	}

	/**
	* Checks the options array or a passed in array for validity checking...
	*
	* @param array $arOptions
	* @throws CException
	* @return bool
	*/
	public static function checkOptions( IPSOptionContainer $oContainer )
	{
		//	One at a time...
		foreach ( $oContainer->getRawOptions() as $_oOption )
			self::checkOption( $_oOption );

		//	We made it here? We cool baby!
		return true;
	}

  /**
    * Checks the callback array to see if they're valid.
    *
    * @throws CException
    * @return true If all is good.

    */
	public static function checkCallbacks( IPSComponent $oComponent )
	{
		$_arCallbacks = $oComponent->callbacks;
		$_arValidCallbacks = $oComponent->validCallbacks;

		if ( ! empty( $_arCallbacks ) && ! empty( $_arValidCallbacks ) )
		{
			foreach ( $_arCallbacks as $_sKey => $_oValue )
			{
				if ( ! in_array( $_sKey, $_arValidCallbacks ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" must be one of: {y}', array( '{x}' => $_sKey, '{y}' => implode( ', ', $_arValidCallbacks ) ) ) );
			}
		}

		//	Clean...
		return true;
	}

	/**
	 * Fix up the callbacks
	 * @param mixed $oValue
	 * @param mixed $arCallbacks
	 */
	protected static function fixCallbacks( $sOutput, $arCallbacks )
	{
		$_sOut = $sOutput;

		foreach ( $arCallbacks as $_sKey => $_oValue )
		{
			$_sQuote = '"';

			//	Indicator to quote key...
			if ( '!!!' == substr( $_sKey, 0, 3 ) )
			{
				$_sQuote = '\'';
				$_sKey = substr( $_sKey, 3 );
			}

			$_sSearch = '"' . self::CB_KEY . $_sKey . '":"' . $_sKey . '"';

			if ( self::isScriptCallback( $_oValue ) )
				$_sReplace = $_sQuote . $_sKey . $_sQuote . ' : ' . $_oValue;
			else
				$_sReplace = $_sKey . ' : \'' . $_oValue . '\'';

			$_sOut = str_replace( $_sSearch, $_sReplace, $_sOut );
		}

		return $_sOut;
	}

}