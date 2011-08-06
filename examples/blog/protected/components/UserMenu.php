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
 * @version 	SVN $Id: UserMenu.php 362 2010-01-03 05:34:35Z jerryablan@gmail.com $
 * @since 		v1.0.6
 * 
 * @filesource
 */
class UserMenu extends CPSPortlet
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Initialize
	* @param CBaseController $oOwner
	*/
	public function __construct( $oOwner = null )
	{
		parent::__construct( $oOwner );
		
		//	Proceed
		$this->title = PS::encode( Yii::app()->user->name );
	}

	/***
	* Initialize
	*/
	public function init()
	{
		parent::init();
		
		if ( PS::o( $_POST, 'command' ) == 'logout' )
		{
			Yii::app()->user->logout();
			Yii::app()->controller->redirect( Yii::app()->homeUrl );
		}
	}

}