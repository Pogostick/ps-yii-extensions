<?php
/**
 * CPSComponent class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.gnu.org/licenses/gpl.html
 */

/**
 * The CPSComponent is the base class for all Pogostick components for Yii
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Base
 * @filesource
 * @since 1.0.3
 */
class CPSComponent extends CApplicationComponent
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Constructor
	*
	*/
	public function __construct()
	{
		//	Log it and check for issues...
		$this->psLog( '{class} constructor called', 'info', ( ! method_exists( $this, 'hasBehaviorMethod' ) ) ? 'CComponent does not contain the proper methods to support this object' : null );

		$this->attachBehaviors(
        	array(
        		'psComponent' => 'application.extensions.pogostick.behaviors.CPSComponentBehavior',
        	)
        );
	}

	//********************************************************************************
	//* Private
	//********************************************************************************

	/**
	* Outputs to the log and optionally throws an exception
	*
	* @param string $sMessage The message to log. Supports the '{class}' parameter substitution.
	* @param string $sLevel The level of the logged message. Defaults to 'info'
	* @param string $sExceptionMessage If supplied, will throw an exception
	* @param string $sCategory The category of the logged message. Defaults to 'application'
	*/
	protected function psLog( $sMessage, $sLevel = 'info', $sExceptionMessage = null, $sCategory = 'application' )
	{
		//	Log the message
		Yii::log( Yii::t( $sCategory, $sMessage, array( '{class}' => __CLASS__ ) ), $sLevel, $sCategory );

		//	Make sure we have the proper support
		if ( null != $sExceptionMessage )
			throw new CException( Yii::t( $sCategory, Yii::t( $sCategory, $sMessage, array( '{class}' => __CLASS__ ) ), $sLevel, $sCategory ), $sLevel, $sCategory );
	}

}
