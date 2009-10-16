<?php
/**
 * CPSGoogleCheckout class file.
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
 * Payment gateway interface for Google Checkout
 */
class CPSGoogleCheckout extends CPSPaymentGateway
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	public function __construct()
	{
		//	Phone home...
		parent::__construct();
		
		//	Augment options...
		$this->addOptions( 
			array(
				'action' => 'string::action:true',
				'actionBody' => 'string::xml_body:true',
			)
		);
	}
	
	/**
	* Process a transaction through the gateway
	* 
	* @param array $arOptions Override the set options or provide new ones
	* @returns CPSPaymentGatewayResponse The results of the transaction
	* @access public
	*/
	public function processTransaction( array $arOptions = null )
	{
		$_sUrl = $this->getTransactionUrl();

		if ( ! $this->productionMode )
		{
			$_sEncData = base64_encode( $this->testAltApiKey . ':' . $this->textApiKey );
			$_sUrl .= $this->action . '/Merchant/' . $this->testAltApiKey;
		}
		else
		{
			$_sEncData = base64_encode( $this->altApiKey . ':' . $this->apiKey );
			$_sUrl .= $this->action . '/Merchant/' . $this->altApiKey;
		}

		$_arHeaders = array(
			'Authorization: Basic ' . $_sEncData,
			'Content-Type: application/xml;charset=UTF-8',
			'Accept: application/xml;charset=UTF-8'
		);
		
		//	Make the request
		if ( ! ( $_sResult = $this->makeHttpRequest( $_sUrl, $this->actionBody, 'POST', null, 60, $_arHeaders ) ) )
			throw new CPSException( 'Error connecting to payment gateway' );
			
		//	Process the response
		$this->m_oResponse = new CPSGoogleCheckoutResponse();
		$this->m_oResponse->processResponse( $_sResult );
		
		//	And return...
		return $this->m_oResponse;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Returns the transaction url based on productionMode setting
	*/
	protected function getTransactionUrl()
	{
		$_sHost = ( $this->productionMode ) ? 'https://checkout.google.com/' : 'https://sandbox.google.com/checkout/';
		return $_sHost . "api/checkout/v2/";
	}

}