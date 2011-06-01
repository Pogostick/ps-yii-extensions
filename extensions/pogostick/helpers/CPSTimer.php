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
		START_TIMER = 0, STOP_TIMER = 1;

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
				if ( ! self::$_timerRunning )
				{
					self::$_timerRunning = true;
					$_time = microtime();
				}
				break;

			case self::STOP_TIMER:
				if ( self::$_timerRunning )
					self::$_timerRunning = false;
				break;

			default:
				//	Bogus command
				return;
		}

		// split the time into components
		list( $_microSeconds, $_seconds ) = explode( ' ', $_time );

		//	Cast to required types
		$_seconds = (int)$_seconds;
		$_microSeconds = (float)$_microSeconds;
		$_microSeconds = (int)( $_microSeconds * self::MICROSECONDS_PER_SECOND );

		$_timer = array(
			$command => array(
				'sec' => $_seconds,
				'usec' => $_microSeconds,
			),
		);

		//	Queue it
		if ( self::START_TIMER == $command )
			array_push( self::$_timerQueue, $_timer );
		else
		{
			$_count = count( self::$_timerQueue );
			$_lastTimer =& self::$_timerQueue[$_count - 1];
			$_lastTimer = array_merge( $_lastTimer, $_timer );
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
		//	Stop timer if it is still running
		if ( self::$_timerRunning )
			self::stop();

		$_seconds = $_microSeconds = 0;

		foreach ( self::$_timerQueue as $_timer )
		{
			$_startTime = $_timer[self::START_TIMER];
			$_endTime = $_timer[self::STOP_TIMER];

			//	Get the difference
			$_difference = $_endTime['sec'] - $_startTime['sec'];

			if ( 0 === $_difference )
				$_microSeconds += ( $_endTime['usec'] - $_startTime['usec'] );
			else
			{
				// add the difference in seconds (compensate for microseconds)
				$_seconds += $_difference - 1;

				// add the difference time between start and end microseconds
				$_microSeconds += ( self::MICROSECONDS_PER_SECOND - $_startTime['usec'] ) + $_endTime['usec'];
			}
		}

		return self::_getFormattedTime( $_seconds, $_microSeconds, $format );
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
		$_seconds = 0;
		$_count = count( self::$_timerQueue );
		$_microSeconds = self::get( self::PRECISION_MICROSECONDS );

		return self::_getFormattedTime( $_seconds, $_microSeconds, $format );
	}

	/**
	 * Returns a value of time formatted per request
	 * @static
	 * @param int $seconds
	 * @param float $microSeconds
	 * @param int $format
	 * @return bool|float|int
	 */
	protected static function _getFormattedTime( $seconds, $microSeconds, $format = self::PRECISION_SECONDS )
	{
		if ( $microSeconds > self::MICROSECONDS_PER_SECOND )
		{
			// move the full second microseconds to the seconds' part
			$seconds += (int)floor( $microSeconds / self::MICROSECONDS_PER_SECOND );

			// keep only the microseconds that are over the self::MICROSECONDS_PER_SECOND
			$microSeconds = $microSeconds % self::MICROSECONDS_PER_SECOND;
		}

		switch ( $format )
		{
			case self::PRECISION_MICROSECONDS:
				return ( $seconds * self::MICROSECONDS_PER_SECOND ) + $microSeconds;

			case self::PRECISION_MILLISECONDS:
				return ( $seconds * 1000 ) + (int)round( $microSeconds / 1000, 0 );

			case self::PRECISION_SECONDS:
			default:
				return (float)$seconds + (float)( $microSeconds / self::MICROSECONDS_PER_SECOND );
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
