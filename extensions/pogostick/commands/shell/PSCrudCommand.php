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
 * @since v1.0.6
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
 
Yii::import( 'system.cli.commands.shell.CrudCommand' );

define( 'POGOSTICK_CRUD_TEMPLATES', Yii::app()->basePath . '/templates/crud' );

class CPSCrudCommand extends CrudCommand
{
	public $templatePath = POGOSTICK_CRUD_TEMPLATES;
}
