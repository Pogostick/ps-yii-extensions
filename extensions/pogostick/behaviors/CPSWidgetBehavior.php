<?php
/**
 * CPSWidgetBehavior class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSWidgetBehavior provides convenient access to typical "widget" behaviors
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @filesource
 * @package psYiiExtensions
 * @subpackage Behaviors
 * @filesource
 * @since 1.0.4
 */
class CPSWidgetBehavior extends CPSComponentBehavior
{
	//********************************************************************************
	//* Constructor
	//********************************************************************************

	public function __construct()
	{
		//	Call daddy
		parent::__construct();

		//	Add our settings to this
		$this->setOptions( self::getBaseOptions() );

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $this->getInternalName(), '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'trace', $this->getInternalName() );
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
				'html' => array( 'value' => '', 'type' => 'string' ),
				'script' => array( 'value' => '', 'type' => 'string' ),
				'cssFile' => array( 'value' => '', 'type' => 'string' ),
				'viewName' => array( 'value' => '', 'type' => 'string' ),
			)
		);
	}

}