<?php
/**
 * CPSComponentBehavior class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSComponentBehavior provides base component behaviors to other classes
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Behaviors
 * @filesource
 * @since 1.0.0
 */
class CPSComponentBehavior extends CBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* An instance of our option manager for this behavior
	*
	* @var (@link CPSOptionManager)
	*/
	private $m_oOptions;
	/**
	* The internal name of the component. Used as the name of the behavior when attaching.
	*
	* In order to facilitate option separation, this value is used along with the prefix delimiter by the internal option
	* manager to distinguish between owners. During construction, it is set to the name of
	* the class, and includes special behavior for Pogostick classes. Example: CPSComponent
	* becomes psComponent. Use or override (@link setInternalName) to change the name at
	* runtime.
	*
	* @var string
	* @see setInternalName
	* @see $m_sPrefixDelimiter
	*/
	protected $m_sInternalName;
	/**
	* The delimiter to use for prefixes. This must contain only characters that are not allowed
	* in variable names (i.e. '::', '||', '.', etc.). Defaults to '::'. There is no length limit,
	* but 2 works out. There is really no need to ever change this unless you have a strong dislike
	* of the '::' characters.
	*
	* @var string
	*/
	protected static $m_sPrefixDelimiter = '::';

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	* Getters for member variables
	*
	*/
	public function getInternalName() { return $this->m_sInternalName; }
	public function getNamePrefix() { return $this->m_sInternalName . $this->m_sPrefixDelimiter; }
	public function getPrefixDelimiter() { return $this->m_sPrefixDelimiter; }
	public function getValidPattern() { return $this->m_arValidPattern; }

	/**
	* Setters for member variables
	*
	*/
	public function setInternalName( $sValue ) { $this->m_sInternalName = $sValue; }
	public function setValidPattern( $arValue ) { $this->m_arValidPattern = $arValue; }

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Constructor
	*
	*/
	public function __construct()
	{
		//	Get our name...
		$_sName = CPSCommonBase::createInternalName( $this );

		//	Import needed classes...
		Yii::import( 'pogostick.base.CPSOptionManager' );

		//	build our option manager...
		$this->m_oOptions = new CPSOptionManager( $this->m_sInternalName );

		//	Set up our base settings
		$this->setOptions( self::getBaseOptions() );

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $_sName, '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'trace', $_sName );
	}

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	/**
	* Sets the default (@link $m_sPrefixDelimiter). Defaults to '::'.
	*
	* @param string $sValue
	*/
	public function setPrefixDelimiter( $sValue ) { $this->m_sPrefixDelimiter = $sValue; }

	/**
	* Returns a reference to the entire reference array
	*
	* @returns array A reference to the internal options array
	* @see getOption
	*/
	public function &getOptions() { return $this->m_oOptions->getOptions(); }

	/**
	* Returns a reference to the entire reference array
	*
	* @returns array A reference to the internal options array
	* @see getOption
	*/
	public function &getOptionsObject() { return $this->m_oOptions; }

	/**
	* Add bulk options to the manager.
	*
	* @param array $arOptions An array containing option_key => value pairs to put into option array. The parameter $arOptions is merged with the existing options array. Existing option values are overwritten or added.
	* <code>
	* $this->setOptions( array( 'option_x' => array( '_value' => '1', '_validPattern' => array( 'valid' => array( 'x', 'y', 'z' ) ) );
	* </code>
	* @returns null
	* @see getOptions
	*/
	public function setOptions( array $arOptions ) { $this->m_oOptions->setOptions( $arOptions ); }

	/**
	* Retrieves an option value from the options array. If key doesn't exist, it's created as an empty array and returned.
	*
	* @param string $sKey
	* @return mixed
	* @see getOptions
	*/
	public function getOption( $sKey ) { return $this->m_oOptions->getOption( $sKey ); }

	/**
	* Checks if an option exists in the options array...
	*
	* @param string $sKey
	* @return bool
	* @see setOption
	* @see setOptions
	*/
	public function hasOption( $sKey ) { return in_array( $sKey, $this->m_oOptions->getOptionsObject() ); }

	/**
	* Sets a single option to the array
	*
	* @param string $sKey
	* @param mixed $oValue
	* @return null
	* @see setOptions
	*/
	public function setOption( $sKey, $oValue )
	{
		//	Validate options first...
		$this->m_oOptions->setOption( $sKey, $oValue );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Easier on the eyes
	*
	*/
	private function getBaseOptions()
	{
		return(
			array(
				'baseUrl_' => array( '_value' => '', '_validPattern' => array( 'type' => 'string' ) ),
				'checkOptions_' => array( '_value' => true, '_validPattern' => array( 'type' => 'boolean' ) ),
				'validOptions_' => array( '_value' => array(), '_validPattern' => array( 'type' => 'string' ) ),
				'checkCallbacks_' => array( '_value' => true, '_validPattern' => array( 'type' => 'boolean' ) ),
				'validCallbacks_' => array( '_value' => array(), '_validPattern' => array( 'type' => 'string' ) ),
				'callbacks_' => array( '_value' => array(), '_validPattern' => array( 'type' => 'string' ) ),
			)
		);
	}

  /**
    * Checks the callback array to see if they're valid.
    *
    * @throws CException
    * @returns true If all is good.

    */
	public function checkCallbacks()
	{
		$_arCallbacks = $this->getOption( 'callbacks' );
		$_arValidCallbacks = $this->getOption( 'validCallbacks' );

		if ( ! empty( $_arCallbacks ) && ! empty( $_arValidCallbacks ) )
		{
			foreach ( $_arCallbacks as $_sKey => $_oValue )
			{
				if ( ! in_array( $_sKey, $_arValidCallbacks ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" must be one of: {y}', array( '{x}' => $_sKey, '{y}' => implode( ', ', $_arValidCallbacks ) ) ) );
			}
		}

		//	Clean...
		return true;
	}

	/**
	* Generates the options for the widget
	*
	* @param array $arOptions
	* @return string
	*/
	public function makeOptions()
	{
		$_arOptions = array();

		//	Now get option/value pairs
		foreach( $this->getOptions() as $_sKey => $_oValue )
		{
			//	Is this private? Move along...
			if ( false !== strpos( $_sKey, '_', strlen( $_sKey ) - 1 ) )
				continue;

			//	This option is safe to output
			$_arOptions[ $_sKey ] = $_oValue;
		}

		$_arCallbacks = $this->getOption( 'callbacks' );

		//	Add callbacks to the array...
		foreach ( $_arCallbacks as $_sKey => $_oValue )
		{
			if ( ! empty( $_oValue ) )
				$_arOptions[ "cb_{$_sKey}" ] = $_sKey;
		}

		//	Get all the options merged...
		$_arToEncode = array();

		foreach( $_arOptions as $_oOption )
			$_arToEncode[ ( isset( $_oOption[ '_extName' ] ) ) ? $_oOption[ '_extName' ] : key( $_oOption ) ] = $_oOption[ '_value' ];

		if ( sizeof( $_arToEncode ) > 0 )
		{
			$_sEncodedOptions = CJavaScript::encode( $_arToEncode );

			//	Fix up the callbacks...
			foreach ( $_arCallbacks as $_sKey => $_oValue )
			{
				if ( ! empty( $_oValue ) )
				{
					if ( 0 == strncasecmp( $_oValue, 'function(', 9 ) )
						$_sEncodedOptions = str_replace( "'cb_{$_sKey}':'{$_sKey}'", "{$_sKey}:{$_oValue}", $_sEncodedOptions );
					else
						$_sEncodedOptions = str_replace( "'cb_{$_sKey}':'{$_sKey}'", "{$_sKey}:'{$_oValue}'", $_sEncodedOptions );
				}
			}

			return( $_sEncodedOptions );
		}

		return( null );
	}

	//********************************************************************************
	//* Magic Methods
	//********************************************************************************

	/**
	 * Returns a property value, an event handler list or a behavior based on its name.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using the following syntax to read a property or obtain event handlers:
	 * <code>
	 * $value=$component->propertyName;
	 * $handlers=$component->eventName;
	 * </code>
	 *
	 * Will also return a property from an attached behavior directly without the need for using the behavior name
	 * <code>
	 * $value = $component->behaviorPropertyName;
	 * </code>
	 * instead of
	 * <code>
	 * $value = $component->behaviorName->propertyName
	 * </code>
	 * @param string the property name or event name
	 * @return mixed the property value, event handlers attached to the event, or the named behavior
	 * @throws CException if the property or event is not defined
	 * @see __set
	 * @see getBehaviorProperty
	 */
	public function __get( $sName )
	{
		if ( in_array( $sName, array_keys( get_class_vars( get_class( $this ) ) ) ) )
			return $this->{$sName};

		//	Try daddy...
		try { return parent::__get( $sName ); } catch ( CException $_ex ) { /* Ignore and pass through */ $_oEvent = $_ex; }

		//	Now us...
		return $this->getBehaviorProperty( $oObject, $sName );
	}

	/**
	 * Sets value of a component property.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using the following syntax to set a property or attach an event handler
	 * <pre>
	 * $this->propertyName=$value;
	 * $this->eventName=$callback;
	 * </pre>
	 *
	 * Will also set a property value in an attached behavior directly without the need for using the behavior name
	 * <pre>
	 * $this->behaviorPropertyName = $value;
	 * </pre>
	 * @param string the property name or the event name
	 * @param mixed the property value or callback
	 * @throws CException if the property/event is not defined or the property is read only.
	 * @see __get
	 * @see setBehaviorProperty
	 */
	public function __set( $sName, $oValue )
	{
		//	Try daddy...
		try { return parent::__set( $sName, $oValue ); } catch ( CException $_ex ) { /* Ignore and pass through */ $_oEvent = $_ex; }

		//	Look in our behavior properties
		return $this->setBehaviorProperty( $oObject, $sName, $oValue );
	}

 }