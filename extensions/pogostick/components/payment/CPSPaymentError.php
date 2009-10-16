<?php
/**
 * CPSPaymentError class file.
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
 * CPSPaymentError provides a generic error format for API calls
 *
 * @package psYiiExtensions
 * @subpackage Components
 */
class CPSPaymentError
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	protected $m_sResponseCode;
	protected $m_sTimeStamp;
	protected $m_sBuildVersion;
	protected $m_sShortMessage;
	protected $m_sLongMessage;
	protected $m_sErrorCode;
	protected $m_sSeverityCode;
	protected $m_arResponse = array();

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	public function getErrorCode() { return( $this->m_sErrorCode ); }
	public function setErrorCode( $sValue ) { $this->m_sErrorCode = $sValue; }
	public function getResponseCode() { return( $this->m_sResponseCode ); }
	public function setResponseCode( $sValue ) { $this->m_sResponseCode = $sValue; }
	public function getTimeStamp() { return( $this->m_sTimeStamp ); }
	public function setTimeStamp( $sValue ) { $this->m_sTimeStamp = $sValue; }
	public function getBuildVersion() { return( $this->m_sBuildVersion ); }
	public function setBuildVersion( $sValue ) { $this->m_sBuildVersion = $sValue; }
	public function getShortMessage() { return( $this->m_sShortMessage ); }
	public function setShortMessage( $sValue ) { $this->m_sShortMessage = $sValue; }
	public function getLongMessage() { return( $this->m_sLongMessage ); }
	public function setLongMessage( $sValue ) { $this->m_sLongMessage = $sValue; }
	public function getSeverityCode() { return( $this->m_sSeverityCode ); }
	public function setSeverityCode( $sValue ) { $this->m_sSeverityCode = $sValue; }
	public function getResponse() { return( $this->m_arResponse ); }
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	public function __construct( array $arResponse = null )
	{
		$this->m_arResponse = $arResponse;
	}
	
	/**
	* Creates a payment error object filling it from the mappings
	* 
	* @param CPSPaymentGatewayResponse $oResponse
	* @param array $arResponse Associative array of error codes and messages
	* @param integer $iNumber The error code to retrieve if multiple are allowed
	* @return CPSPaymentError
	*/
	public static function create( $oResponse, array $arResponse, $iNumber = 0 )
	{
		//	Create our return array...
		$_oError = null;
		
		if ( $oResponse )
		{
			//	Create ourself...
			$_oError = new CPSPaymentError( $arResponse );
			$_arMap = $oResponse->getErrorMap();
			
			//	Count error messages in response...
			foreach ( $_arMap as $_sKey => $_sMapKey )
			{
				//	The error # if supported
				$_sMapKeyToUse = ( false !== strpos( $_sMapKey, '{}' ) ) ? str_replace( '{}', "$iNumber", $_sMapKey ) : $_sMapKey;
					
				//	Map the value
				if ( isset( $arResponse[ $_sMapKeyToUse ] ) )
				{
					$_sKey = 'm_s' . $_sKey;
					if ( ! isset( $_oError->{$_sKey} ) ) $_oError->{$_sKey} = $arResponse[ $_sMapKeyToUse ];
				}             
				else
				{
					//	No more messages...
					$_oError = null;
					break;
				}
			}
		}
		
		return $_oError;
	}

}