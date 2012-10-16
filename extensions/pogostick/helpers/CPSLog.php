<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2010 Pogostick, LLC
 * @link      http://www.pogostick.com Pogostick, LLC.
 * @license   http://www.pogostick.com/licensing
 */

/**
 * Logging helper methods
 *
 * @package        psYiiExtensions
 * @subpackage     helpers
 *
 * @author         Jerry Ablan <jablan@pogostick.com>
 * @version        SVN: $Id: CPSLog.php 401 2010-08-31 21:04:18Z jerryablan@gmail.com $
 * @since          v1.0.6
 *
 * @filesource
 */
class CPSLog implements IPSBase
{
	//**************************************************************************
	//* Constants
	//**************************************************************************

	/**
	 * @const string The string to use for each log entry indentation
	 */
	const
		INDENT_STRING = '  ';
	/**
	 * @const int Standard ANSI console attributes
	 */
	const
		ATTR_RESET = 0, ATTR_BRIGHT = 1, ATTR_DIM = 2, ATTR_UNDERSCORE = 4, ATTR_BLINK = 5, ATTR_REVERSE = 7, ATTR_HIDDEN = 8;
	/**
	 * @const int Standard ANSI foreground color codes
	 */
	const
		FG_BLACK = 30, FG_RED = 31, FG_GREEN = 32, FG_YELLOW = 33, FG_BLUE = 34, FG_MAGENTA = 35, FG_CYAN = 36, FG_WHITE = 37;
	/**
	 * @const int Standard ANSI background color codes
	 */
	const
		BG_BLACK = 40, BG_RED = 41, BG_GREEN = 42, BG_YELLOW = 43, BG_BLUE = 44, BG_MAGENTA = 45, BG_CYAN = 46, BG_WHITE = 47;
	/**
	 * @const string Various color presets
	 */
	const
		COLOR_WHITE = 0, COLOR_GREEN = 1, COLOR_YELLOW = 2, COLOR_RED = 3, COLOR_BOLD = 4, COLOR_BLUE = 5, COLOR_CYAN = 6, COLOR_MAGENTA = 7;
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
	/**
	 * @var integer The base level for getting source of log entry
	 */
	public static $baseLevel = 3;
	/**
	 * @var integer The current indent level
	 */
	public static $currentIndent = 0;
	/**
	 * @var string
	 */
	protected static $_defaultLevelIndicator = '.';
	/**
	 * @var array
	 */
	protected static $_levelIndicators = array(
		'info'    => '*',
		'notice'  => '?',
		'warning' => '-',
		'error'   => '!',
	);
	/**
	 * @var int The size of the category field in the log entries
	 */
	protected static $_categoryWindowWidth = false;
	/**
	 * @var array fancy log output
	 */
	protected $_logColors = array(
		self::COLOR_WHITE   => array( '01;37m', '00m' ),
		self::COLOR_GREEN   => array( '01;32m', '00m' ),
		self::COLOR_YELLOW  => array(
			'01;33m',
			'00m'
		),
		self::COLOR_RED     => array( '01;31m', '00m' ),
		self::COLOR_BOLD    => array( '01m', '00m' ),
		self::COLOR_BLUE    => array(
			'01;34m',
			'00m'
		),
		self::COLOR_CYAN    => array( '01;36m', '00m' ),
		self::COLOR_MAGENTA => array( '01;35m', '00m' ),
	);
	/**
	 * @var string
	 */
	protected $_escapeSequence = "\033[";

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Creates an 'info' log entry
	 *
	 * @param string $category The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code
	 *                         use. See {@link
	 *                         CPhpMessageSource} for more interpretation about message category.
	 * @param string $message  The message to log
	 * @param string $level    The message level
	 * @param array  $options  Parameters to be applied to the message using <code>strtr</code>.
	 * @param string $source   Which message source application component to use.
	 * @param string $language The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 *
	 * @return string
	 */
	public static function log( $category, $message = null, $level = 'info', $options = array(), $source = null, $language = null )
	{
		$_label = 'category';

		//	Allow null categories
		if ( null !== $category && null === $message )
		{
			$message = $category;
			$category = null;
		}

		if ( null === $category )
		{
			$category = self::_getCallingMethod();
			$_label = 'calling_method';
		}

		if ( self::$_categoryWindowWidth )
		{
			$category = substr( $category, ( -1 * self::$_categoryWindowWidth ) );
		}

		//	Get the indent, if any
		$_unindent = ( 0 > ( $_newIndent = self::_processMessage( $message ) ) );

		$_levelList = explode( '|', $level );
		$_logEntry = $message;

		//	Handle writing to multiple levels at once.
		foreach ( $_levelList as $_level )
		{
			$_indicator = ( in_array( $_level, self::$_levelIndicators ) ? self::$_levelIndicators[$_level] : self::$_defaultLevelIndicator );

			//	Indent...
			$_tempIndent = self::$currentIndent;

			if ( $_unindent )
			{
				$_tempIndent--;
			}

			if ( $_tempIndent < 0 )
			{
				$_tempIndent = 0;
			}

			$_logEntry = str_repeat( self::INDENT_STRING, $_tempIndent ) . $_indicator . ' ' . $message;

			try
			{
				//	Echo if we're CLI && user wants it...
				if ( 'cli' == PHP_SAPI && self::$echoData )
				{
					echo
						date( 'M j H:i:s' ) . ' [' . strtoupper( substr( $_level, 0, 4 ) ) . '] ' .
						$_logEntry . ' {"' . $_label . '":"' . $category . '"}' . PHP_EOL;

					flush();
				}

				//	Flush immediately...
				Yii::getLogger()->autoFlush = 1;
				Yii::log( $_logEntry, $_level, $category );
			}
			catch ( Exception $_ex )
			{
				@error_log( 'CPSLog::_log exception: ' . $_ex->getMessage() );
				@error_log( '             Log Entry: ' . $_logEntry );
			}
		}

		//	Set indent level...
		self::$currentIndent += $_newIndent;

		return $_logEntry;
	}

