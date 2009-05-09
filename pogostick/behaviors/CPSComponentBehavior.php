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
	* The valid patterns for options, checked before they are set.
	*
	* Patterns need to be specified as follows:
	* <code>
	* array( 'optionName' =>
	* 	array( 'mustContain' =>
	* 		array( 'subOptions' =>
	* 			array( '
	* </code>
	*
	* @var array
	* @see setValidPattern
	* @see getValidPattern
	*/
	protected $m_arPatterns = null;

	protected $m_sInternalName;
	protected $m_sNamePrefix;
	protected $m_oParent = null;

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	public function getInternalName() { return( $this->m_sInternalName ); }
	public function setInternalName( $sValue ) { $this->m_sInternalName = $sValue; }

	public function getNamePrefix() { return( $this->m_sNamePrefix ); }
	public function setNamePrefix( $sValue ) { $this->m_sNamePrefix = $sValue; }

	public function &getParent() { return $this->m_oParent; }
	public function setParent( &$oParent ) { $this->m_oParent =& $oParent; }

	public function getValidPattern() { return( $this->m_arValidPattern ); }
	public function setValidPattern( $arValue ) { $this->m_arValidPattern = $arValue; }

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Constructor
	*
	*/
	public function __construct( &$oParent = null )
	{
		//	build our option manager...
		$this->m_oOptions = new CPSOptionManager( $this );

		//	Set up our base settings
		$this->setOptions( self::getBaseOptions() );

		//	Get our name...
		$this->createInternalName();

		//	Get parent...
		$this->setParent( $oParent );

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $this->getInternalName(), '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'trace', $this->getInternalName() );
	}

	/**
	* Creates the internal name of the widget. Use (@link setInternalName) to change.
	* @see setInternalName
	*/
	public function createInternalName()
	{
		//	Create our internal name
		$_sClass = get_class( $this );

		//	Set names
		if ( false !== strpos( $_sClass, 'CPS', 0 ) )
			$this->m_sInternalName = $_sClass = str_replace( 'CPS', 'ps', $_sClass );
		else
			$this->m_sInternalName = $_sClass;

		$this->m_sNamePrefix = $this->m_sInternalName . '::';
	}

	/**
	* Easier on the eyes
	*
	*/
	private function getBaseOptions()
	{
		return(
			array(
				'baseUrl' => array( 'value' => '', 'type' => 'string' ),
				'checkOptions' => array( 'value' => true, 'type' => 'boolean' ),
				'validOptions' => array( 'value' => array(), 'type' => 'array' ),
				'options' => array( 'value' => array(), 'type' => 'array' ),
				'checkCallbacks' => array( 'value' => true, 'type' => 'boolean' ),
				'validCallbacks' => array( 'value' => array(), 'type' => 'array' ),
				'callbacks' => array( 'value' => array(), 'type' => 'array' ),
			)
		);
	}

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
	* $this->setOptions( array( 'option_x' => array( 'value' => '1', 'valid' => array( 'x', 'y', 'z' ) );
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
    * Check the options against the valid ones
    *
    * @param array $value user's options
    * @param array $validOptions valid options
    */
	public function checkOptions( array $arOptions = null, array $arValidOptions = null )
 	{
		if ( ! isset( $arValidOptions ) )
			$arValidOptions = $this->validOptions;

		if ( ! isset( $arOptions ) )
			$arOptions = $this->options;

		foreach ( $arOptions as $_sKey => $_oValue )
		{
			if ( is_array( $arValidOptions ) && ! array_key_exists( $_sKey, $arValidOptions ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" is not a valid option', array( '{x}' => $_sKey ) ) );

			$_sType = gettype( $_oValue );
			$_oVOType = $arValidOptions[ $_sKey ][ 'type' ];

			if ( ( ! is_array( $_oVOType ) && ( $_sType != $_oVOType ) ) || ( is_array( $_oVOType ) && ! in_array( $_sType, $_oVOType ) ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" must be of type "{y}"', array( '{x}' => $_sKey, '{y}' => ( is_array( $_oVOType ) ) ? implode( ', ', $_oVOType ) : $_oVOType ) ) );

			if ( array_key_exists( 'valid', $arValidOptions[ $_sKey ] ) )
			{
				$_arValid = $arValidOptions[ $_sKey ][ 'valid' ];

				if ( is_array( $_arValid[ 'valid' ] ) && ! in_array( $_oValue, $_arValid ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" must be one of: "{y}"', array( '{x}' => $_sKey, '{y}' => implode( ', ', $_arValid ) ) ) );
			}

			if ( ( $_sType == 'array' ) && array_key_exists( 'elements', $arValidOptions[ $_sKey ] ) )
				$this->checkOptions( $_oValue, $arValidOptions[ $_sKey ][ 'elements' ] );
		}

		//	Now validate them...
		return( $this->validateOptions( $arOptions, $arValidOptions ) );
	}

	/**
	* Generates the options for the widget
	*
	* @param array $arOptions
	* @return string
	*/
	public function makeOptions( array $arOptions = null )
	{
		$_arOptions = ( $arOptions == null ) ? $this->getOption( 'options' ) : $arOptions;

		foreach ( $this->getOption( 'callbacks' ) as $_sKey => $_oValue )
		{
			if ( ! empty( $_oValue ) )
				$_arOptions[ "cb_{$_sKey}" ] = $_sKey;
		}

		//	Get all the options merged...
		$_arToEncode = array();

		foreach( $_arOptions as $_oOption )
		{
			//	Ignore private options
			if ( isset( $_oOption[ 'private' ] ) && true == $_oOption[ 'private' ] )
				continue;

			$_arToEncode[ $_oOption[ 'name' ] ] = $_oOption[ 'value' ];
		}

		if ( sizeof( $_arToEncode ) > 0 )
		{
			$_sEncodedOptions = CJavaScript::encode( $_arToEncode );

			//	Fix up the callbacks...
			foreach ( $this->getOption( 'callbacks' ) as $_sKey => $_oValue )
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

	/**
	* Validates that required options have been specified...
	*
	* @param mixed $arOptions
	* @param mixed $arValidOptions
	*/
	public function validateOptions( array $arOptions , array $arValidOptions )
	{
		foreach ( $arOptions as $_sKey => $_oValue )
		{
			//	Is it a valid option?
			if ( ! array_key_exists( $_sKey, $arValidOptions ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" is not a valid option', array( '{x}' => $_sKey ) ) );

			$_oCurOption = $arOptions[ $_sKey ];
			$_oCurValidOption = $arValidOptions[ $_sKey ];

			if ( isset( $_oCurValidOption[ 'required' ] ) && $_oCurValidOption[ 'required' ] && ( ! $_oCurOption || empty( $_oCurOption ) ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" is a required option', array( '{x}' => $_sKey ) ) );
		}

		return( true );
	}

   /**
    *
    * @param array $value user's callbacks
    * @param array $validCallbacks valid callbacks
    */
	public function checkCallbacks( array $arCallbacks = null, array $arValidCallbacks = null )
	{
		if ( ! empty( $arValidCallbacks ) && is_array( $arValidCallbacks ) )
		{
			foreach ( $arCallbacks as $_sKey => $_oValue )
			{
				if ( ! in_array( $_sKey, $arValidWidgetCallbacks ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" must be one of: {y}', array( '{x}' => $_sKey, '{y}' => implode( ', ', $arValidCallbacks ) ) ) );
			}
		}
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
	 * @see CPSCommonBase::genericGet
	 */
	public function __get( $sName )
	{
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
	 * @see CPSCommonBase::genericSet
	 */
	public function __set( $sName, $oValue )
	{
		//	Try daddy...
		try { return parent::__set( $sName, $oValue ); } catch ( CException $_ex ) { /* Ignore and pass through */ $_oEvent = $_ex; }

		//	Look in our behavior properties
		return $this->setBehaviorProperty( $oObject, $sName, $oValue );
	}

	/**
	 * Calls the named method which is not a class method.
	 * Do not call this method. This is a PHP magic method that we override
	 * @param string The method name
	 * @param array The method parameters
	 * @throws CException if the property/event is not defined or the property is read only.
	 * @see __call
	 * @return mixed The method return value
	 */
	public function __call( $sName, $arParams )
	{
		//	Try parent first... cache exception
		try { return parent::__call( $sName, $arParams ); } catch ( CException $_ex ) { /* Ignore and pass through */ $_oEvent = $_ex; }

		//	Check behavior methods...
		if ( $_oBehave = $this->hasBehaviorMethod( $oObject, $sName ) )
			return call_user_func_array( array( $_oBehave[ '_object' ], $sName ), $arParams );

		//	Invalid property...
		if ( null != $oEvent )
			throw $oEvent;
	}

}