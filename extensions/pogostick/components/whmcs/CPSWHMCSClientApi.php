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
 * @version 	SVN $Id: CPSWHMCSClientApi.php -1   $
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
		$this->addRequestMapping( 'firstName', 'firstname', true, null, self::CLIENT_API, 'addclient' );
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
		$this->addRequestMapping( 'clientId', 'clientid', true, null, self::CLIENT_API, 'updateclient' );
		$this->addRequestMapping( 'status' );
		$this->addRequestMapping( 'firstName', 'firstname' );
		$this->addRequestMapping( 'lastName', 'lastname' );
		$this->addRequestMapping( 'companyName', 'companyname' );
		$this->addRequestMapping( 'emailAddress', 'email' );
		$this->addRequestMapping( 'address1', 'address1' );
		$this->addRequestMapping( 'address2', 'address2' );
		$this->addRequestMapping( 'city', 'city' );
		$this->addRequestMapping( 'state', 'state' );
		$this->addRequestMapping( 'postalCode', 'postcode' );
		$this->addRequestMapping( 'country', 'country' );
		$this->addRequestMapping( 'phoneNumber', 'phonenumber' );
		$this->addRequestMapping( 'password2', 'password2' );
		$this->addRequestMapping( 'creditBalance', 'credit' );
		$this->addRequestMapping( 'taxExempt', 'taxexempt' );
		$this->addRequestMapping( 'notes' );
		$this->addRequestMapping( 'cardType', 'cardtype' );
		$this->addRequestMapping( 'cardNumber', 'cardnum' );
		$this->addRequestMapping( 'cardExpireDate', 'expdate' );
		$this->addRequestMapping( 'startDate', 'startdate' );
		$this->addRequestMapping( 'cardIssueNumber', 'issuenumber' );

		//	Client::DeleteClient
		$this->addRequestMapping( 'clientId', 'clientid', true, null, self::CLIENT_API, 'deleteclient' );

		//	Client::AddInvoicePayment
		$this->addRequestMapping( 'invoiceId', 'invoiceid', true, null, self::CLIENT_API, 'addinvoicepayment' );
		$this->addRequestMapping( 'transactionId', 'transid', true );
		$this->addRequestMapping( 'invoiceAmount', 'amount' );
		$this->addRequestMapping( 'fees', null, true );
		$this->addRequestMapping( 'gateway', null, true );
		$this->addRequestMapping( 'noEmail', 'noemail' );
		$this->addRequestMapping( 'paymentDate', 'date' );

		//	Client::GetClientsDetails
		$this->addRequestMapping( 'clientId', 'clientid', true, null, self::CLIENT_API, 'getclientsdetails' );

		//	Client::GetClientsProducts
		$this->addRequestMapping( 'clientId', 'clientid', true, null, self::CLIENT_API, 'getclientsproducts' );
	}
	
	/**
	 * Adds a client to the system
	 * 
	 * @param array $arRequestData
	 */
	public function addClient( $arRequestData = array() )
	{
		if ( $arRequestData['country'] != 'US' && empty( $arRequestData['state'] ) )
			$arRequestData['state'] = 'NONE';

		return $this->makeApiCall( self::CLIENT_API, 'addclient', $arRequestData );
	}

	/**
	 * Updates a client record
	 * 
	 * @param array $arRequestData
	 */
	public function updateClient( $arRequestData = array() )
	{
		return $this->makeApiCall( self::CLIENT_API, 'updateclient', $arRequestData );
	}

	/**
	 * Deletes a client
	 * 
	 * @param integer $iClientId
	 */
	public function deleteClient( $iClientId )
	{
		$_arData = array(
			'clientId' => $iClientId,
		);

		return $this->makeApiCall( self::CLIENT_API, 'deleteclient', $_arData );
	}

	/**
	 * Retrieves a clients' details
	 *
	 * @param integer $iClientId
	 */
	public function getClientsDetails( $iClientId )
	{
		$_arData = array(
			'clientId' => $iClientId,
		);

		return $this->makeApiCall( self::CLIENT_API, 'getclientsdetails', $_arData );
	}

	/**
	 * Retrieves a clients' products
	 *
	 * @param integer $iClientId
	 */
	public function getClientsProducts( $iClientId )
	{
		$_arData = array(
			'clientId' => $iClientId,
		);

		return $this->makeApiCall( self::CLIENT_API, 'getclientsproducts', $_arData );
	}

	/**
	 * Records an invoice payment
	 * 
	 * @param array $arRequestData
	 */
	public function addInvoicePayment( $arRequestData = array() )
	{
		return $this->makeApiCall( self::CLIENT_API, 'addinvoicepayment', $arRequestData );
	}

}