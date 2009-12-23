<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Provides functionality helpful for parent/child relationships
 * 
 * @package 	psYiiExtensions
 * @subpackage 	behaviors
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 */
class CPSParentChildBehavior extends CActiveRecordBehavior implements IPogostick
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Returns whether or not this model has children
	* Override as necessary
	* 
	* @returns boolean
	*/
	public function hasChildren()
	{
		return false;
	}
	
	/**
	* Named scope to return child models
	* Override as necessary
	* 
	* @param integer $iParentId Default to null
	*/
	public function childOf( $iParentId = null )
	{
		return $this;
	}
	
	/**
	* Outputs a string of UL/LI tags from an array of models suitable
	* for menu structures
	* 
	* @param array $arModel
	* @param array $arOptions
	* @return string
	*/
	public function asUnorderedList( $arModel = array(), $arOptions = array() )
	{
		static $_bInit;
		static $_sValColumn;
		static $_sKeyColumn;
		static $_sChildrenRelation;
		static $_bLinkText;

		$_sClass = $_sId = $_sOut = null;
		
		if ( ! $_bInit )
		{
			$_sId = PS::o( $arOptions, 'id', null, true );
			$_bLinkText = PS::o( $arOptions, 'linkText', true, true );
			$_sKeyColumn = PS::o( $arOptions, 'keyColumn', 'id', true );
			$_sValColumn = PS::o( $arOptions, 'valueColumn', null, true );
			$_sChildrenRelation = PS::o( $arOptions, 'childrenRelation', 'children', true );
			$_sClass = PS::o( $arOptions, 'class', null, true );

			$_bInit = true;
		}

		//	If no model array was specified, get top level
		$_arModel = PS::nvl( $arModel, $this->childOf( null )->findAll() );
		
		if ( ! empty( $_arModel ) )
		{
			//	Loop...
			foreach ( $arModel as $_oModel )
			{
				//	Does this model have relational kids?
				$_bHasKids = in_array( $_sChildrenRelation, array_keys( $_oModel->relations() ) ) && $_oModel->hasChildren();
				$_sOut .= PS::tag( 'li', array(), ( $_bLinkText ? PS::link( PS::encode( $_oModel->{$_sValColumn} ), '#' ) : PS::encode( $_oModel->{$_sValColumn} ) ) . ( $_bHasKids ? $_oModel->asUnorderedList( $_oModel->{$_sChildrenRelation}, $arOptions ) : null ) );
			}
		}
		
		$_arOpts = array();
		if ( $_sId ) $_arOpts['id'] = $_sId;
		if ( $_sClass ) $_arOpts['class'] = $_sClass;

		return PS::tag( 'ul', $_arOpts, $_sOut );
	}
	
}