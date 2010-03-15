<?php
/*
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
 * @version 	SVN $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 */
class CPSWHMCSClientApi extends CPSWHMCSApi
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/***
	* Initialize
	*/
	public function init()
	{
		//	Call daddy...
		parent::init();

		//	Client API
		$this->apiToUse = self::CLIENT_API;
		
		//	Create the base array
		$this->requestMap = array();

		//	Client::AddClient
		$this->addRequestMapping( 'addClient', 'addclient', true, null, self::CLIENT_API );
			$this->addRequestMapping( 'firstName', 'firstname', true );
			$this->addRequestMapping( 'lastName', 'lastname', true );
			$this->addRequestMapping( 'companyName', 'companyname' );
			$this->addRequestMapping( 'emailAddress', 'email', true );
			$this->addRequestMapping( 'address1', 'address1', true );
			$this->addRequestMapping( 'address2', 'address2' );
			$this->addRequestMapping( 'city', 'city', true );
			$this->addRequestMapping( 'state', 'state', true );
			$this->addRequestMapping( 'postalCode', 'postcode', true );
			$this->addRequestMapping( 'country', 'country', true );
			$this->addRequestMapping( 'phoneNumber', 'phonenumber', true );
			$this->addRequestMapping( 'password2', 'password2' );
			$this->addRequestMapping( 'currency', 'currency', true );
			$this->addRequestMapping( 'noEmail', 'noemail', true );
		
		//	Client::UpdateClient
		$this->addRequestMapping( 'updateClient', 'updateclient', true, null, self::CLIENT_API );
			$this->addRequestMapping( 'firstName', 'firstname', true );
			$this->addRequestMapping( 'lastName', 'lastname', true );
			$this->addRequestMapping( 'companyName', 'companyname' );
			$this->addRequestMapping( 'emailAddress', 'email', true );
			$this->addRequestMapping( 'address1', 'address1', true );
			$this->addRequestMapping( 'address2', 'address2' );
			$this->addRequestMapping( 'city', 'city', true );
			$this->addRequestMapping( 'state', 'state', true );
			$this->addRequestMapping( 'postalCode', 'postcode', true );
			$this->addRequestMapping( 'country', 'country', true );
			$this->addRequestMapping( 'phoneNumber', 'phonenumber', true );
			$this->addRequestMapping( 'password2', 'password2' );
			$this->addRequestMapping( 'creditBalance', 'credit' );
			$this->addRequestMapping( 'taxExempt', 'taxexempt' );
			$this->addRequestMapping( 'notes' );
			$this->addRequestMapping( 'cardType', 'cardtype' );
			$this->addRequestMapping( 'cardNumber', 'cardnum' );
			$this->addRequestMapping( 'cardExpireDate', 'expdate' );
			$this->addRequestMapping( 'startDate', 'startdate' );
			$this->addRequestMapping( 'cardIssueNumber', 'issuenumber' );
			$this->addRequestMapping( 'status' );

		//	Client::DeleteClient
		$this->addRequestMapping( 'deleteClient', 'deleteclient', true, null, self::CLIENT_API );
			$this->addRequestMapping( 'clientId', 'clientid', true );

		//	Client::AddInvoicePayment
		$this->addRequestMapping( 'addInvoicePayment', 'addinvoicepayment', true, null, self::CLIENT_API );
			$this->addRequestMapping( 'invoiceId', 'invoiceid', true );
			$this->addRequestMapping( 'transactionId', 'transid', true );
			$this->addRequestMapping( 'invoiceAmount', 'amount' );
			$this->addRequestMapping( 'fees', null, true );
			$this->addRequestMapping( 'gateway', null, true );
			$this->addRequestMapping( 'noEmail', 'noemail' );
			$this->addRequestMapping( 'paymentDate', 'date' );
	}

}