	/**
	 * Creates an 'info' log entry
	 *
	 * @param mixed $category The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code
	 *                        use. See {@link
	 *                        CPhpMessageSource} for more interpretation about message category.
	 * @param mixed $message  The message to log
	 * @param mixed $options  Parameters to be applied to the message using <code>strtr</code>.
	 * @param mixed $source   Which message source application component to use.
	 * @param mixed $language The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 *
	 * @return string
	 */
	public static function info( $category, $message = null, $options = array(), $source = null, $language = null )
	{
		return self::log( $category, $message, 'info', $options, $source, $language );
	}

	/**
	 * Creates an 'error' log entry
	 *
	 * @param mixed $category The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code
	 *                        use. See {@link
	 *                        CPhpMessageSource} for more interpretation about message category.
	 * @param mixed $message  The message to log
	 * @param mixed $options  Parameters to be applied to the message using <code>strtr</code>.
	 * @param mixed $source   Which message source application component to use.
	 * @param mixed $language The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 *
	 * @return string
	 */
	public static function error( $category, $message = null, $options = array(), $source = null, $language = null )
	{
		return self::log( $category, $message, 'error', $options, $source, $language );
	}

	/**
	 * Creates an 'warning' log entry
	 *
	 * @param mixed $category The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code
	 *                        use. See {@link
	 *                        CPhpMessageSource} for more interpretation about message category.
	 * @param mixed $message  The message to log
	 * @param mixed $options  Parameters to be applied to the message using <code>strtr</code>.
	 * @param mixed $source   Which message source application component to use.
	 * @param mixed $language The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 *
	 * @return string
	 */
	public static function warning( $category, $message = null, $options = array(), $source = null, $language = null )
	{
		self::log( $category, $message, 'warning', $options, $source, $language );
	}

	/**
	 * Creates an 'trace' log entry
	 *
	 * @param mixed $category The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code
	 *                        use. See {@link
	 *                        CPhpMessageSource} for more interpretation about message category.
	 * @param mixed $message  The message to log
	 * @param mixed $options  Parameters to be applied to the message using <code>strtr</code>.
	 * @param mixed $source   Which message source application component to use.
	 * @param mixed $language The target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 *
	 * @return string
	 */
	public static function trace( $category, $message = null, $options = array(), $source = null, $language = null )
	{
		if ( defined( 'PYE_TRACE_LEVEL' ) || defined( 'YII_DEBUG' ) || defined( 'YII_TRACE_LEVEL' ) )
		{
			return self::log( $category, $message, 'trace', $options, $source, $language );
		}

		return null;
	}

	/**
	 * Creates an 'api' log entry
	 *
	 * @param string $apiCall  The API call made
	 * @param mixed  $response The API response to log
	 *
	 * @return string
	 */
	public static function api( $apiCall, $response = null )
	{
		return self::log( $apiCall, PHP_EOL . print_r( $response, true ) . PHP_EOL, 'api' );
	}

	/**
	 * Creates a 'debug' log entry
	 *
	 * @param mixed $category The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code
	 *                        use. See {@link
	 *                        CPhpMessageSource} for more interpretation about message category.
	 * @param mixed $message  The message to log
	 *
	 * @return string
	 */
	public static function debug( $category, $message = null )
	{
		return self::log( $category, $message, 'debug' );
	}

	/**
	 * Creates an user-defined log entry
	 *
	 * @param mixed  $message  The message
	 * @param string $level    The message level
	 * @param mixed  $category The message category. Please use only word letters. Note, category 'yii' is reserved for Yii framework core code
	 *                         use. See {@link
	 *                         CPhpMessageSource} for more interpretation about message category.
	 *
	 * @return string
	 */
	public static function write( $message, $level = null, $category = null )
	{
		return self::log( $category, $message, $level );
	}

