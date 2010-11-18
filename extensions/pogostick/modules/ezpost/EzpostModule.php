<?php
/**
 * EzpostModule class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 What's Up Interactive, Inc.
 * @author Jerry Ablan <jablan@whatsup.com>
 * @link http://www.whatsup.com What's Up Interactive, Inc.
 * @package psYiiExtensions
 * @subpackage modules
 * @since v1.0.0
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
/**
 * EzpostModule provides module functions
 *
 * @package psYiiExtensions
 * @subpackage modules
 */
class EzpostModule extends CWebModule
{
	/**
	* Initialize
	* 
	*/
	public function init()
	{
		// import the module-level models and components
		$this->setImport(
			array(
				'ezpost.models.*',
				'ezpost.components.*',
			)
		);
	}

	/**
	* put your comment there...
	* 
	* @param mixed $controller
	* @param mixed $action
	*/
	public function beforeControllerAction( $controller, $action )
	{
		if ( parent::beforeControllerAction( $controller, $action ) ) return true;
		return false;
	}
}
