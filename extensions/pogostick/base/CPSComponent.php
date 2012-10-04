<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link      http://www.pogostick.com Pogostick, LLC.
 * @license   http://www.pogostick.com/licensing
 * @filesource
 */

/**
 * CPSComponent class
 * This is the base class for all Pogostick Yii Extension library objects.
 * It extends the base functionality of the Yii Framework without replacing
 * and core code.
 *
 * @package           psYiiExtensions
 * @subpackage        base.components
 *
 * @author            Jerry Ablan <jablan@pogostick.com>
 * @version           SVN $Id: CPSComponent.php 405 2010-10-21 21:44:02Z jerryablan@gmail.com $
 * @since             v1.0.0
 *
 * @property string  $internalName The internal name of the component.
 * @property boolean $debugMode    Enable trace-level debugging
 * @property integer $debugLevel   A user-defined debugging level
 */
class CPSComponent extends CApplicationComponent implements IPSComponent
{
	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	 * @var string The internal name of the component.
	 */
	protected $_internalName;

	/**
	 * @var boolean Tracks the status of debug mode for component
	 */
	protected $_debugMode = false;

	/**
	 * @var integer The level of debugging
	 */
	protected $_debugLevel = 0;

	/**
	 * @var SplStack|array
	 */
	protected $_exceptionStack;

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	/**
	 * Constructs a component.
	 *
	 * @param array $config
	 */
	public function __construct( $config = array() )
	{
		$this->_exceptionStack = ( @class_exists( 'SplStack', false ) ) ? new SplStack() : array();

		//	Set any properties via standard config array
		if ( is_array( $config ) && !empty( $config ) )
		{
			$this->_loadConfiguration( $config );
		}

		//	Preinitialize, called before afterConstruct
		$this->preinit();
	}

	/**
	 * Preinitialize the component
	 * Override to add your own functionality before init() is called.
	 */
	public function preinit()
	{
		//	Create our internal name
		CPSHelperBase::createInternalName( $this );

		//	Attach our default Behavior
		$this->attachBehavior( 'psComponent', 'pogostick.behaviors.CPSComponentBehavior' );
	}

	/**
	 * Alias for setOptions
	 *
	 * @param array $optionList
	 *
	 * @see setOptions
	 */
	public function configure( $optionList = array() )
	{
		$this->setOptions( $optionList );
	}

	/**
	 * Outputs a debug string if in debug mode.
	 *
	 * @param <type> $message The message
	 * @param <type> $category The category/method of the output
	 * @param <type> $route The destination of output. Can be 'echo', 'trace|info|error|debug|etc...', 'http', 'firephp'
	 */
	public function debug( $message, $category = null, $route = null )
	{
		if ( $this->_debugMode )
		{
			echo $message . '<BR />';
		}
	}

	/**
	 * @param Exception $exception
	 *
	 * @return \SplStack
	 */
	public function setLastException( $exception )
	{
		if ( @class_exists( 'SplStack', false ) )
		{
			$this->_exceptionStack->push( $exception );
		}
		else
		{
			$this->_exceptionStack[] = $exception;
		}

		return $this;
	}

	/**
	 * @return \Exception
	 */
	public function getLastException()
	{
		if ( @class_exists( 'SplStack', false ) )
		{
			return $this->_exceptionStack->pop();
		}

		if ( empty( $this->_exceptionStack ) )
		{
			return null;
		}

		return $this->_exceptionStack[count( $this->_exceptionStack ) - 1];
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Loads an array into properties if they exist.
	 *
	 * @param array $optionList
	 * @param bool  $overwriteExisting
	 *
	 */
	protected function _loadConfiguration( $optionList = array(), $overwriteExisting = true )
	{
		//	Make a copy for posterity
		if ( property_exists( $this, '_optionList' ) )
		{
			if ( $overwriteExisting || empty( $this->_optionList ) )
			{
				$this->_optionList = $optionList;
			}
			else
			{
				$this->_optionList = array_merge( $this->_optionList, $optionList );
			}
		}

		try
		{
			foreach ( $optionList as $_option => $_value )
			{
				try
				{
					//	See if __set has a better time with this...
					$this->{$_option} = $_value;
				}
				catch ( Exception $_ex )
				{
					//	Completely ignore errors...
				}
			}
		}
		catch ( Exception $_ex )
		{
			CPSLog::error( __METHOD__, 'Error while loading configuration options: ' . $_ex->getMessage() );
		}
	}

	//*************************************************************************
	//* Properties
	//*************************************************************************

	/**
	 * @param int $debugLevel
	 *
	 * @return \CPSComponent
	 */
	public function setDebugLevel( $debugLevel )
	{
		$this->_debugLevel = $debugLevel;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getDebugLevel()
	{
		return $this->_debugLevel;
	}

	/**
	 * @param boolean $debugMode
	 *
	 * @return \CPSComponent
	 */
	public function setDebugMode( $debugMode )
	{
		$this->_debugMode = $debugMode;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getDebugMode()
	{
		return $this->_debugMode;
	}

	/**
	 * @param string $internalName
	 *
	 * @return \CPSComponent
	 */
	public function setInternalName( $internalName )
	{
		$this->_internalName = $internalName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getInternalName()
	{
		return $this->_internalName;
	}

	/**
	 * @param array $optionList
	 *
	 * @return \CPSComponent
	 */
	public function setOptionList( $optionList )
	{
		$this->_optionList = $optionList;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getOptionList()
	{
		return $this->_optionList;
	}

	/**
	 * @param array|\SplStack $exceptionStack
	 *
	 * @return \CPSComponent
	 */
	public function setExceptionStack( $exceptionStack )
	{
		$this->_exceptionStack = $exceptionStack;

		return $this;
	}

	/**
	 * @return array|\SplStack
	 */
	public function getExceptionStack()
	{
		return $this->_exceptionStack;
	}

}
