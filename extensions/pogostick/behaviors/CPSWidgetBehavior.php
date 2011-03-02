<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSWidgetBehavior provides convenient access to typical "widget" behaviors
 * 
 * @package 	psYiiExtensions
 * @subpackage 	behaviors
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSWidgetBehavior.php 353 2010-01-02 19:43:58Z jerryablan@gmail.com $
 * @since 		v1.0.4
 * 
 * @filesource
 */
class CPSWidgetBehavior extends CPSComponentBehavior
{
	//********************************************************************************
	//* Public methods
	//********************************************************************************

	public function preinit()
	{
		//	Call daddy
		parent::preinit();

		//	Add our options...
		$this->addOptions( self::getBaseOptions() );
	}
	
	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Allows for single behaviors
	*
	*/
	private function getBaseOptions()
	{
		return(
			array(
				'html_' => 'string',
				'script_' => 'string',
				'cssFile_' => 'string',
				'viewName_' => 'string',
			)
		);
	}

}