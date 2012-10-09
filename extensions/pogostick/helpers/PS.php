<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
require_once 'CPSLog.php';
/**
 * Base functionality that I want in ALL my helper classes
 * 
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: PS.php 364 2010-01-04 06:33:35Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 * @todo Find a better way to do this
 */
class PS extends CPSWidgetHelper
{
	//*************************************************************************
	//*	Log Helpers
	//*************************************************************************

	/**
	 * @static
	 * @param string|Exception|null $category
	 * @param string|null $message
	 * @param array|null $options
	 * @param string|null $source
	 * @param string|null $language
	 * @return void
	 */
	public static function _error( $category, $message = null, $options = array(), $source = null, $language = null )
	{
		//	Allow direct sending of Exception
		if ( $category instanceof Exception )
		{
			$message = ( null === $message ? 'Exception: ' : $message . ' ' ) . $category->getMessage() . ' (Code: ' . $category->getCode() . ')';
		}

		CPSLog::error( $category, $message, $options, $source, $language );
	}

	/**
	 * @static
	 * @param $category
	 * @param null $message
	 * @param array $options
	 * @param null $source
	 * @param null $language
	 * @return void
	 */
	public static function _notice( $category, $message = null, $options = array(), $source = null, $language = null )
	{
		CPSLog::warning( $category, $message, $options, $source, $language );
	}

	/**
	 * @static
	 * @param $category
	 * @param null $message
	 * @param array $options
	 * @param null $source
	 * @param null $language
	 * @return void
	 */
	public static function _warn( $category, $message = null, $options = array(), $source = null, $language = null )
	{
		CPSLog::warning( $category, $message, $options, $source, $language );
	}

	/**
	 * @static
	 * @param $category
	 * @param null $message
	 * @param array $options
	 * @param null $source
	 * @param null $language
	 * @return void
	 */
	public static function _info( $category, $message = null, $options = array(), $source = null, $language = null )
	{
		CPSLog::info( $category, $message, $options, $source, $language );
	}

	/**
	 * @static
	 * @param $category
	 * @param null $message
	 * @param array $options
	 * @param null $source
	 * @param null $language
	 * @return void
	 */
	public static function _debug( $category, $message = null, $options = array(), $source = null, $language = null )
	{
		CPSLog::debug( $category, $message, $options, $source, $language );
	}

	/**
	 * @static
	 * @param $category
	 * @param null $message
	 * @param array $options
	 * @param null $source
	 * @param null $language
	 * @return void
	 */
	public static function _trace( $category, $message = null, $options = array(), $source = null, $language = null )
	{
		CPSLog::trace( $category, $message, $options, $source, $language );
	}

}