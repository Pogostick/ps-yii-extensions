<?php
/**
 * CPSThemeChangeRequestEvent class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSThemeChangeRequestEvent provides specialized events for {@link ThemeRoller} portlet
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @package 	psYiiExtensions
 * @subpackage 	events
 * @since 		1.0.6
 * 
 * @filesource
 * 
 * @property string $themeRequested The theme requested
 */
class CPSThemeChangeRequestEvent extends CEvent
{
	//********************************************************************************
	//* Members
	//********************************************************************************

	/**
	* The theme requested
	* @var string
	*/
	protected $m_sThemeRequested = null;

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	//	These are all read-only
	public function getThemeRequested() { return $this->m_sThemeRequested; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Constructor
	*
	* @param string $sThemeRequested
	* @param mixed $sender
	* @return CPSThemeChangeRequestEvent
	*/
	public function __construct( $sThemeRequested, $oSender = null )
	{
		parent::__construct( $oSender );
		$this->m_sThemeRequested = $sThemeRequested;
	}

}