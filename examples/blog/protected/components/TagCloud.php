<?php
/*
 * This file is part of psYiiExtensions blog example
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
/**
 * @package 	psYiiExtensions.examples.blog
 * @subpackage 	components
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 */
class TagCloud extends CPSPortlet
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Initialize
	* 
	*/
	public function preinit()
	{
		parent::preinit();
		$this->title = 'Tags';
	}
	
	/**
	* Get our recent data
	* 
	*/
	public function getTagWeights()
	{
		return Tag::model()->findTagWeights();
	}
 
}