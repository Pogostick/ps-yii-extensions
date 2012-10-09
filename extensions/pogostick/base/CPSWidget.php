<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link      http://www.pogostick.com Pogostick, LLC.
 * @license   http://www.pogostick.com/licensing
 */

/**
 * CPSWidget is the base class for all Pogostick widgets for Yii. This object
 * is pretty much identical to CPSComponent but offers a little bit of extra functionality.
 *
 * @package          psYiiExtensions
 * @subpackage       base
 *
 * @author           Jerry Ablan <jablan@pogostick.com>
 * @version          SVN: $Id: CPSWidget.php 405 2010-10-21 21:44:02Z jerryablan@gmail.com $
 * @since            v1.0.0
 *
 * @filesource
 */
class CPSWidget extends CInputWidget implements IPSComponent
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * The internal name of the component.
	 *
	 * @var string
	 */
	protected $_internalName;

	/**
	 * Tracks if we have been initialized yet.
	 *
	 * @var boolean
	 */
	protected $_initialized = false;

	/**
	 * Our behaviors. Cached for speed here...
	 *
	 * @var array
	 */
	protected $_behaviorCache = array();

	/**
	 * Our CSS files
	 *
	 * @var array
	 */
	protected $_cssFiles = array();

	/**
	 * Our JS files
	 *
	 * @var array
	 */
	protected $_scriptFiles = array();

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	/**
	 * Constructs a widget
	 */
	public function __construct( $owner = null )
	{
		parent::__construct( $owner );

		//	Preinitialize
		$this->preinit();
	}

	/**
	 * Choose your destructor!
	 */
	public function __destruct()
	{
		try
		{
			//	Make sure scripts get regged before we die...
			$this->registerClientScripts();

			parent::__destruct();
		}
		catch ( Exception $_ex )
		{
			//	Ignore.
		}
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
		$this->attachBehavior( 'psWidget', 'pogostick.behaviors.CPSWidgetBehavior' );
	}

	/**
	 * Initialize our component.
	 */
	public function init()
	{
		if ( !$this->_initialized )
		{
			//	Now call parent's init...
			parent::init();

			//	Call our behaviors init() method if they exist
			foreach ( $this->_behaviorCache as $_name )
			{
				$this->asa( $_name )->init();
			}

			//	Get the id/name of this widget
			list( $this->name, $this->id ) = $this->resolveNameID();

			if ( empty( $this->name ) )
			{
				$this->name = $this->_internalName;
			}

			//	We are now...
			$this->_initialized = true;
		}
	}

	/**
	 * Pushes a css file onto the page load stack.
	 *
	 * @param string $path Path of css relative to doc_root
	 * @param string $id
	 */
	public function pushCssFile( $path, $media = 'screen' )
	{
		array_push(
			$this->_cssFiles,
			array(
				'path'  => $path,
				'media' => $media,
			)
		);
	}

	/**
	 * Pops a css file off the top of the page load stack.
	 *
	 * @return string|null
	 */
	public function popCssFile()
	{
		return array_shift( $this->_cssFiles );
	}

	/**
	 * Pushes a script onto the page load stack.
	 *
	 * @param string  $path Path of script relative to doc_root
	 * @param integer $position
	 * @param string  $id
	 */
	public function pushScriptFile( $path, $position = CClientScript::POS_HEAD )
	{
		array_push(
			$this->_scriptFiles,
			array(
				'path'     => $path,
				'position' => $position,
			)
		);

		$this->_scriptFiles[] = array( $path, $position );
	}

	/**
	 * Pops a script file off the top of the page load stack.
	 *
	 * @return string|null
	 */
	public function popScriptFile()
	{
		return array_shift( $this->_scriptFiles );
	}

	/***
	 * Handles registration of scripts & css files...
	 *
	 * @return CClientScript Returns the current applications CClientScript object {@link CWebApplication::getClientScript}
	 */
	public function registerClientScripts()
	{
		//	Register a special CSS file if we have one...
		if ( $this->cssFile )
		{
			$this->pushCssFile( $this->cssFile );
			$this->cssFile = null;
		}

		$_baseUrl = CPSHelperBase::_gbu();

		//	Load css files and unset from array...
		while ( null !== ( $_file = $this->popCssFile() ) )
		{
			CPSLog::trace( __METHOD__, 'Popped css: ' . print_r( $_file, true ) );
			CPSHelperBase::_rcf( $_baseUrl . $_file['path'], $_file['media'] );
		}

		//	Load script files and unset from array...
		while ( null !== ( $_file = $this->popScriptFile() ) )
		{
			CPSLog::trace( __METHOD__, 'Popped script: ' . print_r( $_file, true ) );
			$_position = null;

			if ( !is_array( $_file ) )
			{
				$_path = $_file;
			}
			else
			{
				$_path = PS::o( $_file, 'path' );
				$_position = PS::o( $_file, 'position' );
			}

			CPSHelperBase::_rsf( $_baseUrl . $_path, $_position );
		}

		//	Send upstream for convenience
		return CPSHelperBase::_cs();
	}

	/**
	 * Registers a widget script.
	 * If no script is provided, the object's generateJavascript() method is called to get the sript.
	 *
	 * @param string $script
	 * @param        integer Where to load the script. See {@link CClientScript} for values.
	 */
	public function registerWidgetScript( $script = null, $position = CClientScript::POS_READY )
	{
		if ( null === $script )
		{
			$script = $this->generateJavascript();
		}
		if ( $script )
		{
			CPSHelperBase::_rs( $this->getUniqueId( $this->id ), $script, $position );
		}
	}

	/**
	 * Attaches an Behavior to this component.
	 * We just cache the names here for lookup speed.
	 *
	 * @param string the Behavior's name. It should uniquely identify this Behavior.
	 * @param mixed  the Behavior configuration. This is passed as the first parameter to {@link YiiBase::createComponent} to create the Behavior object.
	 *
	 * @return IPSBehavior the Behavior object
	 */
	public function attachBehavior( $name, $behavior )
	{
		//	Attach the Behavior at the parent and add options here...
		if ( $_behavior = parent::attachBehavior( $name, $behavior ) )
		{
			//	Add to our cache...
			$this->_behaviorCache[] = $name;
		}

		return $_behavior;
	}

	/**
	 * Alias for setOptions
	 *
	 * @param array $options
	 *
	 * @see setOptions
	 */
	public function configure( $options = array() )
	{
		$this->setOptions( $options );
	}

	/**
	 * Given an ID, a unique name is built and returned
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	public function getUniqueId( $id = null )
	{
		return 'ps.' . CPSHash::hash( CPSHelperBase::nvl( $id, __CLASS__ ) . time() . CPSWidgetHelper::getNextIdCount() );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Generates the javascript code for the widget
	 *
	 * @return string
	 */
	protected function generateJavascript()
	{
	}

	/**
	 * Generates the javascript code for the widget
	 *
	 * @return string
	 */
	protected function generateHtml()
	{
	}

	//********************************************************************************
	//* Interface Requirements
	//********************************************************************************

	/**
	 * Get our internal name
	 *
	 * @return string
	 */
	public function getInternalName()
	{
		return $this->_internalName;
	}

	/**
	 * Set our internal name
	 *
	 * @param string $name
	 */
	public function setInternalName( $value )
	{
		$this->_internalName = $value;
	}

	//********************************************************************************
	//* Statics
	//********************************************************************************

	/**
	 * Constructs and returns a widget
	 *
	 * The options passed in are dynamically added to the options array and will be accessible
	 * and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	 *
	 * @param string $name    The type of widget to create
	 * @param array  $options The options for the widget
	 *
	 * @return CPSWidget
	 */
	public static function create( $name = null, array $options = array() )
	{
		//	Allow shifted arguments...
		if ( is_array( $name ) && array() === $options )
		{
			$options = $name;
			$name = null;
		}

		//	Instantiate...
		$_name = CPSHelperBase::nvl( $name, ( is_callable( 'get_called_class' ) ? get_called_class() : $name ) );
		$_class = CPSHelperBase::o( $options, 'class', ( is_callable( 'get_called_class' ) ? get_called_class() : $_name ), true );
		$_widget = new $_class();
		$_widget->widgetName = $_name;
		$_widget->id = $_widget->name = CPSHelperBase::o( $options, 'id', $_name );
		$_widget->name = CPSHelperBase::o( $options, 'name', $_widget->id );

		//	Push any optional scripts
		foreach ( CPSHelperBase::o( $options, '_scripts', array(), true ) as $_script )
		{
			$_widget->pushScriptFile( $_widget->baseUrl . $_script );
		}

		//	And CSS
		foreach ( CPSHelperBase::o( $options, '_cssFiles', array(), true ) as $_css )
		{
			$_widget->pushCssFile( $_widget->baseUrl . $_css );
		}

		//	Now process the rest of the options...
		$_widget->addOptions( $options );

		//	Initialize our widget
		$_widget->init();

		//	And run it...
		if ( $_widget->autoRun )
		{
			$_widget->run();
		}

		//	And return...
		return $_widget;
	}

}