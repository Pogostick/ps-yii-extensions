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
	//* Member variables
	//********************************************************************************

	protected $m_sInternalName;
	protected $m_sNamePrefix;
	protected $m_bHasBehaviors = false;
	protected $m_arBehaviors = null;

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	public function getInternalName() { return( $this->m_sInternalName ); }
	public function setInternalName( $sValue ) { $this->m_sInternalName = $sValue; }

	public function getNamePrefix() { return( $this->m_sNamePrefix ); }
	public function setNamePrefix( $sValue ) { $this->m_sNamePrefix = $sValue; }

	public function getHasBehaviors() { return( $this->m_bHasBehaviors ); }
	public function setHasBehaviors( $bValue ) { $this->m_bHasBehaviors = $bValue; }

	public function getBehaviors() { return( $this->m_arBehaviors ); }

	public function &hasBehaviorMethod( $sMethodName ) { return CPSCommonBase::hasBehaviorMethod( $this, $sMethodName ); }
	public function &hasBehaviorProperty( $sName ) { return CPSCommonBase::hasBehaviorProperty( $this, $sName ); }
	public function getBehaviorProperty( $sName ) { return CPSCommonBase::getBehaviorProperty( $this, $sName); }
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

		//	Import behaviors
		Yii::import( 'pogostick.behaviors.CPSComponentBehavior' );
		Yii::import( 'pogostick.behaviors.CPSWidgetBehavior' );
		Yii::import( 'pogostick.behaviors.CPSApiBehavior' );
		Yii::import( 'pogostick.behaviors.CPSApiWidgetBehavior' );

		//	Make our internal name...
		$this->createInternalName();

		//	Attach our widget behaviors
		$this->attachBehavior( $this->m_sInternalName, 'pogostick.behaviors.CPSWidgetBehavior' );

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $this->getInternalName(), '{class} constructed', array( "{class}" => $_sClass ) ), 'trace', $this->getInternalName() );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Creates the internal name of the widget. Use (@link setInternalName) to change.
	* @see setInternalName
	*/
	protected function createInternalName()
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
		if ( ! empty( $this->cssFile ) )
			$_oCS->registerCssFile( Yii::app()->baseUrl . "{$this->cssFile}", 'screen' );

		//	Send upstream for convenience
		return( $_oCS );
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
			$this->m_arBehaviors[ $sName ][ '_object' ] = $_oObject;

			//	Place valid options in here for fast checking...
			$this->m_arBehaviors[ $sName ][ '_validOptions' ] = array();

			//	Cache behavior methods for lookup speed
			$this->m_arBehaviors[ $sName ][ '_classMethods' ] =
				array_merge(
					( null == $this->m_arBehaviors[ $sName ][ '_classMethods' ] ) ? array() : $this->m_arBehaviors[ $sName ][ '_classMethods' ],
					array_change_key_case( array_flip( array_values( get_class_methods( $_oObject ) ) ), CASE_LOWER
				)
			);

			//	Cache behavior members for lookup speed
			$this->m_arBehaviors[ $sName ][ '_classVars' ] =
				array_merge(
					( null == $this->m_arBehaviors[ $sName ][ '_classVars' ] ) ? array() : $this->m_arBehaviors[ $sName ][ '_classVars' ],
					array_change_key_case( array_flip( array_keys( get_class_vars( get_class( $this ) ) ) ), CASE_LOWER
				)
			);
		}

		return $_oObject;
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

	//********************************************************************************
	//* Magic Methods
	//********************************************************************************

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

		//	Check behavior properties
		return $this->getBehaviorProperty( $sName );
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
		//	Let parent take a stab. He'll check getter/setters and behavior methods
		try { return parent::__set( $sName, $oValue ); } catch ( CException $_ex ) { /* Ignore and pass through */ $_oEvent = $_ex; }

		//	Check behavior properties
		return $this->setBehaviorProperty( $sName, $oValue );
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
