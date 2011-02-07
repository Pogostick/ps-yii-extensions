<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Logging helper methods
 * 
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSLog.php 401 2010-08-31 21:04:18Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 */
class CPSLog implements IPSBase
{
	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * @var boolean If true, all applicable log entries will be echoed to the screen
	 */
	public static $echoData = false;
	
	/**
	 * @var string Prepended to each log entry before writing.
	 */
	public static $prefix = null;
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Creates an 'info' log entry
	 * @param string $category The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code use. See {@link CPhpMessageSource} for more interpretation about message category.
	 * @param string $message The message to log
	 * @param string $level The message level
	 * @param array $options Parameters to be applied to the message using <code>strtr</code>.
	 * @param string $source Which message source application component to use.
	 * @param string $language The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 */
	protected static function log( $category, $message, $level = 'info', $options = array(), $source = null, $language = null )
	{
		$_logEntry = self::$prefix . Yii::t( $category, $message, $options, $source, $language );

		if ( self::$echoData )
		{
			echo date( 'Y.m.d h.i.s' ) . '[' . strtoupper( $level[0] ) . '] ' . '[' . sprintf( '%-40s', $category ) . '] ' . $_logEntry . '<br />';
			flush();
		}

		Yii::log( $_logEntry, $level, $category );
	}
	
	/**
	 * Creates an 'info' log entry
	 * @param mixed $category The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code use. See {@link CPhpMessageSource} for more interpretation about message category.
	 * @param mixed $message The message to log
	 * @param mixed $options Parameters to be applied to the message using <code>strtr</code>.
	 * @param mixed $source Which message source application component to use.
	 * @param mixed $language The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 */
	public static function info( $category, $message, $options = array(), $source = null, $language = null )
	{
		self::log( $category, $message, 'info', $options, $source, $language );
	}
	
	/**
	 * Creates an 'error' log entry
	 * @param mixed $category The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code use. See {@link CPhpMessageSource} for more interpretation about message category.
	 * @param mixed $message The message to log
	 * @param mixed $options Parameters to be applied to the message using <code>strtr</code>.
	 * @param mixed $source Which message source application component to use.
	 * @param mixed $language The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 */
	public static function error( $category, $message, $options = array(), $source = null, $language = null )
	{
		self::log( $category, $message, 'error', $options, $source, $language );
	}
	
	/**
	 * Creates an 'warning' log entry
	 * @param mixed $category The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code use. See {@link CPhpMessageSource} for more interpretation about message category.
	 * @param mixed $message The message to log
	 * @param mixed $options Parameters to be applied to the message using <code>strtr</code>.
	 * @param mixed $source Which message source application component to use.
	 * @param mixed $language The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 */
	public static function warning( $category, $message, $options = array(), $source = null, $language = null )
	{
		self::log( $category, $message, 'warning', $options, $source, $language );
	}
	
	/**
	 * Creates an 'trace' log entry
	 * @param mixed $category The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code use. See {@link CPhpMessageSource} for more interpretation about message category.
	 * @param mixed $message The message to log
	 * @param mixed $options Parameters to be applied to the message using <code>strtr</code>.
	 * @param mixed $source Which message source application component to use.
	 * @param mixed $language The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 */
	public static function trace( $category, $message, $options = array(), $source = null, $language = null )
	{
		self::log( $category, $message, 'trace', $options, $source, $language );
	}
	
	/**
	 * Creates an 'api' log entry
	 * @param string $apiCall The API call made
	 * @param mixed $response The API response to log
	 */
	public static function api( $apiCall, $response )
	{
		self::log( $apiCall, PHP_EOL . print_r( $response, true ) . PHP_EOL, 'api' );
	}
	
	/**
	 * Creates a 'debug' log entry
	 * @param mixed $message The message to log
	 * @param mixed $category The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code use. See {@link CPhpMessageSource} for more interpretation about message category.
	 */
	public static function debug( $message, $category )
	{
		self::log( $category, $message, 'debug' );
	}
	
}
