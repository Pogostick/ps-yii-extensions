<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Hash code/password generators
 * 
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSHash.php 365 2010-01-08 17:38:06Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 */
class CPSHash implements IPSBase
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	* Our hash types
	*/
	const ALL = 0;
	const ALPHA_LOWER = 1;
	const ALPHA_UPPER = 2;
	const ALPHA = 3;
	const ALPHA_NUMERIC = 4;
	const ALPHA_LOWER_NUMERIC = 5;
	const NUMERIC = 6;
	const ALPHA_LOWER_NUMERIC_IDIOTPROOF = 7;

	/**
	* Hashing methods
	*/
	const MD5 = 1;
	const SHA1 = 2;
	const CRC32 = 18;
	
	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	* Our hash seeds
	* @var array
	*/
	protected static $m_arSeed = array(
		self::ALL => array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9'),
		self::ALPHA_LOWER => array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'),
		self::ALPHA_UPPER => array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'),
		self::ALPHA => array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'),
		self::ALPHA_NUMERIC => array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9'),
		self::ALPHA_LOWER_NUMERIC => array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9'),
		self::NUMERIC => array('0','1','2','3','4','5','6','7','8','9'),
		self::ALPHA_LOWER_NUMERIC_IDIOTPROOF => array('a','b','c','d','e','f','g','h','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z','2','3','4','5','6','7','8','9'),
	);
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Generates a unique hash code
	* 
	* @param int $iLength
	* @return string
	*/
	public static function generate( $iLength = 20, $eType = self::ALL )
	{
		if ( ! isset( self::$m_arSeed ) || ! is_array( self::$m_arSeed[ $eType ] ) ) return md5( time() . time() );

		$_sHash = null;
		$_iSize = count( self::$m_arSeed[ $eType ] ) - 1;
		for ( $_i = 0; $_i < $iLength; $_i++ ) $_sHash .= self::$m_arSeed[ $eType ][ mt_rand( 0, $_iSize ) ];

		return $_sHash;
	}
	
	/**
	* Generic hashing method. Will hash any string or generate a random hash and hash that!
	* 
	* @param string $sValueToHash
	* @param integer $eHashType
	* @param integer $iLength
	* @param boolean $bRawOutput
	* @return string
	*/
	public static function hash( $sValueToHash = null, $eHashType = self::SHA1, $iLength = 32, $bRawOutput = false )
	{
		$_sValue = ( null === $sValueToHash ) ? self::generate( $iLength ) : $sValueToHash;
		
		switch ( $eHashType )
		{
			case self::MD5:
				$_sHash = md5( $_sValue, $bRawOutput );
				break;
				
			case self::SHA1:
				$_sHash = sha1( $_sValue, $bRawOutput );
				break;
				
			case self::CRC32:
				$_sHash = crc32( $_sValue );
				break;

			default:
				$_sHash = hash( $eHashType, $_sValue, $bRawOutput );
				break;
		}
		
		return $_sHash;
	}
	
}