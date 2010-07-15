<?php
/**
 * CPSCrudCommand class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage Commands
 * @since v1.0.4
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
 
Yii::import( 'system.cli.commands.shell.CrudCommand' );

define( 'EZPOST_CRUD_TEMPLATES', Yii::app()->basePath . '/modules/ezpost/views/crud_templates' );

class EZPostCrudCommand extends CrudCommand
{
	public $templatePath = EZPOST_CRUD_TEMPLATES;
}