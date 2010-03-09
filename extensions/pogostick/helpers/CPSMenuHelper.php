<?php
/*
 * This file is part of psYiiExtensions package
 * 
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
/**
 * @package 	psYiiextenions
 * @subpackage 	helpers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * 
 * @filesource
 */
class CPSMenuHelper extends CPSHelperBase
{

	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	* The class to apply to current menu items.
	* @staticvar string
	*/
	protected static $m_sCurrentClass = 'ps-menu-current';
	
	/**
	* The current index into the menu array
	* @staticvar integer
	*/
	protected static $m_iCurrentSection = 0;
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	 * Retrieves the class that marks a menu item as current. Defaults to ps-menu-current.
	 * @returns string
	 * @static
	 */
	public static function getCurrentClass() { return self::$m_sCurrentClass; }
	
	/**
	 * Sets the class that marks a menu item as current.
	 * @returns string
	 * @static
	 */
	public static function setCurrentClass( $sValue ) { self::$m_sCurrentClass = $sValue; }
	
	/**
	 * Retrieves the current index into the menu
	 * @returns integer
	 */
	public static function getCurrentSection() { return self::$m_iCurrentSection; }
	
	/**
	 * Builds a single menu block for use with an accordion or some such device.
	 * 
	 * options:
	 * 
	 * 	headerTag		The HTML tag for the header element. Defaults to H3
	 *	headerOptions	Any HTML options for the headerTag
	 * 	<linkName>		<linkUrl> or array( 'url' => <url>, other options for link tag )
	 * 
	 * @param string $sTitle
	 * @param array $arItems
	 * @returns string
	 */
	public static function buildMenuBlock( $sTitle, $arOptions = array() )
	{
		$_sOut = null;
		
		//	Make the header
		$_sHeader = PS::tag( 
			PS::o( $arOptions, 'headerTag', 'h3', true ), 
			PS::o( $arOptions, 'headerOptions', array(), true ),
			PS::link( $sTitle )
		);
		
		//	build the links
		foreach ( $arOptions as $_sLabel => $_arLink )
		{
			if ( is_array( $_arLink ) )
				$_sUrl = PS::o( $_arLink, 'url', null, true );
			else
			{
				$_sUrl = $_arLink;
				$_arLink = array();
			}
				
			$_sOut .= self::getMenuLink( $_sUrl, $_sLabel, $_arLink );
		}
		
		//	And return...
		return PS::tag( 'div', array(), $_sHeader . PS::tag( 'ul', array(), $_sOut ) );
	}	

	/**
	 * Builds a single menu block for use with an accordion or some such device.
	 * 
	 * options:
	 * 
	 * 	headerTag		The HTML tag for the header element. Defaults to H3
	 *	headerOptions	Any HTML options for the headerTag
	 * 	<linkName>		<linkUrl> or array( 'url' => <url>, other options for link tag )
	 * 
	 * @param string $sTitle
	 * @param array $arItems
	 * @returns string
	 */
	public static function buildMenuFromArray( $sIndex, $arMenu = array() )
	{
		if ( $_arMenuBlock = PS::o( $arMenu, $sIndex ) )
		{
			$_sTitle = PS::o( $_arMenuBlock, 'title' );
			$_arItems = PS::o( $_arMenuBlock, 'items' );
			
			//	If a non-array is passed in, assume it's to be eval'd...
			if ( ! is_array( $_arItems ) && ! empty( $_arItems ) )
				$_arItems = eval( 'return ' . $_arItems . ';' );

			return self::buildMenuBlock( $_sTitle, $_arItems );
		}
		
		return null;
	}	

	/**
	 * Get's a single formatted menu link
	 * 
	 * @param string $sUrl
	 * @param string $sTitle
	 * @param array $arOptions
	 * @return string
	 */
	public static function getMenuLink( $sUrl, $sTitle, $iActive = 0, $arOptions = array() )
	{
		static $_sCurrent = null;
		$_sOut = null;
		
		$_sClass = PS::o( $arOptions, 'class', null, true );
		$_sCurrentClass = PS::o( $arOptions, 'class', self::$m_sCurrentClass, true );
			
		if ( ! $_sCurrent ) $_sCurrent = Yii::app()->getRequest()->getRequestUri();

		if ( $sUrl == $_sCurrent || false !== stripos( $_sCurrent, $sUrl ) ) 
		{
			$_sClass .= ' ' . self::$m_sCurrentClass;
			self::$m_iCurrentSection = $iActive;
		}

		$arOptions['class'] = trim( $_sClass );
				
		return PS::tag( 'li', array(), PS::link( $sTitle, $sUrl, $arOptions ) );
	}

}