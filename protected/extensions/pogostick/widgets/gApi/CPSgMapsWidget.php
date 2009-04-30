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
		$this->setOption( 'options.value',
			array(
				//	GMapOptions
				'size' => array( 'type' => 'array' ),
				'mapTypes' => array( 'type' => 'array' ),
				'draggableCursor' => array( 'type' => 'string' ),
				'draggingCursor' => array( 'type' => 'string' ),
				'googleBarOptions' => array( 'type' => 'array' ),
				'backgroundColor' => array( 'type' => 'string' ),
				//	Method Options
				'mapCenter' => array( 'type' => 'array' ),
				'mapType' => array( 'type' => 'string' ),
			)
		);
	}

	public function generateJavascript()
	{
		$_sCode = "var map = new GMap2(document.getElementById(\"{$this->id}\"));";
	}
}
