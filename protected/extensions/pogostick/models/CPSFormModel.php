<?php
/**
 * CPSFormModel class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSFormModel provides something stupid
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @filesource
 * @package psYiiExtensions
 * @subpackage Models
 * @since 1.0.4
 */
class CPSFormModel extends CFormModel
{
	/**
	* Fixup attribute labels for my funky naming conventions...
	*
	* @param string $sName
	* @return mixed
	*/
	public function generateAttributeLabel( $sName )
	{
		if ( substr( $sName, 0, 2 ) == 'm_' )
			$sName = substr( $sName, 3 );

		return( parent::generateAttributeLabel( $sName ) );
	}
}