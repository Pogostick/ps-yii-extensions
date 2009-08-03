<?php
/**
 * CPSPaymentEvent class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com Pogostick Yii Extension Library
 * @package psYiiExtensions
 * @subpackage Events
 * @since psYiiExtensions v1.0.5
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSPaymentEvent provides specialized events for {@link CPSPaymentGatewayResponse}
 *
 * @package psYiiExtensions
 * @subpackage Events
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