<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Quick little class that only overrides the label mechanism. This allows you to 
 * override the label from the original model in models that have relations.
 * 
 * For example, if you have a label in model Person called 'marital status' and in 
 * another related model you'd rather the label be 'Married?', this class allows you
 * to do so when sorting.
 * 
 * Simply prepend the model name to the attribute name, separated by a period in
 * your attributeLabels() method:
 * 
 * 	<code>
 * 	public function attributeLabels()
 * 	{
 * 		return array(
 * 			...
 * 			'person.marriage_ind' => 'Married?',
 * 			...
 * }
 * </code>
 * 
 * @package 	psYiiExtensions
 * @subpackage 	components
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSSort.php 354 2010-01-02 22:05:36Z jerryablan@gmail.com $
 * @since 		v1.0.6
 * 
 * @filesource
 */
class CPSSort extends CSort implements IPSBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Resolves the attribute label based on label definition in the AR class.
	* This will invoke {@link CActiveRecord::getAttributeLabel} to determine what label to use.
	* @param string the attribute name.
	* @return string the attribute label
	* @since 1.0.6
	*/
	public function resolveLabel( $sAttribute )
	{
		if ( false !== ( $_i = strpos( $sAttribute, '->' ) ) )
		{
			$_sColumn = substr( $sAttribute, $_i + 2 );

			if ( null === ( $_sLabel = CActiveRecord::model( $this->modelClass )->getAttributeLabel( $sAttribute ) ) )
			{
				$_oBaseModel = CActiveRecord::model( $this->modelClass );
				
				if ( null !== ( $_oRelation = $_oBaseModel->getActiveRelation( $_sColumn  ) ) )
					$_sLabel = CActiveRecord::model( $_oRelation->className )->getAttributeLabel( $_sColumn );
				else
					$_sLabel = $_oBaseModel->getAttributeLabel( $_sColumn );
			}
		}
		else
			$_sLabel = CActiveRecord::model( $this->modelClass )->getAttributeLabel( $sAttribute );
			
		return $_sLabel;
	}

}