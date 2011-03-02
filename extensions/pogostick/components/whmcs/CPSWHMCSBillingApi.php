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
 * @version 	SVN $Id: CPSWHMCSBillingApi.php -1   $
 * @since 		v1.0.6
 *
 * @filesource
 */
class CPSWHMCSBillingApi extends CPSWHMCSApi
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

		//	Billing API
		$this->apiToUse = self::BILLING_API;

		//	Billing::AddBillableItem
		$this->addRequestMapping( 'clientId', 'clientid', true, null, self::BILLING_API, 'addbillableitem' );
		$this->addRequestMapping( 'description', 'description', true );
		$this->addRequestMapping( 'amount', 'amount', true );
		$this->addRequestMapping( 'recurFrequency', 'recur', true );
		$this->addRequestMapping( 'recurCycle', 'recurcycle', true );
		$this->addRequestMapping( 'recurCount', 'recurfor', true );
		$this->addRequestMapping( 'invoiceAction', 'invoiceaction', true );
		$this->addRequestMapping( 'dueDate', 'duedate', true );

        //    Billing::AddOrder
        $this->addRequestMapping( 'clientId', 'clientid', true, null, self::BILLING_API, 'addorder' );
        $this->addRequestMapping( 'productId', 'pid', true );
        $this->addRequestMapping( 'domain', 'domain' );
        $this->addRequestMapping( 'addOns', 'addons' );
        $this->addRequestMapping( 'customFields', 'customfields' );
        $this->addRequestMapping( 'configOptions', 'configOptions' );
        $this->addRequestMapping( 'domainType', 'domainType' );
        $this->addRequestMapping( 'registrationPeriod', 'regperiod' );
        $this->addRequestMapping( 'dnsManagement', 'dnsmanagement' );
        $this->addRequestMapping( 'emailForwarding', 'emailforwarding' );
        $this->addRequestMapping( 'idProtection', 'idprotection' );
        $this->addRequestMapping( 'eppCode', 'eppcode' );
        $this->addRequestMapping( 'ns1', 'ns1' );
        $this->addRequestMapping( 'ns2', 'ns2' );
        $this->addRequestMapping( 'ns3', 'ns3' );
        $this->addRequestMapping( 'ns4', 'ns4' );
        $this->addRequestMapping( 'paymentMethod', 'paymentmethod' );
        $this->addRequestMapping( 'promoCode', 'promocode' );
        $this->addRequestMapping( 'noInvoice', 'noInvoice' );
        $this->addRequestMapping( 'noEmail', 'noemail' );
        $this->addRequestMapping( 'clientIP', 'clientip' );

		//	Billing::AcceptOrder
		$this->addRequestMapping( 'orderId', 'orderid', true, null, self::BILLING_API, 'acceptorder' );

        //	Billing::GetOrders
        $this->addRequestMapping( 'limitStart', 'limitstart', true, null, self::BILLING_API, 'getorders' );
        $this->addRequestMapping( 'limitNumber', 'limitnum' );

        //	Billing::CreateInvoice
        $this->addRequestMapping( 'clientId', 'userid', true, null, self::BILLING_API, 'createinvoice' );
        $this->addRequestMapping( 'date', 'date', true );
        $this->addRequestMapping( 'dueDate', 'duedate' );
        $this->addRequestMapping( 'paymentMethod', 'paymentmethod' );
        $this->addRequestMapping( 'sendInvoice', 'sendinvoice' );
        $this->addRequestMapping( 'itemDescription', 'itemdescription1' );
        $this->addRequestMapping( 'itemAmount', 'itemamount1' );
        $this->addRequestMapping( 'itemTaxed', 'itemtaxed1' );

        //	Billing::GetInvoice
        $this->addRequestMapping( 'invoiceId', 'invoiceid', true, null, self::BILLING_API, 'getinvoice' );

		//	Billing::addCredit
        $this->addRequestMapping( 'clientId', 'clientid', true, null, self::BILLING_API, 'addcredit' );
        $this->addRequestMapping( 'description', 'description', true );
		$this->addRequestMapping( 'amount', 'amount', true );
	}

	/**
	 * Adds an order to the system
	 *
	 * @param array $arRequestData
	 */
	public function addOrder( $arRequestData = array() )
	{
		return $this->makeApiCall( self::BILLING_API, 'addorder', $arRequestData );
	}

	/**
	 * Gets orders from the system
	 *
	 * @param array $arRequestData
	 */
	public function getOrders( $arRequestData = array() )
	{
		return $this->makeApiCall( self::BILLING_API, 'getorders', $arRequestData );
	}

	/**
	 * Accepts an order in the system
	 *
	 * @param array $arRequestData
	 */
	public function acceptOrder( $arRequestData = array() )
	{
		return $this->makeApiCall( self::BILLING_API, 'acceptorder', $arRequestData );
	}

	/**
	 * Adds an item to an order
	 *
	 * @param array $arRequestData
	 */
	public function addBillableItem( $arRequestData = array() )
	{
		return $this->makeApiCall( self::BILLING_API, 'addbillableitem', $arRequestData );
	}

	/**
	 * Adds a credit to an account
	 *
	 * @param array $arRequestData
	 */
	public function addCredit( $arRequestData = array() )
	{
		return $this->makeApiCall( self::BILLING_API, 'addcredit', $arRequestData );
	}

	/**
	 * Creates an invoice
	 *
	 * @param array $arRequestData
	 */
	public function createInvoice( $arRequestData = array() )
	{
		return $this->makeApiCall( self::BILLING_API, 'createinvoice', $arRequestData );
	}

	/**
	 * Retrieves an invoice
	 *
	 * @param array $arRequestData
	 */
	public function getInvoice( $iInvoiceId )
	{
		$_arRequestData = array(
			'invoiceId' => $iInvoiceId,
		);

		return $this->makeApiCall( self::BILLING_API, 'getinvoice', $_arRequestData );
	}

}
