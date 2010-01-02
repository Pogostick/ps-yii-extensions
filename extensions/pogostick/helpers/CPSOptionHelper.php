<?php
/*
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
 * @version 	SVN $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 */
class CPSOptionHelper implements IPSBase
{
	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Check to see if the value follows a callback function pattern
	* @param string $sValue
	*/
	public static function isScriptCallback( $sValue )
	{
		return is_string( $sValue ) && ( 0 == strncasecmp( $sValue, 'function(', 9 ) || 0 == strncasecmp( $sValue, 'jQuery(', 7 ) || 0 == strncasecmp( $sValue, '$(', 2 ) );
	}
	
	/**
	* Parses a rule pattern string
	* 
	* @param string $sKey The key
	* @param mixed $oPattern The pattern string or array
	* @param boolean $bPrivate Forces this option to be private
	* @returns array
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
			$_arRules[ CPSOption::RPT_TYPE ] = PS::o( $_arPattern, 0, PS::o( $_arRules, CPSOption::RPT_TYPE, 'string' ) );
			
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
			if ( $_arRules[ CPSOption::RPT_DEFAULT ] = PS::o( $_arPattern, 1, PS::o( $_arRules, CPSOption::RPT_DEFAULT ) ) )
			{
				//	Evalute default if we have one...
				if ( $_arRules[ CPSOption::RPT_TYPE ] !== 'string' && ! empty( $_arRules[ CPSOption::RPT_DEFAULT ] ) )
					$_arRules[ CPSOption::RPT_DEFAULT ] = eval( 'return ' . $_arRules[ CPSOption::RPT_DEFAULT ] . ';' );
			}

			//	Different name for external use?
			$_arRules[ CPSOption::RPT_EXTERNAL_NAME ] = PS::o( $_arPattern, 2, PS::o( $_arRules, CPSOption::RPT_EXTERNAL_NAME ) );
			
			//	Required value? Must eval to bool(true)
			$_arRules[ CPSOption::RPT_REQUIRED ] = ( bool )( eval( 'return ' . PS::o( $_arPattern, 3, PS::o( $_arRules, CPSOption::RPT_REQUIRED, false ) ) . ';' ) === true );
			
			//	And finally the allowed values. If string contains '|', will be transformed to an array
			$_arRules[ CPSOption::RPT_ALLOWED ] = ( is_string( $_oAllowed = PS::o( $_arPattern, 4, PS::o( $_arRules, CPSOption::RPT_ALLOWED ) ) ) && false !== strpos( $_oAllowed, '|' ) ) ? explode( '|', $_oAllowed ) : $_oAllowed;

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
	public static function validateOptions( IPSOptionContainer $oContainer )
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
	public static function validateOption( CPSOption $oOption, $oValue = null )
	{
		//	We all start innocent
		$_bPassed = true;
		
		//	Get the value to test
		$_oValue = PS::nvl( $oValue, $oOption->getValue() );
		
		//	Check required values...
		if ( ! $_bPassed = ( $oOption->getIsRequired() && null === $_oValue ) )
			throw new CPSOptionException( "Option \"{$oOption->getName()}\" is required." );
		
		//	Check type...
		if ( $_bPassed && null !== ( $_sType = $oOption->getOptionType() ) )
		{
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
					$_bPassed = is_object( $_oValue );
					break;
					
				case 'string':
					$_bPassed = is_string( $_oValue ) || $_oValue === null;
					break;
					
				default:
					//	If we get here, we have unknown or user-defined type. If not null, test it.
					if ( null !== $_sType && 'NULL' !== $_sType ) $_bPassed = ( $_oValue instanceof $_sType );
					break;
			}
			
			if ( ! $_bPassed )
			{
				$_sValType = gettype( $_oValue );
				throw new CPSOptionException( "Option \"{$oOption->getName()}\" expects values to be of type \"{$_sType}\". \"{$_sValType}\" given." );
			}
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
	public static function makeOptions( IPSOptionContainer $oContainer, $bPublicOnly = true, $iFormat = PS::OF_JSON, $bNoCheck = false )
	{
		//	Check them first...
		if ( ! $bNoCheck ) self::validateOptions( $oContainer );

		//	Get our public callbacks...
		$_arCallbacks = $oContainer->getOption( 'callbacks', array() );

		//	Our output array
		$_arOut = array();
		$_sEncodedOptions = null;

		//	Now build our final array...
		foreach( $oContainer as $_sKey => $_oOption )
		{
			//	Private option? Skip if public-only
			if ( $_sKey == 'callbacks' || ( $bPublicOnly && $_oOption->getIsPrivate() ) )
				continue;
				
			//	Skip nulls...
			if ( $_oOption->__isset( $_sKey ) )
			{
				$_oValue = $_oOption->getValue();
				$_sExtName = $_oOption->getExternalName();
					
				//	Check for callbacks in the inner array (.i.e. "buttons" from jqUI dialog)
				if ( 'array' == $_oOption->getOptionType() )
				{
					foreach ( $_oValue as $_sSubKey => $_oSubValue )
					{
						if ( ! is_array( $_oSubValue ) && self::isScriptCallback( $_oSubValue ) )
						{
							$_arCallbacks[ $_sSubKey ] = $_sSubValue;
							unset( $_oValue[ $_sSubKey ] );
						}
					}
				}
					
				$_arOut[ $_sExtName ] = $_oValue;
			}
		}
		
		if ( count( $_arOut ) )
		{
			switch ( $iFormat )
			{
				case PS::OF_JSON:
					$_sEncodedOptions = json_encode( $_arOut );
					break;
					
				case PS::OF_HTTP:
					foreach ( $_arOut as $_sKey => $_sValue )
					{
						if ( ! empty( $_sValue ) ) 
							$_sEncodedOptions .= '&' . $_sKey . '=' . urlencode( $_sValue );
					}
					break;
					
				case PS::OF_ASSOC_ARRAY:
					if ( ! empty( $_arCallbacks ) ) 
						throw new CPSOptionException( 'Cannot use type "ASSOC_ARRAY" when callbacks are present.' );
						
					$_sEncodedOptions = $_arOut;
					break;
			}

			//	Fix up the callbacks...
			foreach ( $_arCallbacks as $_sKey => $_oValue )
			{
				$_sQuote = null;

				//	Indicator to quote key...
				if ( '!!!' == substr( $_sKey, 0, 3 ) )
				{
					$_sQuote = '\'';
					$_sKey = substr( $_sKey, 3 );
				}

				if ( ! empty( $_oValue ) )
				{
					if ( self::isScriptCallback( $_oValue ) )
						$_sEncodedOptions = str_replace( "\"cb_{$_sKey}\":\"{$_sKey}\"", "{$_sQuote}{$_sKey}{$_sQuote}:{$_oValue}", $_sEncodedOptions );
					else
						$_sEncodedOptions = str_replace( "\"cb_{$_sKey}\":\"{$_sKey}\"", "{$_sKey}:'{$_oValue}'", $_sEncodedOptions );
				}
			}

			return $_sEncodedOptions;
		}

		//	Nada
		return null;
	}
}