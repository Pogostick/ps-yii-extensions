<?php
/**
 * CPSAuthorizeNET class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com Pogostick Yii Extension Library
 * @package psYiiExtensions
 * @subpackage Components
 * @since psYiiExtensions v1.0.5
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 * @license http://www.pogostick.com/license/
 */
/**
 * Payment gateway interface with Authorize.NET
 */
class CPSAuthorizeNET extends CPSPaymentGateway
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Process a transaction through the gateway
	* 
	* @param array $arOptions Override the set options or provide new ones
	* @returns CPSPaymentGatewayResponse The results of the transaction
	* @access public
	*/
	public function processTransaction( array $arOptions = null )
	{
		//	Merge in the passed in options...
		if ( is_array( $arOptions ) ) $this->setOptions( $arOptions );
		
		//	Make the post request data...
		$_sPostData = $this->makeOptions( true, PS::OF_HTTP );
		
		//	Make the request
		if ( ! ( $_sResult = PS::makeHttpRequest( $this->getTransactionUrl(), $_sPostData, 'POST' ) ) )
			throw new CPSException( 'Error connecting to payment gateway' );
			
		//	Convert results to an array...
		$_arResponse = explode( $this->delimiterCharacter, $_sResult );
		
		//	Fill out the payment response object...
		$_oResp = new CPSPaymentGatewayResponse();
		$_oResp->rawResponse = $_sResult;
		$_oResp->responseCode = $_arResponse[ 0 ];
		$_oResp->responseSubcode  = $_arResponse[ 1 ];
		$_oResp->responseReasonCode = $_arResponse[ 2 ];
		$_oResp->responseReasonText = $_arResponse[ 3 ];
		$_oResp->authorizationCode = $_arResponse[ 4 ];
		$_oResp->avsResponse = $_arResponse[ 5 ];
		$_oResp->transactionId = $_arResponse[ 6 ];
		
		//	And return...
		return $this->m_oResponse = $_oResp;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Returns the transaction url based on productionMode setting
	*/
	protected function getTransactionUrl()
	{
		$_sHost = ( $this->productionMode ) ? 'https://secure' : 'http://test';
		return $_sHost . ".authorize.net/gateway/transact.dll";
	}

}