	/**
	 * Safely decrements the current indent level
	 *
	 * @param int $howMuch
	 */
	public static function decrementIndent( $howMuch = 1 )
	{
		self::$currentIndent -= $howMuch;

		if ( self::$currentIndent < 0 )
		{
			self::$currentIndent = 0;
		}
	}

	//*************************************************************************
	//* Private Methods
	//*************************************************************************

	/**
	 * Returns the name of the method that made the call
	 *
	 * @param integer $level The level of the call
	 *
	 * @return string
	 */
	protected static function _getCallingMethod( $level = null )
	{
		$_className = get_class();
		$level = ( null === $level ? self::$baseLevel : $level );

		try
		{
			$_trace = debug_backtrace();

			while ( $level >= 0 && isset( $_trace[$level] ) )
			{
				if ( null === ( $_caller = PS::o( $_trace, $level ) ) )
				{
					break;
				}

				//	If we see our self, then we must go again
				if ( null !== ( $_class = PS::o( $_caller, 'class' ) ) && $_class != $_className )
				{
					return $_class . '::' . PS::o( $_caller, 'function' );
				}

				//	If we see our self, then we must go again
				if ( $_className != basename( PS::o( $_caller, 'file' ) ) )
				{
					return basename( PS::o( $_caller, 'file' ) ) . '::' . PS::o( $_caller, 'function' ) . ' (Line ' . PS::o( $_caller,
						'line' ) . ')';
				}

				$level--;
			}
		}
		catch ( Exception $_ex )
		{
			//	Error logging shouldn't create more errors...
		}

		return null;
	}

	/**
	 * Processes the indent level for the messages
	 *
	 * @param string $message
	 *
	 * @return integer The indent difference AFTER this message
	 */
	protected static function _processMessage( &$message )
	{
		$_newIndent = 0;

		switch ( substr( $message, 0, 2 ) )
		{
			case '>>':
				$_newIndent = 1;
				$message = trim( substr( $message, 2 ) );
				break;

			case '<<':
				$_newIndent = -1;
				$message = trim( substr( $message, 2 ) );
				break;
		}

		return $_newIndent;
	}

	//*************************************************************************
	//* Properties
	//*************************************************************************

	/**
	 * @param $defaultLevelIndicator
	 */
	public static function setDefaultLevelIndicator( $defaultLevelIndicator )
	{
		self::$_defaultLevelIndicator = $defaultLevelIndicator;
	}

	/**
	 * @return string
	 */
	public static function getDefaultLevelIndicator()
	{
		return self::$_defaultLevelIndicator;
	}

	/**
	 * @param $levelIndicators
	 */
	public static function setLevelIndicators( $levelIndicators )
	{
		self::$_levelIndicators = $levelIndicators;
	}

	/**
	 * @return array
	 */
	public static function getLevelIndicators()
	{
		return self::$_levelIndicators;
	}

	/**
	 * @param $baseLevel
	 */
	public static function setBaseLevel( $baseLevel )
	{
		self::$baseLevel = $baseLevel;
	}

	/**
	 * @return int
	 */
	public static function getBaseLevel()
	{
		return self::$baseLevel;
	}

	/**
	 * @param $currentIndent
	 */
	public static function setCurrentIndent( $currentIndent )
	{
		self::$currentIndent = $currentIndent;
	}

	/**
	 * @return int
	 */
	public static function getCurrentIndent()
	{
		return self::$currentIndent;
	}

	/**
	 * @param $echoData
	 */
	public static function setEchoData( $echoData )
	{
		self::$echoData = $echoData;
	}

	/**
	 * @return bool
	 */
	public static function getEchoData()
	{
		return self::$echoData;
	}

	/**
	 * @param $prefix
	 */
	public static function setPrefix( $prefix )
	{
		self::$prefix = $prefix;
	}

	/**
	 * @return null
	 */
	public static function getPrefix()
	{
		return self::$prefix;
	}

	/**
	 * @param int $categoryWindowWidth
	 *
	 * @return void
	 */
	public static function setCategoryWindowWidth( $categoryWindowWidth )
	{
		self::$_categoryWindowWidth = $categoryWindowWidth;
	}

	/**
	 * @return int
	 */
	public static function getCategoryWindowWidth()
	{
		return self::$_categoryWindowWidth;
	}

	/**
	 * @param string $escapeSequence
	 *
	 * @return \CPSLog $this
	 */
	public function setEscapeSequence( $escapeSequence )
	{
		$this->_escapeSequence = $escapeSequence;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getEscapeSequence()
	{
		return $this->_escapeSequence;
	}

	/**
	 * @param array $logColors
	 *
	 * @return \CPSLog $this
	 */
	public function setLogColors( $logColors )
	{
		$this->_logColors = $logColors;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getLogColors()
	{
		return $this->_logColors;
	}
}