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
 * @version 	SVN $Id: ThemeRoller.php 367 2010-01-16 04:29:24Z jerryablan@gmail.com $
 * @since 		v1.0.6
 * 
 * @filesource
 */
class ThemeRoller extends CPSPortlet
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
	
}
