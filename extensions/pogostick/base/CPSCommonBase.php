<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSCommonBase provides methods that are common to both CPSComponent and CPSWidget.
 *
 * Since these two classes do not share a common base far enough up the subclasses tree,
 * it is more modular to have this class provide the common methods necessary between
 * our (@link CPSWidget) and (@link CPSComponent).
 * 
 * @package 	psYiiExtensions
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.0
 */
class CPSCommonBase implements IPogostickBehaviorBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Creates the internal name of a component/widget. Use (@link setInternalName) to change.
	* @param IPogostick $oComponent
	*/
	public static function createInternalName( IPogostick &$oComponent )
	{
		//	Get the class...
		$_sClass = ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) ? get_called_class() : get_class( $oComponent );

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
	* @param IPogostick Component or widget to work with
	* @param string $sMethodName Name to look for
	* @returns null|(@link CPSOptionManager)
	* @static
	* @see hasBehaviorProperty
	*/
	public static function &hasBehaviorMethod( IPogostick $oComponent, $sMethodName )
	{
		if ( $oComponent->getHasBehaviors() && null !== ( $_arBehaviors = $oComponent->getBehaviors() ) )
		{
			foreach ( array_keys( $_arBehaviors ) as $_sKey )
			{
				if ( false !== ( $_sKey = array_search( strtolower( $sMethodName ), $_arBehaviors[ $_sKey ][ self::BEHAVIOR_META_METHODS ] ) ) )
					return $_arBehaviors[ $_sKey ][ self::BEHAVIOR_META_METHODS ];
			}
		}

		return null;
	}

	/**
	 * Determines whether a behavior property is defined.
	 * A behavior property is defined if there is a getter or setter method
	 * defined in the behavior class. Note, property names are case-insensitive.
	 * @param IPogostick Com
	 * @param string the property name
	 * @return boolean whether the property is defined
	 * @see hasBehaviorMethod
	 * @static
	 */
	public static function &hasBehaviorProperty( IPogostick $oComponent, $sName )
	{
		if ( $oComponent->getHasBehaviors() )
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
		else if ( $oComponent->hasOption( $sName ) )
		{
			return $oComponent;
		}
		
		return null;
	}

	/**
	* Returns a property from an attached behavior or throws an exception which can be caught
	*
	* @param IPogostick $oComponent The component or widget to work with
	* @param string $sName
	* @returns mixed
	* @static
	* @see setBehaviorProperty
	* @see hasBehaviorProperty
	* @throws CException
	*/
	public static function &getBehaviorProperty( IPogostick $oComponent, $sName )
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
	* @param IPogostick $oComponent The component or widget to work with
	* @param string $sName
	* @param mixed $oValue
	* @static
	* @see getBehaviorProperty
	* @see hasBehaviorProperty
	* @throws CException
	*/
	public static function setBehaviorProperty( IPogostick $oComponent, $sName, $oValue )
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