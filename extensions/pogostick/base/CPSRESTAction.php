<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSRESTAction represents a REST action that is defined as a CPSRESTController method.
 * The method name is like 'restXYZ' where 'XYZ' stands for the action name.
 * 
 * @package 	psYiiExtensions
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 */
class CPSRESTAction extends CAction implements IPSBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Runs the REST action.
	* @throws CHttpException
	*/
	public function run()
	{
		$_oController = $this->getController();
		
		if ( ! ( $_oController instanceof IPSRest ) )
		{
			$_oController->missingAction( $this->getId() );
			return;
		}
		
		//	Call the controllers dispatch method...
		$_oController->dispatchRequest( $this );
	}

}