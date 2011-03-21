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
 * @version 	SVN $Id: CPSWHMCSApi.php -1   $
 * @since 		v1.0.6
 * 
 * @filesource
 */
class CPSWHMCSApi extends CPSApiComponent
{
	//********************************************************************************
	//* Class Constants
	//********************************************************************************
	
	const CLIENT_API = 'client';
	const SUPPORT_API = 'support';
	const MODULE_API = 'module';
	const QUOTE_API = 'quotes';
	const ORDER_API = 'orders';
	const BILLING_API = 'billing';
	const MISC_API = 'miscellaneous';
	
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

		//	Add ours...
		$this->addOptions( self::getBaseOptions() );
	
		//	And our settings...
		$this->apiQueryName = 'action';
	}
	
	/**
	 * Makes an API call to the local install of WHMCS
	 * 
	 * @param string $sAction
	 * @param array $arRequestData
	 */
	public function makeApiCall( $sApiName, $sSubApiName, $arRequestData = array() )
	{
		$_arResults = array();
		
		$this->apiToUse = $sApiName;
		$arRequestData['username'] = $this->apiUserName;
		$arRequestData['password'] = md5( $this->apiPassword );
		$arRequestData['action'] = $sSubApiName;
		
		if ( $_arResponse = parent::makeRequest( $sSubApiName, $arRequestData, 'POST' ) )
		{
			if ( false !== strpos( $_arResponse, '<?xml' ) )
			{
				$_oXml = simplexml_load_string( $_arResponse );
				if ( $_oXml->result == 'success' )
					return $_oXml;

				$this->lastErrorMessage = $_oXml->message;
			}
			else
			{
				if ( $_arData = explode( ';', $_arResponse ) )
				{
					foreach ( $_arData as $_oItem )
					{
						$_sItem = explode( '=', $_oItem );
						if ( trim( $_sItem[0] ) ) $_arResults[ $_sItem[0] ] = $_sItem[1];
					}

					if ( PS::o( $_arResults, 'result' ) == 'success' )
						return $_arResults;

					$this->lastErrorMessage = PS::o( $_arResults, 'message' );
				}
			}
		}
		else
			$this->lastErrorMessage = 'Unknown error making API call';
		
		return false;
	}
		
	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Allows for single behaviors
	*/
	private function getBaseOptions()
	{
		return(
			array(
				//	API options
				'apiUserName' => 'string:',
				'apiPassword' => 'string:',
			)
		);
	}

}
