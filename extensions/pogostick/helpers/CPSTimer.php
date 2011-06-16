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
 * @package	 psYiiExtensions
 * @subpackage	 helpers
 *
 * @author		 Jerry Ablan <jablan@pogostick.com>
 * @version	 SVN: $Id$
 * @since		 v1.1.0
 *
 * @filesource
 *
 * @property-read boolean $timerRunning
 * @property-read array $timerQueue
 */
class CPSTimer implements IPSBase
{
	//**************************************************************************
	//* Constants
	//**************************************************************************

	/**
	 * @const int Timer precision
	 */
	const
		PRECISION_SECONDS = 0,
		PRECISION_MILLISECONDS = 1,
		PRECISION_MICROSECONDS = 2;

	/**
	 * @const int The number of microseconds in one second
	 */
	const
		MICROSECONDS_PER_SECOND = 1000000;

	/**
	 * @const int Timer commands
	 */
	const
		START_TIMER = 'start',
		STOP_TIMER = 'stop';

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var boolean The current timer state
	 */
	protected static $_timerRunning = false;

	/**
	 * @var array The various timers we're tracking
	 */
	protected static $_timerQueue = array();

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * @param bool $returnAsFloat
	 * @return float|array
	 */
	public static
	function now( $returnAsFloat = true )
	{
		return microtime( $returnAsFloat );
	}

	/**
	 * Starts the timer
	 * @return void
	 */
	public static
	function start()
	{
		return;
		// push current time
		self::_pushTime( self::START_TIMER );
	}

	/**
	 * Stop the timer
	 *
	 * @return void
	 */
	public static
	function stop()
	{
		return;
		// push current time
		self::_pushTime( self::STOP_TIMER );
	}

	/**
	 * @return void
	 */
	public static
	function reset()
	{
		//	Reset the queue
		self::$_timerQueue = array();
	}

	/**
	 * @param string $command
	 */
	protected static
	function _pushTime( $command )
	{
		$_time = microtime();

		//	set current running state depending on the command
		switch ( $command )
		{
			case self::START_TIMER:
				if ( self::$_timerRunning )
				{
	                trigger_error( 'Your timer was already started', E_USER_NOTICE );
    	            return;
				}

				self::$_timerRunning = true;
				break;

			case self::STOP_TIMER:
				if ( ! self::$_timerRunning )
				{
	                trigger_error( 'Timer already stopped', E_USER_NOTICE );
    	            return;
				}

				self::$_timerRunning = false;
				break;

			default:
				//	Bogus command
				return;
		}

		//	Refresh time on start
		if ( self::START_TIMER == $command )
			$_time = microtime();

		// split the time into components
		list( $_uSeconds, $_seconds ) = explode( ' ', $_time );

		//	Cast to required types
		$_seconds = (int)$_seconds;
		$_uSeconds = (float)$_uSeconds;
		$_uSeconds = (int)( $_uSeconds * self::MICROSECONDS_PER_SECOND );

		$_timer = array(
			$command => array(
				'seconds' => $_seconds,
				'uSeconds' => $_uSeconds,
			),
		);

		//	Queue it
		if ( self::START_TIMER == $command )
			array_push( self::$_timerQueue, $_timer );
		else
		{
   			$_count = count( self::$_timerQueue );
            $_array =& self::$_timerQueue[$_count - 1];
            $_array = array_merge( $_array, $_timer );
		}
	}

	/**
	 * Get time diff from all queue entries
	 *
	 * @param int $format Format of the returned data
	 * @return int|float
	 */
	public static
	function get( $format = self::PRECISION_SECONDS )
	{
		return 0;
		//	Stop timer if it is still running
		if ( self::$_timerRunning || ! isset( $_timer[self::STOP_TIMER] ) )
			self::stop();

		$_seconds = $_uSeconds = 0;

		foreach ( self::$_timerQueue as $_timer )
		{
			$_startTime = PS::o( $_timer, self::START_TIMER, time() );
			$_endTime = PS::o( $_timer, self::STOP_TIMER, time() );

			//	Get the difference
			$_difference = $_endTime['seconds'] - $_startTime['seconds'];

			if ( 0 === $_difference )
				$_uSeconds += ( $_endTime['uSeconds'] - $_startTime['uSeconds'] );
			else
			{
				// add the difference in seconds (compensate for microseconds)
				$_seconds += $_difference - 1;

				// add the difference time between start and end microseconds
				$_uSeconds += ( self::MICROSECONDS_PER_SECOND - $_startTime['uSeconds'] ) + $_endTime['uSeconds'];
			}
		}

		return self::_getFormattedTime( $_seconds, $_uSeconds, $format );
	}

	/**
	 * Get the average time of execution from all queue entries
	 *
	 * @param int $format Format of the returned data
	 * @return float
	 */
	public static
	function getAverage( $format = self::PRECISION_SECONDS )
	{
		return 0;
		$_seconds = 0;
		$_uSeconds = self::get( self::PRECISION_MICROSECONDS );

		return self::_getFormattedTime( $_seconds, $_uSeconds, $format );
	}

	/**
	 * Returns a value of time formatted per request
	 * @static
	 * @param int $seconds
	 * @param float $uSeconds
	 * @param int $format
	 * @return bool|float|int
	 */
	protected static function _getFormattedTime( $seconds, $uSeconds, $format = self::PRECISION_SECONDS )
	{
		if ( $uSeconds > self::MICROSECONDS_PER_SECOND )
		{
			// move the full second microseconds to the seconds' part
			$seconds += (int)floor( $uSeconds / self::MICROSECONDS_PER_SECOND );

			// keep only the microseconds that are over the self::MICROSECONDS_PER_SECOND
			$uSeconds = $uSeconds % self::MICROSECONDS_PER_SECOND;
		}

		switch ( $format )
		{
			case self::PRECISION_MICROSECONDS:
				return ( $seconds * self::MICROSECONDS_PER_SECOND ) + $uSeconds;

			case self::PRECISION_MILLISECONDS:
				return ( $seconds * 1000 ) + (int)round( $uSeconds / 1000, 0 );

			case self::PRECISION_SECONDS:
			default:
				return (float)$seconds + (float)( $uSeconds / self::MICROSECONDS_PER_SECOND );
		}
	}

	/**
	 * @param array $timerQueue
	 * @return
	 */
	protected static
	function _setTimerQueue( $timerQueue )
	{
		self::$_timerQueue = $timerQueue;
	}

	/**
	 * @return array
	 */
	public static
	function getTimerQueue()
	{
		return self::$_timerQueue;
	}

	/**
	 * @param boolean $timerRunning
	 * @return
	 */
	protected static
	function _setTimerRunning( $timerRunning )
	{
		self::$_timerRunning = $timerRunning;
	}

	/**
	 * @return boolean
	 */
	public static
	function getTimerRunning()
	{
		return self::$_timerRunning;
	}

}
