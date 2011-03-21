<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSActiveForm
 * A replacement, easier-to-use, active form widget
 *
 * @package 	psYiiExtensions
 * @subpackage	widgets
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.0
 *
 * @filesource
 */
class CPSActiveForm extends CActiveForm
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	protected $_rowFormat = '{label}{field}';

	public $wide = false;
	public $fields = array();

	/**
	 * @var CModel The model for this form
	 */
	protected $_formModel = null;
	public function getFormModel() { return $this->_formModel; }
	public function setFormModel( $value ) { $this->_formModel = $value; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		$this->_buildForm();
		return parent::run();
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Creates the form elements based on the fields entry in the passed in properties
	 * @param array $properties
	 */
	protected function _buildForm()
	{
		$_fieldList = $this->fields;
		$_model = $this->_formModel;
		$_wide = $this->wide;

		echo PS::openTag( 'div', array( 'class' => 'form' . ( $_wide ? ' wide' : null ) ) );

		foreach ( $_fieldList as $_key => $_property )
		{
			//	Pull out div options and open
			$_divOptions = PS::o( $_property, 'row', array( 'class' => 'row' ), true );
			echo PS::openTag( 'div', $_divOptions );

			try
			{
				//	Pull out label and field info
				$_label = PS::o( $_property, 'label', array(), true );
				$_field = PS::o( $_property, 'field', $_property, true );
				$_innerModel = PS::o( $_property, 'model', null, true );

				//	Field type and options should be all that are left
				$_fieldType = PS::o( $_field, 'type', 'textField', true );

				//	For HTML elements, just echo what's left in the array
				if ( 'html' === strtolower( $_key ) )
					echo PS::o( $_property, 0 );
				else
				{
					echo $this->label( $_innerModel ? $_innerModel : $_model, $_key, $_label );
					array_unshift( $_field, $_model, $_key );
					echo call_user_func_array( array( $this, $_fieldType ), $_field );
				}
			}
			catch ( Exception $_ex )
			{
				CPSLog::error( __METHOD__, 'Exception while building form: ' . $_ex->getMessage() );
			}

			echo PS::closeTag( 'div' );
		}

		echo PS::closeTag( 'div' );
	}
}
