<?php
/**
 * CPSPayment class file.
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
 * CPSPayment provides authorization of credit card data
 *
 * @package psYiiExtensions
 * @subpackage Components
 * @property $gateway The object representing the payment gateway to use
 */
class CPSPayment extends CPSComponent
{
	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Build our object
	* 
	* @access public
	*/
	public function __construct()
	{
		//	Phone home...
		parent::__construct();

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
		if ( $this->isEmpty( $this->gatewayConfig ) || ! is_array( $this->gatewayConfig ) || ! array_key_exists( 'class', $this->gatewayConfig ) )
			throw new CPSException( 'Invalid or no payment gateway specified.' );
			
		//	Create our gateway
		$_oGateway = Yii::createComponent( $this->gatewayConfig );
		
		//	Is it cool?
		if ( ! ( $_oGateway instanceof CPSPaymentGateway ) )
			throw new CPSException( 'Payment gateway specified must implement IPSPaymentGateway interface.' );
		
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
	public static function &create( $sGatewayClass, $sClass = __CLASS__ )
	{
		$_oObj = new CPSPayment();
		$_oObj->gatewayConfig = array( 'class' => $sGatewayClass );
		$_oObj->init();
		
		return $_oObj;
	}
	
}