<?php
/**
 * CPSPaypalResponse class file.
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
 * CPSPaypalResponse encapsulates a Paypal response
 *
 * @package psYiiExtensions
 * @subpackage Components
 */
class CPSPaypalResponse extends CPSPaymentGatewayResponse
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
		parent::__construct( $oResopnse );

		//	Set the error mapping
		$this->setErrorMap( 
			array( 
				'ResponseCode' => 'ACK',
				'TimeStamp' => 'TIMESTAMP',
				'Version' => 'VERSION',
				'Build' => 'BUILD',
				'ErrorCode' => 'L_ERRORCODE{}',
				'ShortMessage' => 'L_SHORTMESSAGE{}',
				'LongMessage' => 'L_LONGMESSAGE{}',
				'SeverityCode' => 'L_SEVERITYCODE{}',
			)
		);
	}
	
	/**
	* Parses the errors/warnings from a Paypal response and returns them in a nice array
	* 
	* @access protected
	* @param array $arResponse
	* @param boolean $bWarningsOnly
	* @returns array
	*/
	protected function parseErrors( array $arResponse, $bWarningsOnly = false )
	{
		$_arError = array();
		$_i = 0;
		
		//	Get all the errors into an array...
		while ( true )
		{
			//	No more errors? Bail!
			if ( ! isset( $arResponse[ "L_ERRORCODE{$_i}" ] ) )
				break;

			$_sSeverity = strtolower( PS::o( $arResponse, "L_SEVERITYCODE{$_i}" ) );
				
			//	Ignore non-warnings if requested
			if ( ( $_sSeverity != 'warning' || $_sSeverity == 'successwithwarning' || $_sSeverity == 'failurewithwarning' ) && $bWarningOnly )
				continue;
				
			$_oError = new CPSPaymentError();
			$_oError->setErrorCode( PS::o( $arResponse, "L_ERRORCODE{$_i}" ) );
			$_oError->setSeverityCode( $_sSeverity );
			$_oError->setShortMessage( PS::o( $arResponse, "L_SHORTMESSAGE{$_i}" ) );
			$_oError->setLongMessage( PS::o( $arResponse, "L_LONGMESSAGE{$_i}" ) );
			
			//	Add to array
			$_arError[] = $_oError;
			
			//	Increment counter
			$_i++;
		}
		
		//	Return the list
		return $_arError;
	}

	/**
	* Parse the response from Paypal and fills in the errorList and cookedResponse
	* 	
	* @access protected
	* @param array $arResponse
	* @returns CPSPay
	*/
	protected function parseResponse( array $arResponse )
	{
		$_bSuccess = false;
		$_arIgnore = array( 'ACK', 'TIMESTAMP', 'CORRELATIONID', 'VERSION', 'BUILD', 'L_ERRORCODE*', 'L_SEVERITYCODE*', 'L_SHORTMESSAGE*', 'L_LONGMESSAGE*' );
		
		//	Pull out standard header items...
		$_sAck = strtolower( PS::o( $arResponse, 'ACK' ) );
		$_sTimeStamp = PS::o( $arResponse, 'TIMESTAMP' );
		$_sCorrelationId = PS::o( $arResponse, 'CORRELATIONID' );		
		$_sVersion = PS::o( $arResponse, 'VERSION' );
		$_sBuild = PS::o( $arResponse, 'BUILD' );
		
		switch ( $_sAck )
		{
			case 'success':
			case 'successwithwarning':
				$_bSuccess = true;
				
				//	Get our error list...
				$this->m_arErrorList = $this->parseErrors( $arResponse, true );
				
				//	Create a cooked response array...
				$_arKeys = array_keys( $arResponse );
				foreach ( $_arIgnore as $_sKey )
				{
					if ( $_sKey{strlen($_sKey)-1} == '*' )
					{
						$_bPartial = true;
						$_sKey = substr( $_sKey, 0, strlen( $_sKey ) - 1 );
					}
					
					//	Remove ignored keys from response...
					foreach ( $_arKeys as $_iIndex => $_sRespKey )
					{
						if ( ( ! $_bPartial && 0 == strcasecmp( $_sRespKey, $_sKey ) ) || ( $_bPartial && false !== stripos( $_sRespKey, $_sKey ) ) )
							unset( $_arKeys[ $_iIndex ] );
					}
				}

				//	Clean up the array... probably a better way to do this...
				$_arTemp = array();
				foreach ( $_arKeys as $_sKey )
					$_arTemp[ $_sKey ] = $arResponse[ $_sKey ];
					
				$this->cookedResponse = $_arTemp;
				break;
				
			case 'failure':
			case 'failurewithwarning':
			case 'warning':
				$this->m_arErrorList = $this->parseErrors( $arResponse );
				break;
		}

		//	Set flags accordingly		
		return $this->apiCallSuccess = $_bSuccess;
	}

	//********************************************************************************
	//* Static Methods
	//********************************************************************************
	
	/**
	* Parses the response from the Google Checkout gateway and raises the proper event
	* 
	* @static
	* @access public
	* @param string $oResponse
	* @returns CPSPaypalResponse
	*/
	public static function create( $sResponse, $sClass = __CLASS__ )
	{
		//	Parent creates my object
		$_oResp = null;
		
		//	Paypal sends an HTTP style response
		parse_str( urldecode( $sResponse ), $_arResponse );
		
		//	Success? Set flag and return...
		if ( is_array( $_arResponse ) ) 
		{
			$_oResp = parent::create( $sResponse, $sClass );
			$_oResp->parseResponse( $_arResponse );
		}
			
		//	Send it back
		return $_oResp;
	}

}
