<?php
/**
 * CPSOAuthResponse class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSOAuthResponse encapsulates a response from and OAuth provider
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Components
 * @since 1.0.0
 */
class CPSOAuthResponse
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	protected $m_oResponse;

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	public function __construct( $oResp )
	{
		$this->m_oResponse = $oResp;
	}

	//********************************************************************************
	//* Magic Method Ovverides
	//********************************************************************************

	public function __get( $sKey )
	{
		$_iCode = $this->m_oResponse->code;

		if ( $_iCode < 200 || $_iCode > 299 )
			return false;

		parse_str( $this->m_oResponse->data, $_arResults );

		foreach ( $_arResults as $_sKey => $_oValue )
			$this->$_sKey = $_oValue;

		return $_arResults[ $sKey ];
	}

}