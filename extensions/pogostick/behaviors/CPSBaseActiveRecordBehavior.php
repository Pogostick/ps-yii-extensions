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
		//	Preinitialize
		$this->preinit();
		
		//	Raise our new event
		$this->onBeforeInit( new CEvent( $this ) );

		//	Log it and check for issues...
		Yii::trace( Yii::t( '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'pogostick.behaviors' );
	}

	//********************************************************************************
	//* Interface Requirements
	//********************************************************************************
	
	/**
	 * Preinitialize the object
	 */
	function preinit()
	{
		PS::createInternalName( $this );
		$this->attachEventHandler( 'onBeforeInit', array( $this, 'beforeInit' ) );
	}
	
	/**
	* Get the internal name of our component
	* @returns string
	*/
	function getInternalName() { return $this->m_sInternalName; }
	
	/**
	* Set the internal name of this component
	* @param string
	*/
	function setInternalName( $sValue ) { $this->m_sInternalName = $sValue; }
	
	//********************************************************************************
	//* Event Handlers
	//********************************************************************************
	
	/***
	 * beforeInit event. Called right before init() is called.
	 * @param CEvent $oEvent
	 */
	function beforeInit( $oEvent )
	{
	}

}