<?php
/*
 * This file is part of psYiiExtensions examples
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * 
 * @copyright Copyright &copy; 2009 What's Up Interactive, Inc.
 * @link http://www.whatsup.com What's Up Interactive, Inc.
 * 
 * @copyright Copyright &copy; 2009 InTopic Media, LLC
 * @link http://www.intopicmedia.com InTopic Media, LLC.
 */
/**
 * @package 	blog
 * @subpackage 	config
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 */

//	Our application parameters
return array(

	//	This is displayed in the header section
	'title' => 'My Yii Blog',
	
	//	This is used in the error pages
	'adminEmail' => 'webmaster@example.com',
	
	//	The number of posts displayed per page
	'postsPerPage' => 10,
	
	//	Whether post comments need to be approved before published
	'commentsNeedApproval' => true,
	
	//	The copyright information displayed in the footer section
	'copyrightInfo' => 'Copyright &copy; ' . date('Y') . ' by My Company.',

	//	Our default theme
	'theme' => 'ui-lightness',

	//	Defaults for phpDoc scaffolding. Edit accordingly
	'@copyright' => 'Copyright &copy; ' . date('Y') . ' My Company, LLC.',
	'@author' => 'Web Master <webmaster@example.com>',
	'@link' => 'http://www.example.com',
	'@package' => 'blog',
);