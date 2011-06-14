<?php
/**
 * This file is part of the Pogostick Yii Extension library
 * 
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
/**
 * @package 	psYiiExtensions
 * @subpackage 	components
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * 
 * @version 	SVN $Id: CPSWHMCSSupportApi.php -1   $
 * @since 		v1.0.6
 * 
 * @filesource
 */
class CPSWHMCSSupportApi extends CPSWHMCSApi
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/***
	* Initialize
	*/
	public function preinit()
	{
		//	Call daddy...
		parent::preinit();

		//	Support API
		$this->apiToUse = self::SUPPORT_API;
		
        //	Support::OpenTicket
		$this->addRequestMapping( 'clientId', 'clientid', true, null, self::SUPPORT_API, 'openticket' );
        $this->addRequestMapping( 'name', 'name' );
        $this->addRequestMapping( 'email', 'email' );
        $this->addRequestMapping( 'deptId', 'deptid' );
        $this->addRequestMapping( 'subject', 'subject' );
        $this->addRequestMapping( 'message', 'message' );
        $this->addRequestMapping( 'priority', 'priority' );
        $this->addRequestMapping( 'serviceId', 'serviceid' );
        $this->addRequestMapping( 'customFields', 'customfields' );
	}

	/**
	 * Opens a support ticket
	 * @param array $arRequestData
	 * @return array
	 */
	public function openTicket( $arRequestData = array() )
	{
		return $this->makeApiCall( self::SUPPORT_API, 'openticket', $arRequestData );
	}

}