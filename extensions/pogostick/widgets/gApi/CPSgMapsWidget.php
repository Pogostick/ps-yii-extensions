<?php
/**
 * CPSgMapsWidget class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSgMapsWidget encapsulates access to the {@link http://code.google.com/apis/maps Google Maps API}
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Widgets
 * @filesource
 * @since 1.0.4
 */
class CPSgMapsWidget extends CPSgApiWidget
{
	public function init()
	{
		$this->addOptions(
			array(
				//	GMapOptions
				'size' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'mapTypes' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'draggableCursor' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'draggingCursor' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'googleBarOptions' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'backgroundColor' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				//	Method Options
				'mapCenter' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'mapType' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
			),
			true
		);
	}

	public function generateJavascript()
	{
		$_sCode = "var map = new GMap2(document.getElementById(\"{$this->id}\"));";
	}
}