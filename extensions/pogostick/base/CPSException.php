<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link      http://www.pogostick.com Pogostick, LLC.
 * @license   http://www.pogostick.com/licensing
 * @filesource
 */

/**
 * Generic exception
 *
 * @package           psYiiExtensions
 * @subpackage        base.exceptions
 *
 * @author            Jerry Ablan <jablan@pogostick.com>
 * @version           SVN $Id: CPSException.php 358 2010-01-02 23:33:40Z jerryablan@gmail.com $
 * @since             v1.0.4
 */
class CPSException extends CException implements IPSBase
{
}

/**
 * API exception
 *
 * @package           psYiiExtensions
 * @subpackage        base.exceptions
 *
 * @author            Jerry Ablan <jablan@pogostick.com>
 * @version           SVN $Id$
 * @since             v1.1.0
 */
class CPSApiException extends CPSException
{
}
