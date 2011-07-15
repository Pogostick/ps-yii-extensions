<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSFormModel provided something selfish for me. I'm old and lazy and used to prefix my member variable names with 'm_' (Yeah, I used to do a lot of MS stuff).
 * This class would look for that when doing gets/sets. In any case, I've embraced non-hungarian notation and this is now just an unnecessary layer. Therefore,
 * deprecated.
 * 
 * @package 	psYiiExtensions
 * @subpackage 	models
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSFormModel.php 364 2010-01-04 06:33:35Z jerryablan@gmail.com $
 * @since 		v1.0.4
 *  
 * @filesource
 * 
 * @property-read string $modelName The class name of the model
 * @deprecated Removing in 1.1.0
 */
class CPSFormModel extends CFormModel implements IPSBase
{
}