<?php
/**
 * CPSSort class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage components
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
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
 */
class CPSSort extends CSort
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
		if ( false !== ( $_i = strpos( $sAttribute, '.' ) ) )
		{
			$_sColumn = substr( $sAttribute, $_i + 1 );

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