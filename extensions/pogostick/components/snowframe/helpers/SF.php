<?php
/**
 * This file is part of the SnowFrame package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
 
/**
 * @package 	snowframe
 * @subpackage 	helpers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.1.0
 *  
 * @filesource
 */
class SF extends CSFHelperBase
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
	const	IP_AFTER_ADD = 0;
	const	IP_AFTER_RESULTS = 1;
	const	IP_APP_MANAGED = 2;
	
	/**
	 * Quiz Answer Order: The order of rendered questions
	 */
	const	QAO_AS_ENTERED = 0;
	const	QAO_RANDOM = 1;

	/**
	 * Platform types
	 */
	const 	PT_FACEBOOK = 1000;
	const 	PT_MYSPACE = 1001;
	const 	PT_BEBO = 1002;
	const 	PT_MEEBO = 1003;
	const 	PT_ORKUT = 1004;
	const 	PT_OPENSOCIAL = 1005;
	const 	PT_FRIENDSTER = 1006;
	
	/**
	 * Ad Vendors
	 */
	const 	AV_APPSAHOLIC = 0;
	const 	AV_ROCKYOU = 1;
	const 	AV_SOCIALBUX = 2;
	const 	AV_CUBICS = 3;
	const 	AV_ADBRITE = 4;
	const 	AV_GOOGLE = 5;
	const 	AV_ADCHAP = 6;
	const 	AV_ZOHARK = 7;
	
	/**
	 * Ad Type
	 */
	const	AT_BANNER = 0;
	const	AT_CROSSSELL = 1;
	
	/**
	 * Application Types
	 */
	const 	AT_GENERALQUIZ = 0;
	const 	AT_TVQUIZ = 1;
	const 	AT_MOVIESQUIZ = 2;
	const 	AT_COMICSQUIZ = 3;
	const 	AT_NIGHTLIFEQUIZ = 4;
	const 	AT_DATINGQUIZ = 5;
	const 	AT_GENERALQUOTES = 6;
	const 	AT_BROADWAYQUIZ = 7;
}