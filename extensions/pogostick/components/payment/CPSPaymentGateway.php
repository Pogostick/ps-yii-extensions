<?php
/**
 * CPSPaymentGateway class file.
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
 * The base payment gateway 
 * @abstract
 */
abstract class CPSPaymentGateway extends CPSApiComponent
{
	//********************************************************************************
	//* Constructor
	//********************************************************************************
	
	public function __construct()
	{
		//	Phone home...
		parent::__construct();
	
		//	Add our options...
//		$this->addOptions( $this->getBaseOptions() );
	}

	/**
	* Our private base options...
	*/
	private function getBaseOptions()
	{
		return array(
			'paymentApiKey' => 'string::x_login:true',
			'paymentAltApiKey' => 'string::x_tran_key:true',
			'transactionType' => 'string:AUTH_CAPTURE:x_type:true:AUTH_CAPTURE|AUTH_ONLY|CAPTURE_ONLY|CREDIT|PRIOR_AUTH_CAPTURE|VOID',
			'transactionAmount' => 'float:0:x_amount:true',
			'cardNumber' => 'string::x_card_num:true',
			'cardCode' => 'string::x_card_code',
			'expirationDate' => 'string::x_exp_date:true',
			'transactionId' => 'string::x_trans_id',
			'authorizationCode' => 'string::x_auth_code',
			'version' => 'string:3.1:x_version:true',
			'paymentMethod' => 'string:CC:x_method:true:CC|ECHECK',
			'recurringBilling' => 'string:FALSE:x_recurring_billing:false:TRUE|FALSE|T|F|YES|NO|Y|N',
			'testRequest' => 'string:FALSE:x_test_request:false:TRUE|FALSE|T|F|YES|NO|Y|N',
			'duplicateWindow' => 'integer:120:x_duplicate_window:false',
			'delimiterCharacter' => 'string:|:x_delim_char:true',
			'delimitData' => 'string:TRUE:x_delim_data:true:TRUE|FALSE|T|F|YES|NO|Y|N',
			'encapsulateCharacter' => 'string::x_encap_char:false',
			'relayResponse' => 'string:FALSE:x_relay_response:false:TRUE|FALSE|T|F|YES|NO|Y|N',
		);
	}

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
	abstract public function processTransaction( array $arRequestData = null );

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Returns the transaction url based on productionMode setting
	*/
	abstract protected function getTransactionUrl();

}