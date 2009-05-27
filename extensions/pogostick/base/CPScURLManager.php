<?php
/**
 * CPScURLManager class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPScURLManager provides a manager for the cURL objects
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Base
 * @since 1.0.0
 */
class CPScURLManager
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* The key to the array
	*
	* @var string
	*/
	protected $m_sKey;
	/**
	* The cURL object
	*
	* @var CPScURLObject
	*/
	protected $m_ocURL;

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	function __construct( $sKey )
	{
	    $this->m_sKey = $sKey;
	    $this->m_ocURL = CPScURLObject::getInstance();
	}

	//********************************************************************************
	//* Magic Methods
	//********************************************************************************

	public function __get( $sName )
	{
		$_arResponses = $this->m_ocURL->getResult( $this->m_sKey );
		return $_arResponses[ $sName ];
	}
}