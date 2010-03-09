<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSPayment provides authorization of payment data
 * 
 * @package 	psYiiExtensions.payments
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.1.0
 * 
 * @filesource
 * 
 * @property $gateway The object representing the payment gateway to use
 */
class CPSPayment extends CPSComponent
{
	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Build our object
	* @access public
	*/
	public function preinit()
	{
		//	Phone home...
		parent::preinit();

		//	Add our component options
		$this->addOptions(
			array(
				'productionMode_' => 'bool:false::true',
				'gatewayConfig_' => 'array:null::true',
				'gateway_' => 'object:null::true',
			)
		);
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Initialize the component
	* 
	* @access public
	*/
	public function init()
	{
		//	Check configuration array
		if ( $this->gatewayConfig || ! is_array( $this->gatewayConfig ) || ! array_key_exists( 'class', $this->gatewayConfig ) )
			throw new CPSException( 'Invalid or no payment gateway specified.' );
			
		//	Create our gateway
		$_oGateway = Yii::createComponent( $this->gatewayConfig );
		
		//	Is it cool?
		if ( ! ( $_oGateway instanceof CPSPaymentGateway ) )
			throw new CPSException( 'Payment gateway specified is not compatible.' );
		
		$this->gateway = $_oGateway;
	}
	
	/**
	* Process a transaction through the gateway
	* 
	* @param array $arOptions
	* @returns mixed The results of the transaction
	* @access public
	*/
	public function processTransaction( array $arOptions = null )
	{
		if ( null == $this->gateway )
			throw new CPSException( 'No payment gateway available. Please configure a payment gateway.' );
			
		//	Let the gateway do it's magic...
		return $this->gateway->processTransaction( $arOptions );
	}
	
	/**
	* Creates a payment object and sets the gateway
	* 
	* @access public
	* @static
	* @param array $arConfig
	* @param string $sClass
	* @returns CPSPayment
	*/
	public static function create( $sGatewayClass, $sClass = __CLASS__ )
	{
		$_oObj = new self();
		$_oObj->gatewayConfig = array( 'class' => $sGatewayClass );
		$_oObj->init();

		return $_oObj;
	}
	
}