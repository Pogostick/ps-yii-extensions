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
 
Yii::import( 'system.cli.commands.shell.ModelCommand' );

define( 'POGOSTICK_MODEL_TEMPLATES', Yii::getPathOfAlias('pogostick') . '/templates' );

class PSModelCommand extends ModelCommand
{
	public function __construct()
	{
		$this->templatePath = POGOSTICK_MODEL_TEMPLATES;
	}
}
