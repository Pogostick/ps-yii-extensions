<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * A base class for AR behaviors
 * 
 * @package 	psYiiExtensions
 * @subpackage 	behaviors
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 */
class CPSBaseActiveRecordBehavior extends CActiveRecordBehavior implements IPSBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* My name
	* @var string
	*/
	protected $m_sInternalName;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		//	Log it and check for issues...
		CPSLog::trace( 'pogostick.behaviors', __CLASS__ . ' constructed' );
		
		//	Preinitialize
		$this->preinit();
	}

	//********************************************************************************
	//* Interface Requirements
	//********************************************************************************
	
	/**
	 * Preinitialize the object
	 */
	public function preinit()
	{
		PS::createInternalName( $this );
	}
	
	/**
	 * Initialize
	 */
	public function init()
	{
	}
	
	/**
	* Get the internal name of our component
	* @returns string
	*/
	public function getInternalName() { return $this->m_sInternalName; }
	
	/**
	* Set the internal name of this component
	* @param string
	*/
	public function setInternalName( $sValue ) { $this->m_sInternalName = $sValue; }

}