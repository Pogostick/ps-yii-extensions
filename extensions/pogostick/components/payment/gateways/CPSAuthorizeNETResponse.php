<?php
/**
 * CPSAuthorizeNETResponse class file.
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
 * CPSAuthorizeNETResponse encapsulates a payment gateway response
 *
 * @package psYiiExtensions
 * @subpackage Components
 */
class CPSAuthorizeNETResponse extends CPSComponent
{
	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Build our object
	* 
	* @access public
	* @param mixed $oResponse
	*/
	public function __construct( $oResponse = null )
	{
		//	Phone home...
		parent::__construct( $oResponse );
		
		//	Set AVS codes
		$this->m_arAVSResponse = array( 
			'A' => 'Address (Street) matches, ZIP does not',
			'B' => 'Address information not provided for AVS check',
			'E' => 'AVS error',
			'G' => 'Non-U.S. Card Issuing Bank',
			'N' => 'No Match on Address (Street) or ZIP',
			'P' => 'AVS not applicable for this transaction',
			'R' => 'Retry – System unavailable or timed out',
			'S' => 'Service not supported by issuer',
			'U' => 'Address information is unavailable',
			'W' => 'Nine digit ZIP matches, Address (Street) does not',
			'X' => 'Address (Street) and nine digit ZIP match',
			'Y' => 'Address (Street) and five digit ZIP match',
			'Z' => 'Five digit ZIP matches, Address (Street) does not',
		);

		//	Set CVV codes
		$this->m_arCAVResponse = array( 
			'' => 'Not validated',
			'0' => 'CAVV not validated because erroneous data was submitted',
			'1' => 'CAVV failed validation',
			'2' => 'CAVV passed validation',
			'3' => 'CAVV validation could not be performed; issuer attempt incomplete',
			'4' => 'CAVV validation could not be performed; issuer system error',
			'5' => 'Reserved for future use',
			'6' => 'Reserved for future use',
			'7' => 'CAVV attempt – failed validation – issuer available (U.S.-issued card/non-U.S acquirer)',
			'8' => 'CAVV attempt – passed validation – issuer available (U.S.-issued card/non-U.S. acquirer)',
			'9' => 'CAVV attempt – failed validation – issuer unavailable (U.S.-issued card/non-U.S. acquirer)',
			'A' => 'CAVV attempt – passed validation – issuer unavailable (U.S.-issued card/non-U.S. acquirer)',
			'B' => 'CAVV passed validation, information only, no liability shift',
		);

		//	Add our component options
		$this->addOptions(
			array(
				'responseCode' => 'string',
				'responseSubcode' => 'string',
				'responseReasonCode' => 'string',
				'responseReasonText' => 'string',
				'authorizationCode' => 'string',
				'avsResponseCode' => 'string',
				'avsResponse' => 'string',
				'cavResponseCode' => 'string',
				'cavResponse' => 'string',
				'transactionId' => 'string',
			)
		);
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Constructs a response object and fills it with data
	* 
	* @param mixed $oResponse
	* @access public
	* @static
	* @returns CPSAuthorizeNETResponse
	*/
	public static function processResponse( $oResponse, $sClass = __CLASS__ )
	{
		//	Override to do stuff here
		return new $sClass();
	}

}
