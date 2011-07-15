<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Date helper methods
 *
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSDateHelper.php 377 2010-03-31 16:16:02Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *
 * @filesource
 */
class CPSDateHelper implements IPSBase
{
	//*************************************************************************
	//* Private Members
	//*************************************************************************

	/**
	 * @staticvar array
	 */
	protected static $_quarterBounds = array(
		1 => array( 'Jan 01', 'Mar 31' ),
		2 => array( 'Apr 01', 'Jun 30' ),
		3 => array( 'Jul 01', 'Sep 30' ),
		4 => array( 'Oct 01', 'Dec 31' ),
	);

	/**
	 * @staticvar array
	 */
	protected static $_quarterMap = array(
		1 => 1, 2 => 1, 3 => 1,
		4 => 2, 5 => 2, 6 => 2,
		7 => 3, 8 => 3, 9 => 3,
		10 => 4, 11 => 4, 12 => 4,
	);

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Returns the differnce between the two dates
	*
	* @param string $dtStart
	* @param string $dtEnd
	* @return DateInterval
	*/
	public static function dateDiff( $dtStart, $dtEnd = null, $bAbsolute = false )
	{
		$_dtStart = new DateTime( $dtStart );
		$_dtEnd = new DateTime( PS::nvl( $dtEnd, date( 'Y-m-d H:i:s' ) ) );
		return $_dtEnd->diff( $_dtStart, $bAbsolute );
	}

	/**
	* Returns the time difference in seconds between two time zones
	*
	* @param string $timeZone
	* @param string $myTimeZone
	* @return int
	*/
	public static function zoneDiff( $timeZone, $myTimeZone = null )
	{
		if ( null === $myTimeZone )
			$myTimeZone = date_default_timezone_get();

		$_targetZone = new DateTimeZone( $timeZone );
		$_sourceZone = new DateTimeZone( $myTimeZone );

		$_targetZoneTime = new DateTime( 'now', $_targetZone );
		$_sourceZoneTime = new DateTime( 'now', $_sourceZone );

		return $_targetZone->getOffset( $_sourceZoneTime );
	}

	/**
	* Returns value (or current date) formatted
	*
	* @param mixed $date
	* @return string
	*/
	public static function asDate( $date = null )
	{
		return self::format( $date, 'Y-m-d' );
	}

	/**
	* Returns value (or current date/time) formatted
	*
	* @param mixed $date
	* @param string $sFormat The date() format. Defaults to 'Y-m-d H:i:s'
	* @return string
	*/
	public static function asDateTime( $date = null )
	{
		return self::format( $date, 'Y-m-d H:i:s' );
	}

	/**
	 * Formats a date
	 * @param mixed $dtDate
	 * @param string $sFormat
	 * @return string
	 */
	public static function format( $dtDate = null, $sFormat = 'Y-m-d H:i:s' )
	{
		return date( $sFormat, $dtDate ? strtotime( $dtDate ) : time() );
	}

	/**
	 * Checks to see if a date/time is valid
	 * @param string $dtValue
	 * @return bool
	 */
	public static function isValid( $dtValue )
	{
		if ( preg_match( "/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $dtValue, $_arMatches ) )
		{
        	if ( checkdate( $_arMatches[2], $_arMatches[3], $_arMatches[1] ) )
            	return true;
        }

	    return false;
	}

	/**
	 * Given a date, return the quarter in which it resides. Returns 1 - 4
	 * @param string|date|null $date
	 * @return int
	 */
	public static function getQuarterNumber( $date = null )
	{
		if ( null !== $date && is_numeric( $date ) && $date >= 1 && $date <= 4 )
			return $date;

		$_month = date( 'n', ( null === $date ? time() : strtotime( $date ) ) );

//		CPSLog::trace( 'Month of date: ' . $date . ' is ' . $_month );

		return self::$_quarterMap[ $_month ];
	}

	/**
	 * Returns the boundaries of a quarter based on the date passed in.
	 * If null, current date is used. Date format of returned data is "M d"
	 * @param string|date|null $date
	 * @return array
	 */
	public static function getQuarterBounds( $date = null )
	{
		return self::$_quarterBounds[ self::getQuarterNumber( $date ) ];
	}
}