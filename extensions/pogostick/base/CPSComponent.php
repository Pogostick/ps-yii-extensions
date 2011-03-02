<?php
/**
 * This file is part of the YiiXL package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @filesource
 */

/**
 * CPSComponent class
 * This is the base class for all Pogostick Yii Extension library objects.
 * It extends the base functionality of the Yii Framework without replacing
 * and core code.
 *
 * @package		psYiiExtensions
 * @subpackage 	base.components
 *
 * @author			Jerry Ablan <jablan@pogostick.com>
 * @version		SVN $Id: CPSComponent.php 405 2010-10-21 21:44:02Z jerryablan@gmail.com $
 * @since			v1.0.0
 *
 * @property string $internalName The internal name of the component.
 * @property boolean $debugMode Enable trace-level debugging
 * @property integer $debugLevel A user-defined debugging level
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
	 * @return string Returns the internal name
	 */
	public function getInternalName() { return $this->_internalName; }
	/**
	 * Sets the internal name
	 * @param string $value The internal name
	 */
	public function setInternalName( $value ) { $this->_internalName = $value; }

	/**
	* @var array Our configuration options
	*/
	protected $_optionList;
	/**
	 * Gets configuration options
	 * @return array
	 */
	public function getOptionList() { return $this->_optionList; }
	/**
	 * Sets configuration options
	 * @return array
	 */
	public function setOptionList( $value = array() ) { $this->_optionList = $value; }

	/**
	 * @var boolean Tracks the status of debug mode for component
	 */
	protected $_debugMode = false;
	/**
	 * Gets the debug mode
	 * @return boolean The current debug mode
	 */
	public function getDebugMode() { return $this->_debugMode; }
	/**
	 * Sets the debug mode
	 * @param boolean The new debug mode
	 */
	public function setDebugMode( $value = true ) { $this->_debugMode = $value; }

	/**
	 * @var integer The level of debugging
	 */
	protected $_debugLevel = 0;
	/**
	 * Gets the debug level
	 * @return integer
	 */
	public function getDebugLevel() { return $this->_debugLevel; }
	/**
	 * Sets the debug level
	 * @param integer The new debug level
	 */
	public function setDebugLevel( $value = 0 ) { $this->_debugLevel = $value; }

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	/**
	* Constructs a component.
	*/
	public function __construct( $config = array() )
	{
		//	Set any properties via standard config array
		if ( is_array( $config ) && ! empty( $config ) )
			$this->_loadConfiguration( $config );

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
	 * @param array $optionList
	 * @see setOptions
	 */
	public function configure( $optionList = array() )
	{
		$this->setOptions( $optionList );
	}

	/**
	 * Outputs a debug string if in debug mode.
	 * @param <type> $message The message
	 * @param <type> $category The category/method of the output
	 * @param <type> $route The destination of output. Can be 'echo', 'trace|info|error|debug|etc...', 'http', 'firephp'
	 */
	public function debug( $message, $category = null, $route = null )
	{
		if ( $this->_debugMode )
			echo $message . '<BR />';
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Loads an array into properties if they exist.
	 * @param array $optionList
	 */
	protected function _loadConfiguration( $optionList = array(), $overwriteExisting = true )
	{
		//	Make a copy for posterity
		if ( property_exists( $this, '_optionList' ) )
		{
			if ( $overwriteExisting || empty( $this->_optionList ) )
				$this->_optionList = $optionList;
			else
				$this->_optionList = array_merge( $this->_optionList, $optionList );
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
}