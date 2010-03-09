<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
 
/**
 * CPSPaymentEvent provides specialized events for {@link CPSPaymentGatewayResponse}
 * 
 * @package 	psYiiExtensions.payments
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.1.0
 * 
 * @filesource
 * 
 * @property $response The response from the gateway
 */
class CPSPaymentEvent extends CEvent
{
	//********************************************************************************
	//* Members
	//********************************************************************************

	/**
	* The response
	*
	* @var mixed
	*/
	protected $m_oResponse = null;

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************
	
	/**
	* Returns the response
	* @access public
	*/
	public function getResponse() { return $this->m_oResponse; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Constructor
	*
	* @param mixed $sender
	* @return CPSPaymentEvent
	*/
	public function __construct( $oResponse = null, $oSender = null )
	{
		parent::__construct( $oSender );

		$this->m_oResponse = $oResponse;
	}

}