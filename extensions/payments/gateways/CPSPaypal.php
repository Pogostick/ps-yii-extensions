<?php
/**
 * CPSPaypal class file.
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
 * Payment gateway interface for Paypal
 */
class CPSPaypal extends CPSPaymentGateway
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	const 	SET_EXPRESS_CHECKOUT = 'SetExpressCheckout';
	const	GET_EXPRESS_CHECKOUT_DETAILS = 'GetExpressCheckoutDetails';
	const	DO_EXPRESS_CHECKOUT_PAYMENT = 'DoExpressCheckoutPayment';
	const	REFUND_TRANSACTION = 'RefundTransaction';
	
	const	TOKEN = '_paypalToken';
	const	PAYER_ID = '_paypalPayerId';
	
	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	* The last response object
	* 
	* @var CPSPaypalResponse
	*/
	protected $m_oLastResponse = null;
	
	public function getLastResponse() { return $this->m_oLastResponse; }
	
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
				'apiToken' => 'string::TOKEN:true',
				'apiUserName' => 'string::USER:true',
				'apiPassword' => 'string::PWD:true',
				'apiSignature' => 'string::SIGNATURE:true',
				'apiVersion' => 'string:3.2:VERSION:true',
				'apiEmailSubject' => 'string::SUBJECT',
				'apiPayerId' => 'string::PAYERID',
				'errorUrl' => 'string::ERRORURL',
				'redirectUrl_' => 'string',
				'getDetails_' => 'bool:false::true',
			)
		);
		
		//	Get the token and payerId if there...
		$this->apiToken = PS::o( $_REQUEST, 'TOKEN', Yii::app()->user->getState( self::TOKEN ) );
		$this->apiPayerId = PS::o( $_REQUEST, 'PAYERID', Yii::app()->user->getState( self::PAYER_ID ) );
		
		Yii::app()->user->setState( self::TOKEN, $this->apiToken );
		Yii::app()->user->setState( self::PAYER_ID, $this->apiPayerId );
			
		//	Build the API calls...
		$this->setRequestMappings();
	}
	
	/**
	* Set up the API calls in the request map
	* 
	* @access public
	*/
	protected function setRequestMappings()
	{
		//	No api names, we'll build it in the helpers
		$this->requireApiQueryName = false;

		//	No sub apis...
		$this->apiSubUrls = null;

		//	Create the base array
		$this->requestMap = array();
		
		//	Set
		$this->addRequestMapping( 'paymentMethod', 'METHOD', true, array( 'default' => self::SET_EXPRESS_CHECKOUT ), self::SET_EXPRESS_CHECKOUT );
		$this->addRequestMapping( 'returnUrl', 'RETURNURL', true );
		$this->addRequestMapping( 'cancelUrl', 'CANCELURL', true );
		$this->addRequestMapping( 'paymentAmount', 'AMT', true );
		$this->addRequestMapping( 'currencyCode', 'CURRENCYCODE', false, array( 'default' => 'USD' ) );
		$this->addRequestMapping( 'maximumAmount', 'MAXAMT' );
		$this->addRequestMapping( 'userAction', 'USERACTION', false, array( 'default' => 'continue' ) );
		$this->addRequestMapping( 'invoiceNumber', 'INVNUM' );
		$this->addRequestMapping( 'addressOverride', 'ADDROVERRIDE', false, array( 'default' => 0 ) );
		$this->addRequestMapping( 'shipToName', 'SHIPTONAME' );
		$this->addRequestMapping( 'shipToStreet', 'SHIPTOSTREET' );
		$this->addRequestMapping( 'shipToStreet2', 'SHIPTOSTREET2' );
		$this->addRequestMapping( 'shipToCity', 'SHIPTOCITY' );
		$this->addRequestMapping( 'shipToState', 'SHIPTOSTATE' );
		$this->addRequestMapping( 'shipToZip', 'SHIPTOZIP' );
		$this->addRequestMapping( 'shipToCountryCode', 'SHIPTOCOUNTRYCODE' );
		$this->addRequestMapping( 'localeCode', 'LOCALECODE', false, array( 'default' => 'US' ) );
		$this->addRequestMapping( 'pageStyle', 'PAGESTYLE' );
		$this->addRequestMapping( 'headerImageUrl', 'HDRIMG' );

		//	Get
		$this->addRequestMapping( 'paymentMethod', 'METHOD', true, array( 'default' => self::GET_EXPRESS_CHECKOUT_DETAILS ), self::GET_EXPRESS_CHECKOUT_DETAILS );
		
		//	Do
		$this->addRequestMapping( 'paymentMethod', 'METHOD', true, array( 'default' => self::DO_EXPRESS_CHECKOUT_PAYMENT ), self::DO_EXPRESS_CHECKOUT_PAYMENT );
		$this->addRequestMapping( 'paymentAmount', 'AMT', true );
		$this->addRequestMapping( 'paymentAction', 'PAYMENTACTION', true, array( 'default' => 'Sale', 'allowed' => array( 'Sale', 'Authorization', 'Order' ) ) );
		$this->addRequestMapping( 'currencyCode', 'CURRENCYCODE', false, array( 'default' => 'USD' ) );
		$this->addRequestMapping( 'invoiceNumber', 'INVNUM' );
		$this->addRequestMapping( 'itemAmount', 'ITEMAMT' );
		$this->addRequestMapping( 'shippingAmount', 'SHIPPINGAMT' );
		$this->addRequestMapping( 'handlingAmount', 'HANDLINGAMT' );
		$this->addRequestMapping( 'taxAmount', 'TAXAMT' );

		//	Refund Transaction
		$this->addRequestMapping( 'paymentMethod', 'METHOD', true, array( 'default' => self::REFUND_TRANSACTION ), self::REFUND_TRANSACTION );
		$this->addRequestMapping( 'transactionId', 'TRANSACTIONID', true );
		$this->addRequestMapping( 'refundType', 'REFUNDTYPE', true, array( 'default' => 'Full', 'allowed' => array( 'Full', 'Partial' ) ) );
		$this->addRequestMapping( 'refundAmount', 'AMT', true );
		$this->addRequestMapping( 'currencyCode', 'CURRENCYCODE', false, array( 'default' => 'USD' ) );
	}
	
	/**
	* Makes a setExpressCheckout call
	* 
	* @access protected
	* @param array $arRequestData
	* @returns boolean
	*/
	protected function setExpressCheckout( array $arRequestData = null )
	{
		//	Sets internal options
		$this->getTransactionUrl();
		
		//	Set it and forget it...		
		$this->apiToUse = self::SET_EXPRESS_CHECKOUT;
		$this->requestData = array_merge( ( null !== $arRequestData ) ? $arRequestData : $this->requestData, $this->makeOptions( true, PS::OF_ASSOC_ARRAY ) );
		
		//	Make request and process the response
		try
		{
			//	Call & process
			$_oResponse = $this->m_oLastResponse = CPSPaypalResponse::create( $this->makeRequest( '/', null, CPSApiBehavior::HTTP_POST ) );
			
			//	Did it work?
			if ( $_oResponse->apiCallSuccess )
			{
				//	Store the token...
				$this->apiToken = PS::o( $_oResponse->cookedResponse, 'TOKEN', null );
				Yii::app()->user->setState( self::TOKEN, $this->apiToken );
				
				//	Redirect...
				if ( 'commit' == PS::o( $this->requestData, 'userAction', 'continue' ) )
			 		Yii::app()->request->redirect( $this->redirectUrl . $this->apiToken . '&USERACTION=commit' );

				//	Redirect to get login details
				Yii::app()->request->redirect( $this->redirectUrl . $this->apiToken );
			}
			else
				throw new CPSPaymentException( Yii::t( __CLASS__, 'Error during payment request to Paypal : "{response}" ', array( '{response}' => $_oResponse->rawResponse ) ) );
		}
		catch ( CPSPaymentException $_ex )
		{
			throw new CPSPaymentException( Yii::t( __CLASS__, 'Error during payment request to Paypal : "{response}" ', array( '{response}' => $_ex->getMessage() ) ) );
		}
	}
	
	/**
	* Retrieve the checkout details from Paypal
	* 
	* @access public
	* @param array $arRequestData
	*/
	public function getExpressCheckoutDetails()
	{
		//	Sets internal options
		$this->getTransactionUrl();
		
		//	Set it and forget it...		
		$this->apiToUse = $this->paymentMethod = self::GET_EXPRESS_CHECKOUT_DETAILS;
		$this->requestData = $this->makeOptions( true, PS::OF_ASSOC_ARRAY );

		try
		{
			//	Call & process
			$_oResponse = $this->m_oLastResponse = CPSPaypalResponse::create( $this->makeRequest( '/' ) );
			
			//	Did it work?
			if ( $_oResponse->apiCallSuccess )
			{
				//	Store the payer ID
				$this->apiPayerId = PS::o( $_oResponse->cookedResponse, 'PAYERID', $this->apiPayerId );
				Yii::app()->user->setState( self::PAYER_ID, $this->apiPayerId );

				//	It's all good!
				return true;
			}

			//	Log it
			Yii::log( 'Error during getExpressCheckoutDetails request: ' . $_oResponse->rawResponse, 'error', 'pogostick.components.payment.gateways.CPSPaypal' );
		}
		catch ( CPSPaymentException $_ex )
		{
			Yii::log( 'Exception during getExpressCheckoutDetails request: ' . $_ex->getMessage(), 'error', 'pogostick.components.payment.gateways.CPSPaypal' );
		}
		
		//	Should only get here when error occurs...
		return false;
	}

	/**
	* The second(third), and final, step in the Paypal Express Checkout flow. Called after setCheckoutExpress
	* 	
	* @access protected
	* @param array $arRequestData
	* @returns boolean
	*/
	protected function doExpressCheckoutPayment( array $arRequestData = null )
	{
		//	Sets internal options
		$this->getTransactionUrl();
		
		//	Set it and forget it...		
		$this->apiToUse = $this->paymentMethod = self::DO_EXPRESS_CHECKOUT_PAYMENT;
		$this->requestData = array_merge( ( null !== $arRequestData ) ? $arRequestData : $this->requestData, $this->makeOptions( true, PS::OF_ASSOC_ARRAY ) );

		try
		{
			//	Call & process
			$_oResponse = $this->m_oLastResponse = CPSPaypalResponse::create( $this->makeRequest( '/', null, CPSApiBehavior::HTTP_POST ) );
			
			//	Did it work?
			if ( $_oResponse->apiCallSuccess ) return true;

			//	Log it
			Yii::log( 'Error during doExpressCheckoutPayment request: ' . $_oResponse->rawResponse, 'error', 'pogostick.components.payment.gateways.CPSPaypal' );
		}
		catch ( CPSPaymentException $_ex )
		{
			Yii::log( 'Exception during doExpressCheckoutPayment  request: ' . $_ex->getMessage(), 'error', 'pogostick.components.payment.gateways.CPSPaypal' );
		}
		
		//	Should only get here when error occurs...
		return false;
	}
	
	/**
	* Starts the gateway transaction process
	* 
	* @access public
	* @param array $arRequestData Override the set data or provide new ones
	* @param boolean $bForce Forces a reset/restart of the transaction
	* @returns boolean
	*/
	public function beginTransaction( array $arRequestData = null, $bForce = false )
	{
		$_bResult = true;
		
		//	Forced?
		if ( $bForce )
		{
			$this->apiToken = null;
			$this->apiPayerId = null;
		}
		
		//	Set request data...
		$_arRequestData = ( null != $arRequestData ) ? $arRequestData : $this->requestData;
		if ( empty( $_arRequestData ) || ! is_array( $_arRequestData ) ) $_arRequestData = array();
		
		//	Do we have our Paypal token?
		if ( $this->isEmpty( $this->apiToken ) )
			$_bResult = $this->setExpressCheckout( $_arRequestData );
			
		return $_bResult;
	}

	/**
	* Process a transaction through the gateway
	* 
	* @param array $arRequestData Override the set data or provide new ones
	* @returns CPSPaymentGatewayResponse The results of the transaction
	* @access public
	*/
	public function processTransaction( array $arRequestData = null )
	{
		//	Set request data...
		$_arRequestData = ( null != $arRequestData ) ? $arRequestData : $this->requestData;
		if ( empty( $_arRequestData ) || ! is_array( $_arRequestData ) ) $_arRequestData = array();
		
		//	Do we have our Paypal token?
		if ( $this->isEmpty( $this->apiToken ) )
			throw new CPSPaymentException( Yii::t( __CLASS__, 'You must call beginTransaction() to obtain a token before calling processTransaction().' ) );
		
		//	Get the checkout details...
		if ( $this->isEmpty( $this->apiPayerId ) )
		{
			//	Does user want details retrieved?
			if ( ! $this->getExpressCheckoutDetails() )
				throw new CPSPaymentException( Yii::t( __CLASS__, 'Error during call to getExpressCheckoutDetails.' ) );
		}

		//	Complete the process
		return $this->doExpressCheckoutPayment( $_arRequestData );
	}
	
	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Returns the transaction url based on productionMode setting
	*/
	protected function getTransactionUrl()
	{
		if ( $this->productionMode )
		{
			$this->apiBaseUrl = 'https://api-3t.paypal.com/nvp';
			$this->redirectUrl = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';
		}
		else	//	Test/sandbox
		{
			$this->apiBaseUrl = 'https://api-3t.sandbox.paypal.com/nvp';
			$this->redirectUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';
		}
	}

}
