<?php
/**
 * example_payment_paypal view file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com Pogostick Yii Extension Library
 * @package psYiiExtensions
 * @subpackage Examples
 * @since psYiiExtensions v1.0.5
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 * @license http://www.pogostick.com/license/
 */

//	Create our payment component
$_oPaypal = CPSPayment::create( 'pogostick.components.payment.gateways.CPSPaypal' );

//	Sandbox at this time...
$_oPaypal->productionMode = false;

//	Set our API credential values 
$_oPaypal->gateway->apiUserName = 'seller_1246079119_biz_api1.comcast.net';
$_oPaypal->gateway->apiPassword = '1246079132';
$_oPaypal->gateway->apiSignature = 'AVGsOMOXiVk5pSd.-YojiH0yJiQgAZtywbHpBHEPMIAe1B89n.RWZz7O';

if ( isset( $_REQUEST['cancel'] ) && $_REQUEST['cancel'] == '1' )
	echo '<H2>TRANSACTION CANCELED BY BUYER!</H2>';

//	First thing we do is get a token...
if ( isset( $_REQUEST['c'] ) )
{
	$_arReq = array(
		'paymentAmount' => $_REQUEST['amt'],
		'currencyCode' => $_REQUEST['cc'],
		'returnUrl' => 'http://jablan.is-a-geek.com/site/paymentExample',
		'cancelUrl' => 'http://jablan.is-a-geek.com/site/paymentExample?cancel=1',
	);

	switch ( $_REQUEST['c'] )
	{
		case 's':
			$_arReq[ 'returnUrl' ] = 'http://jablan.is-a-geek.com/site/paymentExample';
			$_arReq[ 'cancelUrl' ] = 'http://jablan.is-a-geek.com/site/paymentExample?cancel=1';

			$_oPaypal->gateway->beginTransaction( $_arReq, true );
			echo '<HR><h2>SetExpressCheckout Results</h2>' . var_export( $_oPaypal->gateway->getLastResponse()->cookedResponse, true ) . '<hr>';
			break;
			
		case 'g':
			$_oPaypal->gateway->getExpressCheckoutDetails();
			echo '<HR><h2>GetExpressCheckoutDetails Results</h2>' . var_export( $_oPaypal->gateway->getLastResponse()->cookedResponse, true ) . '<hr>';
			break;
			
		case 'd':
			$_oPaypal->processTransaction( $_arReq );
			echo '<HR><h2>DoExpressCheckoutPayment Results</h2>' . var_export( $_oPaypal->gateway->getLastResponse()->cookedResponse, true ) . '<hr>';
			break;
	}
}
?>
<h1>psYiiExtensions :: Paypal ExpressCheckout Demo</h1>

<div class="yiiForm">
	<?php echo PS::form( null, 'POST', array( 'id' => 'frmMain', 'name' => 'frmMain' ) ); ?>
	<input type="hidden" name="c" id="c" value="" />
	
	<?
		echo PS::field( PS::TEXT, null, 'xTOKEN', array( 'label' => 'Token', 'value' => Yii::app()->user->getState( CPSPaypal::TOKEN ) ) );
		echo PS::field( PS::TEXT, null, 'xPAYERID', array( 'label' => 'Payer ID', 'value' => Yii::app()->user->getState( CPSPaypal::PAYER_ID ) ) );
		echo PS::field( PS::TEXT, null, 'amt', array( 'label' => 'Amount', 'value' => 1 ) );
		echo PS::field( PS::TEXT, null, 'cc', array( 'label' => 'Currency Code', 'value' => 'USD' ) );
	?>
	
	<div class="formHeader">
		<span class="action"><?php echo PS::button( 'DoExpressCheckoutPayment', array( 'onClick' => 'document.getElementById("c").value = "d"; document.frmMain.submit();' ) ); ?></span>
		<span class="action"><?php echo PS::button( 'GetExpressCheckoutDetails', array( 'onClick' => 'document.getElementById("c").value = "g"; document.frmMain.submit();' ) );?></span>
		<span class="action"><?php echo PS::button( 'SetExpressCheckout', array( 'onClick' => 'document.getElementById("c").value = "s"; document.frmMain.submit();' ) ); ?></span>
	</div>
</form>