<?php
/**
 * The master SnowFrame include file
 *
 * @package		snowframe
 * @subpackage	core
 * @author		Jerry Ablan <jablan@pogostick.com>
 * @copyright	Copyright &copy; 2010 Pogostick, LLC
 * @version		$Id$
 */

//	Include Files
//	Constants
//	Global Settings

/**
 * Loads necessary classes for operation
 *
 * @author		Jerry Ablan <jablan@pogostick.com.com>
 * @copyright	Copyright &copy; 2010 Pogostick, LLC
 */
class SnowFrame
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * User Preferences: SnowFrame user preferences index
	 */
	const	UP_HASH_UID = 100;

	/**
	 * Invite Placement: SnowFrame invitation placement
	 */
	const	AFTER_ADD = 0;
	const	AFTER_RESULTS = 1;
	const	APP_MANAGED = 2;

	/**
	 * Quiz Answer Order: The order of rendered questions
	 */
	const	AS_ENTERED = 0;
	const	RANDOM = 1;

	/**
	 * Platform types
	 */
	const 	FACEBOOK = 1000;
	const 	MYSPACE = 1001;
	const 	BEBO = 1002;
	const 	MEEBO = 1003;
	const 	ORKUT = 1004;
	const 	OPENSOCIAL = 1005;
	const 	FRIENDSTER = 1006;

	/**
	 * Ad Vendors
	 */
	const 	ADKNOWLEDGE = 0;
	const 	CUBICS = 1;
	const 	ADBRITE = 2;
	const 	GOOGLE = 3;
	const 	ROCKYOU = 4;

	/**
	 * Ad Type
	 */
	const	BANNER = 0;
	const	CROSSSELL = 1;
	const	INTEXT = 2;

	/**
	 * Quiz Category
	 */
	const 	GENERAL = 0;
	const 	TV = 1;
	const 	MOVIES = 2;
	const 	COMICS = 3;
	const 	NIGHTLIFE = 4;
	const 	DATING = 5;
	const 	QUOTES = 6;
	const 	BROADWAY = 7;

}

/**
 * Convenience class to reduce typing fatigue
 */
class SF extends SnowFrame
{
	//	Intentionally blank
}