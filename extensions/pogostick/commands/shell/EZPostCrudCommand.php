<?php
/**
 * CPSCrudCommand class file.
 *
 * @filesource
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage Commands
 * @since v1.0.4
 * @version SVN: $Revision: 395 $
 * @modifiedby $LastChangedBy: jerryablan@gmail.com $
 * @lastmodified  $Date: 2010-07-15 17:34:48 -0400 (Thu, 15 Jul 2010) $
 */
 
Yii::import( 'system.cli.commands.shell.CrudCommand' );

define( 'EZPOST_CRUD_TEMPLATES', Yii::app()->basePath . '/modules/ezpost/views/crud_templates' );

class EZPostCrudCommand extends CrudCommand
{
	public $templatePath = EZPOST_CRUD_TEMPLATES;
}