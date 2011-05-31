<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSTimer provides helper functions for generic timing
 * 
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.1.0
 *  
 * @filesource
*/
class CPSTimer implements IPSBase
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * @param bool $returnAsFloat
	 * @return float|array
	 */
	public static function now( $returnAsFloat = true )
	{
		return microtime( $returnAsFloat );
	}
	
	/**
	 * @param string|int $id
	 * @return array
	 */
	public static function start( $id = null )
	{
		return array(
			'timerId' => $id,
			'start' => self::now(),
			'end' => null,
		);
	}

	/**
	 * @param array $timer
	 * @return float
	 */
	public static function stop( &$timer = array() )
	{
		if ( ! isset( $timer['start'], $timer['end'] ) )
			$timer = self::start();

		return floatval( $timer['end'] = self::now() ) - floatval( $timer['start'] );
	}

}