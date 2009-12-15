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
	//* Private Members
	//********************************************************************************
	
	/**
	* Cache the client script object for speed
	* 
	* @var CClientScript
	*/
	protected static $m_oClientScript = null;
	public static function getClientScript() { return self::nvl( self::$m_oClientScript, self::$m_oClientScript = Yii::app()->getClientScript() ); }

	//********************************************************************************
	//* Private Constructor
	//********************************************************************************

	/**
	* Disallow construction
	*/
	private function __constructor()
	{
		throw new Exception( 'This class cannot be directly instantiated.', 500 );
	}
		
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
	* Returns value (or current date) formatted
	* 
	* @param mixed $sDate
	* @param string $sFormat
	* @return string
	*/
	public static function asDate( $sDate = null, $sFormat = 'Y-m-d' )
	{
		return date( $sFormat, $sDate ? strtotime( $sDate ) : time() );
	}
	
	/**
	* Returns value (or current date/time) formatted
	* 
	* @param mixed $sDate
	* @param string $sFormat
	* @return string
	*/
	public static function asDateTime( $sDate = null, $sFormat = 'Y-m-d H:i:s' )
	{
		return date( $sFormat, $sDate ? strtotime( $sDate ) : time() );
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
	
	//********************************************************************************
	//* Yii Convenience Mappings
	//********************************************************************************
	
	/**
	* Registers a CSS file
	* 
	* @param string URL of the CSS file
	* @param string media that the CSS file should be applied to. If empty, it means all media types.
	*/
	public static function registerCssFile( $sUrl, $sMedia = '' )
	{
		self::getClientScript()->registerCssFile( $sUrl, $sMedia );
	}

	/**
	* Registers a piece of CSS code.
	* 
	* @param string ID that uniquely identifies this piece of CSS code
	* @param string the CSS code
	* @param string media that the CSS code should be applied to. If empty, it means all media types.
	*/
	public static function registerCss( $sId, $sCss, $sMedia = '' )
	{
		self::getClientScript()->registerCss( $sId, $sCss, $sMedia );
	}

	/**
	* Registers a javascript file.
	* 
	* @param string URL of the javascript file
	* @param integer the position of the JavaScript code. Valid values include the following:
	* <ul>
	* <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	* <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	* <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	* </ul>
	*/
	public static function registerScriptFile( $sUrl, $ePosition = self::POS_HEAD )
	{
		self::getClientScript()->registerScriptFile( $sUrl, $ePosition );
	}

	/**
	* Registers a piece of javascript code.
	* 
	* @param string ID that uniquely identifies this piece of JavaScript code
	* @param string the javascript code
	* @param integer the position of the JavaScript code. Valid values include the following:
	* <ul>
	* <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	* <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	* <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	* <li>CClientScript::POS_LOAD : the script is inserted in the window.onload() function.</li>
	* <li>CClientScript::POS_READY : the script is inserted in the jQuery's ready function.</li>
	* </ul>
	*/
	public static function registerScript( $sId, $sScript, $ePosition = CClientScript::POS_READY )
	{
		self::getClientScript()->registerScript( $sId, $sScript, $ePosition );
	}

	/**
	* Registers a meta tag that will be inserted in the head section (right before the title element) of the resulting page.
	* 
	* @param string content attribute of the meta tag
	* @param string name attribute of the meta tag. If null, the attribute will not be generated
	* @param string http-equiv attribute of the meta tag. If null, the attribute will not be generated
	* @param array other options in name-value pairs (e.g. 'scheme', 'lang')
	*/
	public static function registerMetaTag( $sContent, $sName = null, $sHttpEquiv = null, $arOptions = array() )
	{
		self::getClientScript()->registerMetaTag( $sContent, $sName, $sHttpEquiv, $arOptions );
	}

}