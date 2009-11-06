<?php
/**
 * This is the template for generating the controller class file for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $controllerClass: the controller class name
 * - $modelClass: the model class name
 */
 
$_sClass = $modelClass ? $modelClass : $ID;
 
echo <<<HTML
<?php
/**
 * {$controllerClass} class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC.
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage controllers
 * @since v1.0.6
 * @version SVN: \$Revision\$
 * @modifiedby \$LastChangedBy\$
 * @lastmodified  \$Date\$
 */
class {$controllerClass} extends CPSCRUDController
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	public function init()
	{
		//	Phone home...
		parent::init();
		
		//	Set model name...
		\$this->setModelName( '{$_sClass}' );
	}

	/**
	 * Default admin action. Change conditions for your system
	 *
	 */
	public function actionAdmin( \$arExtraParams = array(), \$oCriteria = null )
	{
		if ( \$oCriteria === null )
		{
			\$oCriteria = new CDbCriteria();
			\$oCriteria->condition = 'owner_sid = :owner_sid';
			\$oCriteria->params = array( ':owner_sid' => Yii::app()->user->id );
		}
		
		return parent::actionAdmin( \$arExtraParams, \$oCriteria );
	}

}
HTML;