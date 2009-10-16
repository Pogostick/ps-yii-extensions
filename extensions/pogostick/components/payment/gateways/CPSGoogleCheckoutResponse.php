<?php
/**
 * CPSGoogleCheckoutResponse class file.
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
 * CPSGoogleCheckoutResponse encapsulates a Google Checkout response
 *
 * @package psYiiExtensions
 * @subpackage Components
 */
class CPSGoogleCheckoutResponse extends CPSPaymentGatewayResponse
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	protected $m_arEventMap = array(
		'request-received' => 'onRequestReceived',
		'error' => 'onError',
		'diagnosis' => 'onDiagnosis',
		'checkout-redirect' => 'onCheckoutRedirect',
		'merchant-calculation-callback' => 'onMerchantCalculationCallback',
		'new-order-notification' => 'onNewOrderNotification',
		'order-state-change-notification' => 'onOrderStateChangeNotification',
		'charge-amount-notification' => 'onChargeAmountNotification',
		'chargeback-amount-notification' => 'onChargebackAmountNotification',
		'refund-amount-notification' => 'onRefundAmountNotification',
		'risk-information-notification' => 'onRiskInformationNotification',
	);
	
	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Build our object
	* 
	* @param mixed $oResponse The response
	* @access public
	*/
	public function __construct()
	{
		//	Phone home...
		parent::__construct();
		
		//	Options for this gateway
		$this->addOptions( 
			array(
				'schemaUrl' => 'string:http://checkout.google.com/schema/2',
			)
		);
		
		//	Change AVS response array...
		$this->m_arAVSResponse = array(
			'Y' => 'Full AVS match (address and postal code)',
			'P' => 'Partial AVS match (postal code only)',
			'A' => 'Partial AVS match (address only)',
			'N' => 'no AVS match',
			'U' => 'AVS not supported by issuer',
		);

		//	And our CAV/CVN responses		
		$this->m_arCAVResponse = array(
			'M' => 'CVN match',
			'N' => 'No CVN match',
			'U' => 'CVN not available',
			'E' => 'CVN error',
		);
	}
	
	/**
	* The response event handlers
	* 
	*/
	public function events()
	{
		return(
			array_merge(
				parent::events(),
				array(
					'onRequestReceived' => 'handleRequestReceived',
					'onError' => 'handleError',
					'onDiagnosis' => 'handleDiagnosis',
					'onCheckoutRedirect' => 'handleCheckoutRedirect',
					'onMerchantCalculationCallback' => 'handleMerchantCalculationCallback',
					'onNewOrderNotification' => 'handleNewOrderNotification',
					'onOrderStateChangeNotification' => 'handleOrderStateChangeNotification',
					'onChargeAmountNotification' => 'handleChargeAmountNotification',
					'onChargebackAmountNotification' => 'handleChargebackAmountNotification',
					'onRefundAmountNotification' => 'handleRefundAmountNotification',
					'onRiskInformationNotification' => 'handleRiskInformationNotification',
				)
			)
		);
	}
	
	//********************************************************************************
	//* Event Handlers
	//********************************************************************************

	/**
	* <request-received> response handler
	* 
	* The <request-received> response indicates that you sent a properly formed XML request 
	* to Google Checkout. However, this response does not indicate whether your request 
	* was processed successfully.
	* 
	* @access public
	* @param mixed $oEvent
	*/
	public function handleRequestReceived( $oEvent )
	{
		//	Just log it to trace...
		Yii::trace( 'CPSGoogleCheckoutResponse : request-received event : ' . htmlentities( $oEvent->getResponse()->dump_mem() ) );
	}

	/**
	* error response handler
	* 
	* @param mixed $oEvent
	*/
	public function handleError( $oEvent )
	{
		//	Get the message...
		$_oDomRoot = $oEvent->getResponse()->document_element();
		$_sError = $_oDomRoot->get_elements_by_tagname( 'error-message' );
		$_arWarnings = $_oDomRoot->get_elements_by_tagname( 'warning-messages' );
		
		//	Fill out the response
		$this->responseCode = 0;
		$this->responseSubcode = null;
		$this->responseReasonCode = null;
		$this->responseReaseText = $_sError;
		$this->authorizationCode = null;
		$this->avsResponse = null;
		
		//	Just log it to trace...
		Yii::trace( 'CPSGoogleCheckoutResponse : error event : ' . htmlentities( $oEvent->getResponse()->dump_mem() ) );
	}

	/**
	* diagnosis response handler
	* 
	* @param mixed $oEvent
	*/
	public function handleDiagnosis( $oEvent )
	{
		//	Just log it to trace...
		Yii::trace( 'CPSGoogleCheckoutResponse : diagnosis event : ' . htmlentities( $oEvent->getResponse()->dump_mem() ) );
	}

	/**
	* checkout-redirect response handler
	* 
	* @param mixed $oEvent
	*/
	public function handleCheckoutRedirect( $oEvent )
	{
		//	Lookup the url...
		$_oDomRoot = $oEvent->getResponse()->document_element();
		$_arRedirUrlList = $_oDomRoot->get_elements_by_tagname( 'redirect-url' );
		$_sRedirUrl = $arRedirUrlList[ 0 ]->get_content();
		
		//	Redirect to the url...
		Yii::app()->request->redirect( $_sRedirUrl );
	}

	/**
	* merchant-calculation-callback response handler
	* 
	* @param mixed $oEvent
	*/
	public function handleMerchantCalculationCallback( $oEvent )
	{
		//	You have to read the doc about this one. 
	}

	/**
	* new-order-notification response handler
	* 
	* @param mixed $oEvent
	*/
	public function handleNewOrderNotification( $oEvent )
	{
		//	Acknowledge event
		$this->acknowledgeNotification( 'new-order-notification' );
	}

	/**
	* order-state-change-notification response handler
	* 
	* @param mixed $oEvent
	*/
	public function handleOrderStateChangeNotification( $oEvent )
	{
		//	Acknowledge event
		$this->acknowledgeNotification( 'order-state-change-notification' );
	}

	/**
	* charge-amount-notification response handler
	* 
	* @param mixed $oEvent
	*/
	public function handleChargeAmountNotification( $oEvent )
	{
		//	Acknowledge event
		$this->acknowledgeNotification( 'charge-amount-notification' );
	}

	/**
	* chargeback-amount-notification response handler
	* 
	* @param mixed $oEvent
	*/
	public function handleChargebackAmountNotification( $oEvent )
	{
		//	Acknowledge event
		$this->acknowledgeNotification( 'chargeback-amount-notification' );
	}

	/**
	* refund-amount-notification response handler
	* 
	* @param mixed $oEvent
	*/
	public function handleRefundAmountNotification( $oEvent )
	{
		//	Acknowledge event
		$this->acknowledgeNotification( 'handle-refund-amount-notification' );
	}

	/**
	* risk-information-notification response handler
	* 
	* @param mixed $oEvent
	*/
	public function handleRiskInformationNotification( $oEvent )
	{
		//	Lookup the url...
		$_oDomRoot = $oEvent->getResponse()->document_element();
		$this->avsResponseCode = $_oDomRoot->get_elements_by_tagname( 'avs-response' );
		$this->cavResponseCode = $_oDomRoot->get_elements_by_tagname( 'cvn-response' );

		//	Populate the responses...
		$this->avsResponse = $this->getAVSResponseText( $this->avsResponseCode );
		$this->cavResponse = $this->getCAVResponseText( $this->cavResponseCode );

  		//	Acknowledge event
		$this->acknowledgeNotification( 'risk-information-notification' );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Parses the response from the Google Checkout gateway and raises the proper event
	* 
	* @param mixed $oResponse
	*/
	protected function processResponse( $oResponse )
	{
		//	Retrieve the XML sent in
		$_sXml = $this->rawResponse = $HTTP_RAW_POST_DATA;
		 
		//	Get rid of PHP's magical escaping of quotes 
		if ( get_magic_quotes_gpc() ) $_sXml = stripslashes( $_sXml );

		//	Get the type of response...
		$_oDom = domxml_open_mem( $_sXml );
		$_oDomRoot = $_oDom->document_element();
		$_sRootTag = $_oDomRoot->tagname();
		
		//	Just log it to trace...
		Yii::trace( 'CPSGoogleCheckoutResponse : Processing event : ' . htmlentities( $_oDom->dump_mem() ) );
		
		//	Something we handle? Let's raise that event...
		if ( array_key_exists( $_sRootTag, $this->m_arEventMap ) ) 
			$this->raiseEvent( $this->m_arEventMap[ $_sRootTag ], new CPSPaymentEvent( $_oDom ) );
	}
	
	/**
	* Acknowledges that the notification was received
	* @access protected
	* @param string $sEvent The name of the event for logging
	*/
	protected function acknowledgeNotification( $sEvent )
	{
		//	Build the acknowledgement
		$_sXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
	        "<notification-acknowledgment xmlns=\"" .
	        $this->schemaUrl . "\"/>";

	    //	Send it out...
	    echo $_sXml;
	    
	    //	Log it
 		Yii::trace( 'CPSGoogleCheckoutResponse : acknowledged notification : ' . $sEvent );
	}

}