/**
 * <?=$className?> class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 What's Up Interactive, Inc.
 * @author Jerry Ablan <jablan@whatsup.com>
 * @link http://www.whatsup.com What's Up Interactive, Inc.
 * @package wui.modules
 * @subpackage ezpost
 * @since v1.0.0
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
class <?=$className;?> extends CEZPostController
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	public function init()
	{
		//	Phone home...
		parent::init();
		
		//	Set model name...
		$this->setModelName( '<?=$modelClass?$modelClass:$ID?>' );
	}

}
