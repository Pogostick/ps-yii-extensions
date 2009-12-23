<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Provides some common utility methods for owner objects
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
class CPSUtilityBehavior extends CBehavior implements IPogostick
{
	//********************************************************************************
	//* Private Members
	//********************************************************************************
	
	/**
	* Cache the client script object for speed
	* @var CClientScript
	* @access protected
	* @see _cs
	*/
	protected static $m_oClientScript = null;

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
	* @access public
	* @static
	*/
	public static function nvl()
	{
		//	Pass it on
		return call_user_func_array( 'PS::nvl', func_get_args() );
	}
	
	/**
	* Returns an analog to Java System.currentTimeMillis()
	* 
	* @returns integer
	*/
	public static function currentTimeMillis()
	{
		return CPSHelp::currentTimeMillis();
	}
	
	/**
	* Shorthand version of getOption. Retrieves a value from an array. 
	* $oDefault is set and returned if $sKey is not 'set'. Optionally will unset option in array.
	*
	* @param array $arOptions
	* @param string|integer $oKey The index for the array. Can be a string or an integer.
	* @param mixed $oDefault
	* @param boolean $bUnset
	* @returns mixed
	* @access public
	* @static
	*/
	public static function o( &$arOptions, $oKey, $oDefault = null, $bUnset = false )
	{
		return PS::o( $arOptions, $oKey, $oDefault, $bUnset );
	}

	/**
	* Sets an option in the given array
	*
	* @param array $arOptions
	* @param string|integer $oKey The index for the array. Can be a string or an integer.
	* @param mixed $oValue
	* @returns mixed The new value of the key
	* @static
	*/
	public static function setOption( array $arOptions, $oKey, $oValue = null )
	{
		return CPSHelp::setOption( $arOptions, $oKey, $oDefault, $bUnset );
	}

	/**
	* Unsets an option in the given array
	*
	* @param array $arOptions
	* @param string|integer $oKey The index for the array. Can be a string or an integer.
	* @returns mixed The new value of the key
	* @static
	*/
	public static function unsetOption( array $arOptions, $oKey )
	{
		return CPSHelp::unsetOption( $arOptions, $oKey, null );
	}
	
	//********************************************************************************
	//* Yii Convenience Mappings
	//********************************************************************************
	
	/**
	* Returns the current clientScript object. Caches for next calls...
	* @returns CClientScript
	* @access public
	* @static
	*/
	public static function _cs() 
	{ 
		return PS::nvl( self::$m_oClientScript, self::$m_oClientScript = Yii::app()->getClientScript() );
	}

	/**
	* Registers a CSS file
	* 
	* @param string URL of the CSS file
	* @param string media that the CSS file should be applied to. If empty, it means all media types.
	*/
	public static function _rcf( $sUrl, $sMedia = '' )
	{
		self::_cs()->registerCssFile( $sUrl, $sMedia );
	}

	/**
	* Registers a piece of CSS code.
	* 
	* @param string ID that uniquely identifies this piece of CSS code
	* @param string the CSS code
	* @param string media that the CSS code should be applied to. If empty, it means all media types.
	*/
	public static function _rc( $sId, $sCss, $sMedia = '' )
	{
		self::_cs()->registerCss( $sId, $sCss, $sMedia );
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
	public static function _rsf( $sUrl, $ePosition = CClientScript::POS_HEAD )
	{
		self::_cs()->registerScriptFile( $sUrl, $ePosition );
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
	public static function _rs( $sId, $sScript, $ePosition = CClientScript::POS_READY )
	{
		self::_cs()->registerScript( $sId, $sScript, $ePosition );
	}

	/**
	* Registers a meta tag that will be inserted in the head section (right before the title element) of the resulting page.
	* 
	* @param string content attribute of the meta tag
	* @param string name attribute of the meta tag. If null, the attribute will not be generated
	* @param string http-equiv attribute of the meta tag. If null, the attribute will not be generated
	* @param array other options in name-value pairs (e.g. 'scheme', 'lang')
	*/
	public static function _rmt( $sContent, $sName = null, $sHttpEquiv = null, $arOptions = array() )
	{
		self::_cs()->registerMetaTag( $sContent, $sName, $sHttpEquiv, $arOptions );
	}

}