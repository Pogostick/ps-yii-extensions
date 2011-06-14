<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
/**
 * CPSWidgetFactory is an enhanced version of Yii's CWidgetFactory
 *
 * CWidgetFactory is used as the default "widgetFactory" application component.
 * To use this class, you must add it to your configuration file as follows:
 *
 * <pre>
 * return array(
 *		...
 *		'components' => array(
 *			'widgetFactory' => array(
 *				'class' => 'CPSWidgetFactory',
 *			),
 *		),
 *		...
 * );
 * </pre>
 *
 * @package		psYiiExtensions
 * @subpackage 	helpers
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version		$Id$
 * @since			v1.2.0
 *
 * @filesource
 */
class CPSWidgetFactory extends CWidgetFactory
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Creates a new widget based on the given class name and initial properties.
	 * @param CBaseController $owner the owner of the new widget
	 * @param string $className the class name of the widget. This can also be a path alias (e.g. system.web.widgets.COutputCache)
	 * @param array $properties the initial property values (name=>value) of the widget.
	 * @return CWidget the newly created widget whose properties have been initialized with the given values.
	 */
	public function createWidget( $owner, $className, $properties = array() )
	{
		$className = Yii::import( $className, true );

		if ( isset( $this->widgets[$className] ) )
			$properties = ( array() === $properties ? $this->widgets[$className] : CMap::mergeArray( $this->widgets[$className], $properties ) );

		if ( $this->enableSkin )
		{
			if ( null === $this->skinnableWidgets || in_array( $className, $this->skinnableWidgets ) )
			{
				$_skinName = CPSHelperBase::o( $properties, 'skin', 'default' );

				if ( false !== $_skinName && array() !== ( $_skin = $this->getSkin( $className, $_skinName ) ) )
					$properties = ( array() === $properties ? $_skin : CMap::mergeArray( $_skin, $properties ) );
			}
		}

		//	Create our widget, pass in properties as well
		$_widget = new $className( $owner, $properties );

		//	Configure
		foreach ( $properties as $_key => $_value )
			$_widget->{$_key} = $_value;

		//	Return!
		return $_widget;
	}

}