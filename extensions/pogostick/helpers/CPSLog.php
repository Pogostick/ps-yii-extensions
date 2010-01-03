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
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 *  
 * @filesource
 */
class CPSLog implements IPSBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Creates an 'info' log entry
	 * @param string $sCategory The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code use. See {@link CPhpMessageSource} for more interpretation about message category.
	 * @param string $sMessage The message to log
	 * @param string $sLevel The message level
	 * @param array $arParams Parameters to be applied to the message using <code>strtr</code>.
	 * @param string $sSource Which message source application component to use.
	 * @param string $sLanguage The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 */
	protected static function log( $sCategory, $sMessage, $sLevel = 'info', $arParams = array(), $sSource = null, $sLanguage = null )
	{
		Yii::log( Yii::t( $sCategory, $sMessage, $arParams, $sSource, $sLanguage ), $sLevel, $sCategory );
	}
	
	/**
	 * Creates an 'info' log entry
	 * @param mixed $sCategory The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code use. See {@link CPhpMessageSource} for more interpretation about message category.
	 * @param mixed $sMessage The message to log
	 * @param mixed $arParams Parameters to be applied to the message using <code>strtr</code>.
	 * @param mixed $sSource Which message source application component to use.
	 * @param mixed $sLanguage The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 */
	public static function info( $sCategory, $sMessage, $arParams = array(), $sSource = null, $sLanguage = null )
	{
		self::log( $sCategory, $sMessage, 'info', $arParams, $sSource, $sLanguage );
	}
	
	/**
	 * Creates an 'error' log entry
	 * @param mixed $sCategory The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code use. See {@link CPhpMessageSource} for more interpretation about message category.
	 * @param mixed $sMessage The message to log
	 * @param mixed $arParams Parameters to be applied to the message using <code>strtr</code>.
	 * @param mixed $sSource Which message source application component to use.
	 * @param mixed $sLanguage The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 */
	public static function error( $sCategory, $sMessage, $arParams = array(), $sSource = null, $sLanguage = null )
	{
		self::log( $sCategory, $sMessage, 'error', $arParams, $sSource, $sLanguage );
	}
	
	/**
	 * Creates an 'warning' log entry
	 * @param mixed $sCategory The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code use. See {@link CPhpMessageSource} for more interpretation about message category.
	 * @param mixed $sMessage The message to log
	 * @param mixed $arParams Parameters to be applied to the message using <code>strtr</code>.
	 * @param mixed $sSource Which message source application component to use.
	 * @param mixed $sLanguage The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 */
	public static function warning( $sCategory, $sMessage, $arParams = array(), $sSource = null, $sLanguage = null )
	{
		self::log( $sCategory, $sMessage, 'warning', $arParams, $sSource, $sLanguage );
	}
	
	/**
	 * Creates an 'trace' log entry
	 * @param mixed $sCategory The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code use. See {@link CPhpMessageSource} for more interpretation about message category.
	 * @param mixed $sMessage The message to log
	 * @param mixed $arParams Parameters to be applied to the message using <code>strtr</code>.
	 * @param mixed $sSource Which message source application component to use.
	 * @param mixed $sLanguage The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 */
	public static function trace( $sCategory, $sMessage, $arParams = array(), $sSource = null, $sLanguage = null )
	{
		self::log( $sCategory, $sMessage, 'trace', $arParams, $sSource, $sLanguage );
	}
	
}
