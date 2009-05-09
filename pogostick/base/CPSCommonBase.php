<?php
/**
 * CPSCommonBase.php class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSCommonBase provides methods that are common to both CComponent and CWidget.
 *
 * Since these two classes do not share a common base far enough up the subclasses tree,
 * it is more modular to have this class provide the common methods necessary between
 * our (@link CPSWidget) and (@link CPSComponent).
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Base
 * @since 1.0.0
 */
class CPSCommonBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	//********************************************************************************
	//* Magic Method Stubs
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
	 * @param CComponent|CWidget $oObject The the calling object
	 * @param string the property name or event name
	 * @return mixed the property value, event handlers attached to the event, or the named behavior
	 * @throws CException if the property or event is not defined
	 * @see (@link genericSet)
	 * @see __set
	 * @static
	 */
	public static function genericGet( $oObject, $oParent = null, $oEvent = null, $sName )
	{
		//	Direct getter method...
		$_sGetter = 'get' . $sName;
		if ( method_exists( $oObject, $_sGetter ) )
			return $oObject->{$_sGetter}();

		//	Are you my daddy?
		if ( $oParent )
			try { return $oParent->__get( $sName ); } catch ( CException $_ex ) { /* Ignore and pass through */ $oEvent = $_ex; }

		//	Check behavior getter methods...
		return self::getBehaviorProperty( $oObject, $sName );

		//	Invalid property...
		if ( null != $oEvent )
			throw $oEvent;
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
	 * @param CComponent|CWidget $oObject The the calling object
	 * @param string $sName The property name or the event name
	 * @param mixed $oValue The property value or callback
	 * @throws CException if the property/event is not defined or the property is read only.
	 * @see __get
	 * @static
	 */
	public static function genericSet( $oObject, $oParent = null, $oEvent = null, $sName, $oValue )
	{
		//	Direct getter method...
		$_sSetter = 'set' . $sName;
		if ( method_exists( $oObject, $_sSetter ) )
			return $oObject->{$_sSetter}( $oValue );

		//	Are you my daddy?
		if ( $oParent )
			try { return $oParent->__set( $sName, $oValue ); } catch ( CException $_ex ) { /* Ignore and pass through */ $oEvent = $_ex; }

		//	Check behavior getter methods...
		return self::setBehaviorProperty( $oObject, $sName, $oValue );

		//	Invalid property...
		if ( null != $oEvent )
			throw $oEvent;
	}

	/**
	 * Calls the named method which is not a class method.
	 *
	 * @param CComponent|CWidget $oObject The the calling object
	 * @param string the method name
	 * @param array method parameters
	 * @return mixed the method return value
	 * @throws CException if the property/event is not defined or the property is read only.
	 * @see __call
	 * @static
	 */
	public function genericCall( $oObject, $oParent = null, $oEvent = null, $sName, $arParams )
	{
		//	Are you my daddy?
		if ( $oParent )
			try { return $oParent->__call( $sName, $arParams ); } catch ( CException $_ex ) { /* Ignore and pass through */ $oEvent = $_ex; }

		//	Check behavior methods...
		if ( $_oBehave = self::hasBehaviorMethod( $oObject, $sName ) )
				return call_user_func_array( array( $_oBehave[ '_object' ], $sName ), $arParams );

		//	Invalid property...
		if ( null != $oEvent )
			throw $oEvent;
	}

	/**
	* Test to see if a behavior has a particular method available
	*
	* @param object Component or widget to work with
	* @param string $sMethodName Name to look for
	* @returns null|(@link CPSOptionManager)
	* @static
	* @see hasBehaviorProperty
	*/
	public static function &hasBehaviorMethod( $oComponent, $sMethodName )
	{
		if ( ( $oComponent instanceof CPSComponent || $oComponent instanceof CPSWidget ) && $oComponent->hasBehaviors )
		{
			foreach ( $oComponent->getBehaviors() as $_sKey => $_oBehave )
			{
				if ( in_array( strtolower( $sMethodName ), $_oBehave[ '_classMethods' ] ) )
					return $_oBehave;
			}
		}

		return null;
	}

	/**
	 * Determines whether a behavior property is defined.
	 * A behavior property is defined if there is a getter or setter method
	 * defined in the behavior class. Note, property names are case-insensitive.
	 * @param string the property name
	 * @return boolean whether the property is defined
	 * @see hasBehaviorMethod
	 * @static
	 */
	public static function &hasBehaviorProperty( $oComponent, $sName )
	{
		if ( ( $oComponent instanceof CPSComponent || $oComponent instanceof CPSWidget ) && $oComponent->hasBehaviors )
		{
			$_arBehaviors = $oComponent->getBehaviors();

			foreach ( $_arBehaviors as $_sKey => $_oBehave )
			{
				if ( in_array( strtolower( $sName ), $_oBehave[ '_classVars' ] ) )
					return $_oBehave;

				//	Check options
				if ( $_oBehave->hasOption( $sName ) )
					return $_oBehave;
			}
		}

		return null;
	}

	/**
	* Returns a property from an attached behavior or throws an exception which can be caught
	*
	* @param mixed $oComponent The component or widget to work with
	* @param string $sName
	* @returns mixed
	* @static
	* @see setBehaviorProperty
	* @see hasBehaviorProperty
	* @throws CException
	*/
	public static function getBehaviorProperty( $oComponent, $sName )
	{
		//	Do we have that somewhere?
		if ( $_oBehave = $oComponent->hasBehaviorProperty( $sName ) )
			return $_oBehave[ '_object' ]->getOption( $oComponent->namePrefix . $sName );

		//	This exception can be ignored upstream...
		throw new CException( Yii::t( 'yii', 'Behavior Property "{class}.{property}" is not defined.', array( '{class}' => get_class( $oComponent ), '{property}' => $sName ) ) );
	}

	/**
	* Sets a property in an attached behavior if it exists or throws a catchable exception
	*
	* @param mixed $oComponent The component or widget to work with
	* @param string $sName
	* @param mixed $oValue
	* @static
	* @see getBehaviorProperty
	* @see hasBehaviorProperty
	* @throws CException
	*/
	public static function setBehaviorProperty( $oComponent, $sName, $oValue )
	{
		//	If a behavior contains
		if ( $_oBehave = self::hasBehaviorProperty( $oComponent, $sName ) )
		{
			$_oBehave[ '_object' ]->setOption( $oComponent->namePrefix . $sName, $oValue );
			return;
		}

		//	Throw exception
		throw new CException( Yii::t( 'yii', 'Behavior Property "{class}.{property}" is not defined.', array( '{class}' => get_class( $oComponent ), '{property}' => $sName ) ) );
	}

	/**
	* Outputs to the log and optionally throws an exception
	*
	* @param string $sMessage The message to log. Supports the '{class}' parameter substitution.
	* @param string $sLevel The level of the logged message. Defaults to 'info'
	* @param string $sExceptionMessage If supplied, will throw an exception
	* @param string $sCategory The category of the logged message. Defaults to 'application'
	* @throws CException
	* @returns null
	*/
	public static function writeLog( $sMessage, $sLevel = 'info', $sCategory = 'application', $sExceptionMessage = null )
	{
		//	Create the message
		$_sLogMessage = Yii::t( $sCategory, $sMessage, array( '{class}' => __CLASS__ ) );

		//	Log the message
		if ( 'trace' == $sLevel )
			Yii::trace( $_LogMessage, $sCategory );
		else
			Yii::log( $_LogMessage, $sLevel, $sCategory );

		//	Make sure we have the proper support
		if ( null != $sExceptionMessage )
			throw new CException( $sExceptionMessage, $sLevel, $sCategory );
	}

}