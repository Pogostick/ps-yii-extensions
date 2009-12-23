<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSComponent is the base class for all Pogostick components for Yii.
 * It contains functionality to call behavior methods without the need for chaining.
 * 
 * @package 	psYiiExtensions
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.0
 * 
 * @filesource
 * @property string $internalName The internal name of the component. Used as the name of the behavior when attaching.
 * @property string $prefixDelimiter The delimiter to use for prefixes.
 */
class CPSComponent extends CApplicationComponent implements IPogostickBehavior
{
	//********************************************************************************
	//* Member variables
	//********************************************************************************

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
	* @see getInternalName
	* @see $m_sPrefixDelimiter
	*/
	protected $m_sInternalName;
	public function getInternalName() { return $this->m_sInternalName; }
	public function setInternalName( $sValue ) { $this->m_sInternalName = $sValue; }
	
	/**
	* The delimiter to use for prefixes. This must contain only characters that are not allowed
	* in variable names (i.e. '::', '||', '.', etc.). Defaults to '::'. There is no length limit,
	* but 2 works out. There is really no need to ever change this unless you have a strong dislike
	* of the '::' characters.
	*
	* @var string
	*/
	protected $m_sPrefixDelimiter = '::';
	public function getPrefixDelimiter() { return $this->m_sPrefixDelimiter; }
	protected function setPrefixDelimiter( $sValue ) { $this->m_sPrefixDelimiter = $sValue; }
	public function getNamePrefix() { return $this->m_sInternalName . $this->m_sPrefixDelimiter; }

	/**
	* As behaviors are added to the object, this is set to true to quickly determine if the
	* component does in fact contain behaviors.
	*
	* @var bool
	*/
	protected $m_bHasBehaviors = false;
	public function getHasBehaviors() { return $this->m_bHasBehaviors; }
	public function setHasBehaviors( $bValue ) { $this->m_bHasBehaviors = $bValue; }

	/**
	* A private array containing all the attached behaviors information of this component.
	* @var array
	*/
	protected $m_arBehaviors = null;
	/**
	* Retrieves the behaviors attached to this component
	* @returns array
	*/
	public function getBehaviors() { return( $this->m_arBehaviors ); }

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	* Convenience functions to access the behavior assets
	*
	*/
	public function &hasBehaviorMethod( $sMethodName ) { return CPSCommonBase::hasBehaviorMethod( $this, $sMethodName ); }
	public function &hasBehaviorProperty( $sName ) { return CPSCommonBase::hasBehaviorProperty( $this, $sName ); }
	public function &getBehaviorProperty( $sName ) { return CPSCommonBase::getBehaviorProperty( $this, $sName); }
	public function setBehaviorProperty( $sName, $oValue ) { return CPSCommonBase::setBehaviorProperty( $this, $sName, $oValue ); }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Constructor
	*
	*/
	public function __construct()
	{
		//	Create our internal name
		$_sName = CPSCommonBase::createInternalName( $this );
		
		//	Attach our default behavior
		$this->attachBehavior( $_sName, 'pogostick.behaviors.CPSComponentBehavior' );

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $_sName, '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'trace', $_sName );
		
		//	Preinitialize if available
		if ( method_exists( $this, 'preinit' ) ) $this->preinit();
	}

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	/**
	 * Attaches a behavior to this component.
	 * This method will create the behavior object based on the given
	 * configuration. After that, the behavior object will be initialized
	 * by calling its {@link IBehavior::attach} method.
	 * @param string the behavior's name. It should uniquely identify this behavior.
	 * @param mixed the behavior configuration. This is passed as the first
	 * parameter to {@link YiiBase::createComponent} to create the behavior object.
	 * @return IBehavior the behavior object
	 */
	public function attachBehavior( $sName, $oBehavior )
	{
		//	Attach the behavior at the parent and add options here...
		if ( $_oObject = parent::attachBehavior( $sName, $oBehavior ) )
		{
			$this->m_bHasBehaviors |= true;
			
			//	Set behavior object's internalName so it can recognize it's own options
			$_oObject->getOptionsObject()->setInternalName( $sName );
			$_arBehavior =& $this->m_arBehaviors[ $sName ];
			
			//	Initialize arrays
			if ( ! isset( $_arBehavior[ self::BEHAVIOR_META_METHODS ] ) || null === $_arBehavior[ self::BEHAVIOR_META_METHODS ] ) 
				$_arBehavior[ self::BEHAVIOR_META_METHODS ] = array();
			
			if ( ! isset( $_arBehavior[ self::BEHAVIOR_META_VARS ] ) || null === $_arBehavior[ self::BEHAVIOR_META_VARS ] ) 
				$_arBehavior[ self::BEHAVIOR_META_VARS ] = array();
			
			//	Set our object
			$_arBehavior[ self::BEHAVIOR_META_OBJECT ] = $_oObject;

			//	Place valid options in here for fast checking...
			$_arBehavior[ self::BEHAVIOR_META_VALID ] = array();

			//	Cache behavior methods for lookup speed
			$_arBehavior[ self::BEHAVIOR_META_METHODS ] =
				array_merge(
					$_arBehavior[ self::BEHAVIOR_META_METHODS ],
					array_change_key_case( array_flip( array_values( get_class_methods( $_oObject ) ) ), CASE_LOWER
				)
			);

			//	Cache behavior members for lookup speed
			$_arBehavior[ self::BEHAVIOR_META_VARS ] =
				array_merge(
					$_arBehavior[ self::BEHAVIOR_META_VARS ],
					array_change_key_case( array_flip( array_keys( get_class_vars( get_class( $this ) ) ) ), CASE_LOWER
				)
			);
		}

		return $_oObject;
	}
	
	/**
	* Yii CComponent::init() override
	* 
	*/
	public function init()
	{
		//	Call daddy
		parent::init();

		//	Call our behaviors init() method if they exist
		foreach ( array_keys( $this->m_arBehaviors ) as $_sKey )
		{
			if ( method_exists( $this->m_arBehaviors[ $_sKey ][ self::BEHAVIOR_META_OBJECT ], 'init' ) )
				$this->m_arBehaviors[ $_sKey ][ self::BEHAVIOR_META_OBJECT ]->init();
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
	public function &__get( $sName )
	{
		//	Check behavior properties
		try { return $this->getBehaviorProperty( $sName ); } catch ( CException $_ex ) { /* Ignore and pass through */ $_oEvent = $_ex; }

		//	Try daddy...
		return parent::__get( $sName );
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
		//	Check behavior properties
		try { return $this->setBehaviorProperty( $sName, $oValue ); } catch ( CException $_ex ) { /* Ignore and pass through */ $_oEvent = $_ex; }

		//	Let parent take a stab. He'll check getter/setters and behavior methods
		return parent::__set( $sName, $oValue );
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
		$_oEvent = null;
		
		//	Check behavior methods...
		if ( $_oBehave = $this->hasBehaviorMethod( $sName ) )
			try { return call_user_func_array( array( $_oBehave[ self::BEHAVIOR_META_OBJECT ], $sName ), $arParams ); } catch ( CException $_ex ) { /* Ignore and pass through */ $_oEvent = $_ex; }

		if ( $_oEvent && 1 == $_oEvent->getCode() ) throw $_oEvent;

		//	Try parent first... cache exception
		return parent::__call( $sName, $arParams );
	}

}