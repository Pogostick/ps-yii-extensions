<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSgMapsWidget encapsulates access to the {@link http://code.google.com/apis/maps Google Maps API}
 * 
 * @package 	psYiiExtensions.widgets
 * @subpackage 	gApi
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.4
 *  
 * @filesource
 */
class CPSgMapsWidget extends CPSgApiWidget
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Initialize our widget
	*/
	public function init()
	{
		$this->addOptions(
			array(
				//	GMapOptions
				'size' => 'array',
				'mapTypes' => 'array',
				'draggableCursor' => 'string',
				'draggingCursor' => 'string',
				'googleBarOptions' => 'array',
				'backgroundColor' => 'string',
				//	Method Options
				'mapCenter' => 'array',
				'mapType' => 'string',
			),
			true
		);
	}

	/**
	* Our javascript
	*/
	public function generateJavascript()
	{
		$_sCode = "var map = new GMap2(document.getElementById(\"{$this->id}\"));";
	}
}