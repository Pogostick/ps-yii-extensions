<?php
/**
 * CPSCommonBase.php class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */
 
Yii::import( 'pogostick.components.*' );

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
	//* Constants
	//********************************************************************************

	const BEHAVIOR_META_METHODS = '_classMethods';
	const BEHAVIOR_META_OBJECT = '_object';
	const BEHAVIOR_META_VALID = '_validOptions';
	const BEHAVIOR_META_VARS = '_classVars';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Creates the internal name of a component/widget. Use (@link setInternalName) to change.
	*
	* @param CPSWidget|CPSComponent The
	*/
	public static function createInternalName( &$oComponent )
	{
		//	Get the class...
		$_sClass = get_class( $oComponent );

		//	Set names (with a little Pogostick magic!)
		$_sIntName = ( false !== strpos( $_sClass, 'CPS', 0 ) ) ? str_replace( 'CPS', 'ps', $_sClass ) : $_sClass;

		//	Set the names inside the object
		$oComponent->setInternalName( $_sIntName );

		//	Return internal name
		return $_sIntName;
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
		$_oBehavior = null;
		
		if ( ( $oComponent instanceof CPSComponent || $oComponent instanceof CPSWidget ) && $oComponent->hasBehaviors )
		{
			foreach ( $oComponent->getBehaviors() as $_sKey => $_oBehave )
			{
				if ( is_array( $_oBehave[ self::BEHAVIOR_META_METHODS ] ) && in_array( strtolower( $sMethodName ), $_oBehave[ self::BEHAVIOR_META_METHODS ] ) )
				{
					$_oBehavior = $_oBehave;
					break;
				}
			}
		}

		return $_oBehavior;
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
		if ( ( $oComponent instanceof CPSComponent || $oComponent instanceof CPSWidget ) && $oComponent->getHasBehaviors() )
		{
			$_arBehaviors = $oComponent->getBehaviors();

			foreach ( $_arBehaviors as $_sKey => $_oBehave )
			{
				if ( is_array( $_oBehave[ self::BEHAVIOR_META_VARS ] ) && in_array( strtolower( $sName ), $_oBehave[ self::BEHAVIOR_META_VARS ] ) )
					return $_oBehave;

				//	Check options
				if ( $_oBehave->hasOption( $sName ) )
					return $_oBehave;
			}
		}
		else if ( $oComponent instanceof CPSComponentBehavior )
		{
			//	Check options
			if ( $oComponent->hasOption( $sName ) )
				return $oComponent;
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
	public static function &getBehaviorProperty( $oComponent, $sName )
	{
		//	Do we have that somewhere?
		if ( $_oBehave = $oComponent->hasBehaviorProperty( $sName ) )
			return $_oBehave[ self::BEHAVIOR_META_OBJECT ]->getOption( $oComponent->getNamePrefix() . $sName );

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
			$_oBehave[ self::BEHAVIOR_META_OBJECT ]->setOption( $oComponent->getNamePrefix() . $sName, $oValue );
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
			Yii::trace( $_sLogMessage, $sCategory );
		else
			Yii::log( $_sLogMessage, $sLevel, $sCategory );

		//	Make sure we have the proper support
		if ( null != $sExceptionMessage )
			throw new CException( $sExceptionMessage, $sLevel, $sCategory );
	}
}