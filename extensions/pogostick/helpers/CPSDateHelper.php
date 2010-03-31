<?php
/*
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
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 *  
 * @filesource
 */
class CPSDateHelper implements IPSBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Returns the differnce between the two dates
	* 
	* @param string $dtStart
	* @param string $dtEnd
	* @returns DateInterval
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
	* @param string $sTimeZone
	* @param string $sMyZone
	* @return int
	*/
	public static function zoneDiff( $sTimeZone, $sMyZone = null )
	{
		$sMyZone = PS::nvl( $sMyZone, date_default_timezone_get() );
		
		$_oDest = new DateTimeZone( $sTimeZone );
		$_oSrc = new DateTimeZone( $sMyZone );
		
		$_oDestTime = new DateTime( 'now', $_oDest );
		$_oSrcTime = new DateTime( 'now', $_oSrc );
		
		return $_oDest->getOffset( $_oSrcTime );
	}

	/**
	* Returns value (or current date) formatted
	* 
	* @param mixed $sDate
	* @return string
	*/
	public static function asDate( $sDate = null )
	{
		return self::format( $sDate, 'Y-m-d' );
	}
	
	/**
	* Returns value (or current date/time) formatted
	* 
	* @param mixed $sDate
	* @param string $sFormat The date() format. Defaults to 'Y-m-d H:i:s'
	* @return string
	*/
	public static function asDateTime( $sDate = null )
	{
		return self::format( $sDate, 'Y-m-d H:i:s' );
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
}