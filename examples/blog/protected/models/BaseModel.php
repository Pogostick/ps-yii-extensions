<?php
/*
 * This file is part of psYiiExtensions Blog example
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * The base model for our blog
 * 
 * @package 	psYiiExtensions.examples.blog
 * @subpackage 	models
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.0
 * 
 * @filesource
 */
class BaseModel extends CPSModel
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/***
	* Sets our default behaviors. 
	* We want our base model to have timestamping
	* @returns array
	* @see CModel::behaviors
	*/
	public function behaviors()
	{
		return array_merge(
			parent::behaviors(),
			array(
				//	Timestampper
				'psTimeStamp' => array(
					'class' => 'pogostick.behaviors.CPSTimeStampBehavior',
					'createdColumn' => 'create_date',
					'lmodColumn' => 'lmod_date',
				),
			)
		);
	}

}