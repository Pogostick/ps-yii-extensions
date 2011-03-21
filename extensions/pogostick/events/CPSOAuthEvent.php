<?php
/**
 * CPSOAuthEvent class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSOAuthEvent provides specialized events for {@link CPSOAuthBehavior}
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSOAuthEvent.php 322 2009-12-23 23:51:37Z jerryablan@gmail.com $
 * @package 	psYiiExtensions
 * @subpackage 	events
 * 
 * @since 		1.0.3
 * 
 * @filesource
 * @property $token The OAuth token
 */
class CPSOAuthEvent extends CEvent
{
	//********************************************************************************
	//* Members
	//********************************************************************************

	/**
	* The token
	*
	* @var array
	*/
	protected $m_arToken = null;

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	//	These are all read-only

	public function getToken() { return $this->m_arToken; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Constructor
	*
	* @param mixed $sender
	* @return CPSOAuthEvent
	*/
	public function __construct( $arToken = null, $oSender = null )
	{
		parent::__construct( $oSender );

		$this->m_arToken = $arToken;
	}

}