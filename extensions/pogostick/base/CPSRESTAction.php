<?php
/**
 * CPSRESTAction.php class file
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage Base
 * @since psYiiExtensions v1.0.6
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
/**
 * CPSRESTAction represents a REST action that is defined as a CPSRESTController method.
 *
 * The method name is like 'restXYZ' where 'XYZ' stands for the action name.
 */
class CPSRESTAction extends CAction
{
	/**
	* Runs the REST action.
	* @throws CHttpException
	*/
	public function run()
	{
		$_oController = $this->getController();
		
		if ( ! $_oController instanceof CPSRESTController )
		{
			$_oController->missingAction( $this->getId() );
			return;
		}
		
		//	Call the controllers dispatch method...
		$_oController->dispatchRequest( $this );
	}

}
