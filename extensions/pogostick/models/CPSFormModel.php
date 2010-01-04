<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSFormModel provides something selfish. I'm old and prefix my member variable names with 'm_'. This class looks for that.
 * 
 * @package 	psYiiExtensions
 * @subpackage 	models
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.4
 *  
 * @filesource
 * 
 * @property-read string $modelName The class name of the model
 */
class CPSFormModel extends CFormModel implements IPSBase
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