<?php
/**
 * CPSOAuthJSONResponse class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSOAuthJSONResponse encapsulates a JSON response from and OAuth provider
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Components
 * @since 1.0.0
 */
class CPSOAuthJSONResponse extends CPSOAuthResponse
{
	//********************************************************************************
	//* Magic Method Ovverides
	//********************************************************************************

	public function __get( $sKey )
	{
    	$this->responseText = $this->m_oResponse->data;
    	$this->response = ( array )json_decode( $this->responseText, 1 );

		foreach ( $this->response as $_sKey => $_oValue )
			$this->$_sKey = $_oValue;

	    return $this->$sKey;
	}

}
