<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link      http://www.pogostick.com Pogostick, LLC.
 * @license   http://www.pogostick.com/licensing
 */

/**
 * Implementors of this interface contain options.
 *
 * @package        psYiiExtensions
 * @subpackage     base
 *
 * @author         Jerry Ablan <jablan@pogostick.com>
 * @version        SVN $Id: IPSOptionContainer.php 358 2010-01-02 23:33:40Z jerryablan@gmail.com $
 * @since          v1.0.6
 *
 * @filesource
 */
interface IPSOptionContainer extends IPSBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Adds an option to the collection.
	 */
	function addOption( $key, $value = null, $pattern = null );

	/**
	 * Add an array of options to the option collection
	 */
	function addOptions( $options = array() );

	/**
	 * Retrieves an option value
	 */
	function getOption( $key, $defaultValue = null, $unsetValue = false );

	/**
	 * Returns all options as a key=>value pair associative array
	 *
	 * @return array
	 */
	function getOptions( $publicOnly = false );

	/**
	 * Sets a single option value
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	function setOption( $key, $value = null );

	/**
	 * Set options in a bulk manner. $arOptions should be array of key => value pairs.
	 */
	function setOptions( $options = array() );

	/**
	 * Unsets a single option
	 */
	function unsetOption( $key );

	/**
	 * Checks if the collection contains a key
	 */
	function contains( $key );

}