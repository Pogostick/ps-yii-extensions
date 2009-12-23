<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPScURLManager provides a manager for the cURL objects
 * 
 * @todo This class is not fully implemented.
 * 
 * @package 	psYiiExtensions
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.0
 * 
 * @filesource
 */
class CPScURLManager implements IPogostick
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