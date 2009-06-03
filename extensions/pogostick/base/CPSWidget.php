<?php
/**
 * CPSWidget class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.gnu.org/licenses/gpl.html
 */

/**
 * The CPSWidget is the base class for all Pogostick widgets for Yii
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Base
 * @filesource
 * @since 1.0.0
 */
class CPSWidget extends CInputWidget
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	const BEHAVIOR_META_METHODS = '_classMethods';
	const BEHAVIOR_META_OBJECT = '_object';
	const BEHAVIOR_META_VALID = '_validOptions';
	const BEHAVIOR_META_VARS = '_classVars';

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
	protected $m_sPrefixDelimiter = '::';
	/**
	* As behaviors are added to the object, this is set to true to quickly determine if the
	* component does in fact contain behaviors.
	*
	* @var bool
	*/
	protected $m_bHasBehaviors = false;
	/**
	* A private array containing all the attached behaviors information of this component.
	*
	* @var array
	*/
	protected $m_arBehaviors = null;

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	* Getters for internal members
	*/
	public function getInternalName() { return $this->m_sInternalName; }
	public function getNamePrefix() { return $this->m_sInternalName . $this->m_sPrefixDelimiter; }
	public function getPrefixDelimiter() { return $this->m_sPrefixDelimiter; }
	public function getHasBehaviors() { return $this->m_bHasBehaviors; }

	/**
	* Setters for internal members
	*/
	public function setInternalName( $sValue ) { $this->m_sInternalName = $sValue; }
	protected function setPrefixDelimiter( $sValue ) { $this->m_sPrefixDelimiter = $sValue; }
	public function setHasBehaviors( $bValue ) { $this->m_bHasBehaviors = $bValue; }

	/**
	* Retrieves the behaviors attached to this component
	*
	* @returns array
	*/
	public function getBehaviors() { return $this->m_arBehaviors; }

	/**
	* Convenience functions to access the behavior assets
	*
	*/
	public function &hasBehaviorMethod( $sMethodName ) { return CPSCommonBase::hasBehaviorMethod( $this, $sMethodName ); }
	public function &hasBehaviorProperty( $sName ) { return CPSCommonBase::hasBehaviorProperty( $this, $sName ); }
	public function &getBehaviorProperty( $sName ) { return CPSCommonBase::getBehaviorProperty( $this, $sName); }
	public function setBehaviorProperty( $sName, $oValue ) { return CPSCommonBase::setBehaviorProperty( $this, $sName, $oValue ); }

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Attach behaviors during construction...
	*
	* @param CBaseController $oOwner
	*/
	public function __construct( $oOwner = null )
	{
		//	Call daddy...
		parent::__construct( $oOwner );

		//	Create our internal name
		$_sName = CPSCommonBase::createInternalName( $this );

		//	Attach our widget behaviors
		$this->attachBehavior( $_sName, 'pogostick.behaviors.CPSWidgetBehavior' );

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $_sName, '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'trace', $_sName );
		
		//	Preinitialize if available
		if ( method_exists( $this, 'preinit' ) )
			$this->preinit();
	}

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	/**
	* Yii widget init
	*
	*/
	public function init()
	{
		//	Call daddy
		parent::init();

		//	Get the id/name of this widget
		list( $this->name, $this->id ) = $this->resolveNameID();

		//	Call our behaviors init() method if they exist
		foreach ( $this->m_arBehaviors as $_oBehave )
		{
			//	Only call init() on our own behaviors
			if ( $_oBehave instanceof CPSComponentBehavior )
				call_user_method( 'init', $_oBehave );
		}
	}

	/***
	* Handles registration of scripts & css files...
	* @returns CClientScript Returns the current applications CClientScript object {@link CWebApplication::getClientScript}
	*/
	public function registerClientScripts()
	{
		//	Get the clientScript
		$_oCS = Yii::app()->getClientScript();

		//	Register a special CSS file if we have one...
		if ( ! $this->isEmpty( $this->cssFile ) )
			$_oCS->registerCssFile( Yii::app()->baseUrl . "{$this->cssFile}", 'screen' );

		//	Send upstream for convenience
		return $_oCS;
	}

	/**
	 * Attaches a list of behaviors to the component.
	 * Each behavior is indexed by its name and should be an instance of
	 * {@link IBehavior}, a string specifying the behavior class, or an
	 * array of the following structure:
	 * <code>
	 * array(
	 *     'class'=>'path.to.BehaviorClass',
	 *     'property1'=>'value1',
	 *     'property2'=>'value2',
	 * )
	 * </code>
	 * @param array list of behaviors to be attached to the component
	 */
	public function attachBehaviors( $arBehaviors )
	{
		foreach( $arBehaviors as $_sName => $_oBehave )
			$this->attachBehavior( $_sName, $_oBehave );
	}

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
			//	Set behavior object's internalName so it can recognize it's own options
			$_oObject->getOptionsObject()->setInternalName( $sName );

			$this->m_bHasBehaviors |= true;
			$this->m_arBehaviors[ $sName ][ CPSCommonBase::BEHAVIOR_META_OBJECT ] = $_oObject;

			//	Place valid options in here for fast checking...
			$this->m_arBehaviors[ $sName ][ CPSCommonBase::BEHAVIOR_META_VALID ] = array();

			//	Cache behavior methods for lookup speed
			$this->m_arBehaviors[ $sName ][ CPSCommonBase::BEHAVIOR_META_METHODS ] =
				array_merge(
					( null == $this->m_arBehaviors[ $sName ][ CPSCommonBase::BEHAVIOR_META_METHODS ] ) ? array() : $this->m_arBehaviors[ $sName ][ CPSCommonBase::BEHAVIOR_META_METHODS ],
					array_change_key_case( array_flip( array_values( get_class_methods( $_oObject ) ) ), CASE_LOWER
				)
			);

			//	Cache behavior members for lookup speed
			$this->m_arBehaviors[ $sName ][ CPSCommonBase::BEHAVIOR_META_VARS ] =
				array_merge(
					( null == $this->m_arBehaviors[ $sName ][ CPSCommonBase::BEHAVIOR_META_VARS ] ) ? array() : $this->m_arBehaviors[ $sName ][ CPSCommonBase::BEHAVIOR_META_VARS ],
					array_change_key_case( array_flip( array_keys( get_class_vars( get_class( $this ) ) ) ), CASE_LOWER
				)
			);
		}

		return $_oObject;
	}

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
	 */
	public function __get( $sName )
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
	 * @return mixed The method return value
	 */
	public function __call( $sName, $arParams )
	{
		$_oBehave = null;
		
		//	Check behavior methods...
		if ( $_oBehave = $this->hasBehaviorMethod( $sName ) )
			try { return call_user_func_array( array( $_oBehave[ CPSCommonBase::BEHAVIOR_META_OBJECT ], $sName ), $arParams ); } catch ( CException $_ex ) { /* Ignore and pass through */ $_oEvent = $_ex; }
			
		if ( $_oEvent && 1 == $_oEvent->getCode() )
			throw $_oEvent;

		//	Try parent first... cache exception
		return parent::__call( $sName, $arParams );
	}
}