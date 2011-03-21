<?php
/**
 * This file is part of psYiiExtensions package
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
/**
 * This is a jQuery UI Theme Roller portlet for your use. 
 * Theme changes are handled by CPSController in conjunction with this portlet.
 * 
 * @package 	psYiiExtensions
 * @subpackage 	widgets
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id: CPSThemeRoller.php 367 2010-01-16 04:29:24Z jerryablan@gmail.com $
 * @since 		v1.0.6
 * 
 * @filesource
 */
class CPSThemeRoller extends CPSPortlet
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * The action to which to post...
	 */
	const	POST_ACTION	= '__themeChangeRequest';
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Initialize
	* 
	*/
	public function preinit()
	{
		parent::preinit();
		$this->title = 'Choose Your Theme';
	}
	
	/**
	 * Initialize and register with our controller...
	 */
	public function init()
	{
		parent::init();
		
		if ( ( $_oController = Yii::app()->getController() ) instanceof CPSController )
			$_oController->addCommandToMap( str_replace( 'CPS', 'ps', get_class( $this ) ), array( $this, 'handleThemeChangeRequest' ) );
	}
	
	/**
	* Renders the content of this portlet.
	* If object is set to auto-render, the view with the same name as this portlet will be rendered when called
	* @return string
	*/
	protected function renderContent()
	{
		if ( $this->autoRender ) $this->render( 'themeRoller' );
	}
	
	/**
	 * User has requested a new theme
	 * @return bool
	 */
	public function handleThemeChangeRequest()
	{
		$_sTheme = PS::o( $_REQUEST, '__theme' );
		$_sUri = PS::o( $_REQUEST, '__uri' );
		
		$_arThemes = CPSjqUIWrapper::getValidThemes();
		$_sTheme = PS::o( $_arThemes, $_sTheme, CPSjqUIWrapper::getCurrentTheme() );
		
		if ( $_sTheme && $_sUri && in_array( $_sTheme, $_arThemes ) )
		{
			Yii::app()->user->setState( CPSjqUIWrapper::getStateName(), $_sTheme );
			$this->redirect( $_sUri );
		}

		return true;
	}
}