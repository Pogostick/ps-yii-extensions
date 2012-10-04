<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link      http://www.pogostick.com Pogostick, LLC.
 * @license   http://www.pogostick.com/licensing
 * @filesource
 */
/**
 * This interface defines methods required for base pYe objects.
 *
 * @package           psYiiExtensions
 * @subpackage        base.interfaces
 *
 * @author            Jerry Ablan <jablan@pogostick.com>
 * @version           SVN $Id: IPSComponent.php 358 2010-01-02 23:33:40Z jerryablan@gmail.com $
 * @since             v1.0.6
 */
interface IPSComponent extends IPSBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Preinitialize the object
	 */
	function preinit();

	/**
	 * Get the internal name of our component
	 *
	 * @return string
	 */
	function getInternalName();

	/**
	 * Set the internal name of this component
	 *
	 * @param string
	 */
	function setInternalName( $value );

}