<?php
/*
 * This file is part of psYiiExtensions blog example
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
/**
 * @package 	psYiiExtensions.examples.blog
 * @subpackage 	components
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 */
class UserLogin extends CPSPortlet
{
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
		
		$this->title = 'Login';
		$this->autoRender = false;
	}
	
	/**
	* Render our content
	*/
	protected function renderContent()
	{
		$_oForm = new LoginForm();
		
		if ( isset( $_POST, $_POST['LoginForm'] ) )
		{
			$_oForm->attributes = $_POST['LoginForm'];
			
			if ( $_oForm->validate() )
				$this->getController()->refresh();
		}
		
		//	Display the login form
		$this->render( 'userLogin', array( 'form' => $_oForm ) );
	}
 
}