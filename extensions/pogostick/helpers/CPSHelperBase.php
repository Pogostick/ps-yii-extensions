<?php
/**
 * CPSHelperBase class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtension
 * @subpackage helpers
 * @since v1.0.6
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
/**
 * Base functionality that I want in ALL my helper classes
*/
class CPSHelperBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* If value is not set or empty, last passed in argument is returned
	* 
	* Allows for multiple nvl chains ( nvl(x,y,z,null) )
	* 
	* @param mixed 
	* @returns mixed
	*/
	public static function nvl()
	{
		$_oDefault = null;
		$_iArgs = func_num_args();
		$_arArgs = func_get_args();
		
		for ( $_i = 0; $_i < $_iArgs; $_i++ )
		{
			if ( isset( $_arArgs[ $_i ] ) && ! empty( $_arArgs[ $_i ] ) )
				return $_arArgs[ $_i ];
				
			$_oDefault = $_arArgs[ $_i ];
		}

		return $_oDefault;
	}
	
	/**
	* Returns an analog to Java System.currentTimeMillis()
	* 
	* @returns integer
	*/
	public static function currentTimeMillis()
	{
		list( $_uSec, $_sec ) = explode( ' ', microtime() );
		return ( ( float )$_uSec + ( float )$_sec );
	}
	
	/**
	* Retrieves an option from the given array. 
	* 
	* $oDefault is set and returned if $sKey is not 'set'. Optionally will unset option in array.
	*
	* @access public
	* @static
	* @param array $arOptions
	* @param string $sKey
	* @param mixed $oDefault
	* @param boolean $bUnset
	* @returns mixed
	*/
	public static function getOption( &$arOptions, $sKey, $oDefault = null, $bUnset = false )
	{
		$_oValue = $oDefault;
		
		if ( is_array( $arOptions ) )
		{
			if ( ! array_key_exists( $sKey, $arOptions ) )
			{
				//	Ignore case and look...
			    $_sNewKey = strtolower( $sKey );
			    foreach ( $arOptions as $_sKey => $_sValue )
			    {
		    		if ( strtolower( $_sKey ) == $_sNewKey )
		    		{
		    			//	Set correct key and break
		    			$sKey = $_sKey;
		    			break;
					}
				}
	        }
			
			if ( isset( $arOptions[ $sKey ] ) )
			{
				$_oValue = $arOptions[ $sKey ];
				if ( $bUnset ) unset( $arOptions[ $sKey ] );
			}
			
			//	Set it in the array if not an unsetter...
			if ( ! $bUnset ) $arOptions[ $sKey ] = $_oValue;
		}

		//	Return...
		return $_oValue;
	}

	/**
	* Sets an option in the given array
	*
	* @param array $arOptions
	* @param string $sKey
	* @param mixed $oValue
	* @returns mixed The new value of the key
	* @static
	*/
	public static function setOption( array $arOptions, $sKey, $oValue = null )
	{
		return $arOptions[ $sKey ] = $oValue;
	}

	/**
	* Unsets an option in the given array
	*
	* @param array $arOptions
	* @param string $sKey
	* @returns mixed The new value of the key
	* @static
	*/
	public static function unsetOption( array $arOptions, $sKey )
	{
		return self::setOption( $arOptions, $sKey, null );
	}
	
	/**
	* Returns the number of "interval" between the two dates
	* 
	* @param string $dtStart
	* @param string $dtEnd
	* @param int $sInterval
	*/
	public static function dateDiff( $dtStart, $dtEnd )
	{
		$_dtStart = new DateTime( $dtStart );
		$_dtEnd = new DateTime( $dtEnd );
		return $_dtEnd->diff( $_dtStart );
	}
	
	/**
	* Merges an array without overwriting...
	* @returns array
	*/
	public static function smart_array_merge()
	{
		$_iCount = func_num_args();
		$_arResult = array();
		
		for ( $_i = 0; $_i < $_iCount; $_i++ )
		{
			foreach ( func_get_arg( $_i ) as $_sKey => $_oValue )
			{
				if ( isset( $_arResult[ $_sKey ] ) )  $_oValue = $_arResult[ $_sKey ] . ' ' . $_oValue;
				$_arResult[ $_sKey ] = $_oValue;
			}
		}
		
		return $_arResult;
	}

}