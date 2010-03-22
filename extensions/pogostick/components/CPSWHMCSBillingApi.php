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
	}

}