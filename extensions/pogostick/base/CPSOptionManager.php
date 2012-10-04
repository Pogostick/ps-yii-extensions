<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link      http://www.pogostick.com Pogostick, LLC.
 * @license   http://www.pogostick.com/licensing
 */

/**
 * CPSOptionManager provides the base class for generic options settings for use with any class.
 * Avoids the need for declaring member variables and provides convenience magic functions to
 * search the options.
 *
 * Here is an example option specification and pattern.
 *
 * <code>
 * array(
 *        //    optionName is the name of your option key, optionally private (by appending an
 *         //    underscore ('_') to the option name. Private options are never returned from
 *         //    (
 *
 * @link                                                                                                                        makeOptions())
 *         'optionName{_}' => array(
 *             //    Option name for a javascript widget. Defaults to '<b>optionName</b>'.
 *            //    Defaults to null,
 *                                                                                                                              CPSOptionManager::META_DEFAULTVALUE => default value,
 *             //    Lay out the validation parameters
 *                                                                                                                              CPSOptionManager::META_RULES => array(
 *                 //    Any valid PHP type (i.e. string, array, integer, etc.),
 *                                                                                                                              CPSOptionManager::META_TYPE => 'typename',
 *                 //    Is option required?
 *                                                                                                                              CPSOptionManager::META_REQUIRED => true|false,
 *                 //    The external name of the option (i.e. what to send to component)
 *                                                                                                                              CPSOptionManager::META_EXTERNALNAME => external-facing name | empty
 *                 // An array of valid values for the option
 *                                                                                                                              CPSOptionManager::META_ALLOWED => array( 'v1', 'v2', 'v3', etc.)
 *         )
 * )
 * </code>
 *
 * <b>CPSOptionManager::META_EXTERNALNAME</b> tells the option manager to use this when formatting an options array with (@link CPSOptionManager::makeOptions()).
 *
 * <b>CPSOptionManager::META_DEFAULTVALUE</b> tells the option manager what to set the value of the option to upon creation. New values will override this default value.
 *
 * When adding options to the options manager, you must specify the pattern for the option's value.
 * The pattern must be placed in the array element with a key of '<b>CPSOptionManager::META_RULES</b>' to be recognized
 * by the option manager.
 *
 * This pattern can include none, one, or more pattern sub-types.
 *
 * These are:
 *     1. <b>CPSOptionManager::META_EXTERNALNAME</b>
 *  2. <b>CPSOptionManager::META_ALLOWED</b>
 *  3. <b>CPSOptionManager::META_REQUIRED</b>
 *
 * <b>CPSOptionManager::META_TYPE</b> tells the option manager what type of variable is allowed to be assigned to the option.
 * Any legal PHP type is allowed. If more than one type is allowed, you may send in an array as the
 * value of '<b>CPSOptionManager::META_EXTERNALNAME</b>'.
 *
 * <b>CPSOptionManager::META_ALLOWED</b> tells the option manager the valid values of the option that is being set. This must
 * be specified as an array. For instance, if an option can only have three possible values: '<b>public</b>',
 * '<b>protected</b>', or '<b>private</b>', the array specified for the value of '<b>CPSOptionManager::META_TYPE</b>'
 * would be:
 *
 * <code>
 * array( 'public', 'protected', 'private' )
 * </code>
 *
 * <b>CPSOptionManager::META_REQUIRED</b> tells the option manager that the option is required and as such, must have a non-null value.
 *
 * This next snippet defines an option named '<b>hamburgerCount</b>'. It is private because we've appended
 * an underscore to the name. It's default value is 6, it can be only of type '<b>integer</b>', and has no
 * required values.
 *
 * <code>
 *
 * 'hamburgerCount_' = array(
 *     CPSOptionManager::META_DEFAULTVALUE => 6,
 *     CPSOptionManager::META_RULES =>
 *         array(
 *             CPSOptionManager::META_TYPE => 'integer',
 *             CPSOptionManager::META_ALLOWED => null,
 *         ),
 *     );
 *
 * </code>
 *
 * Using this option from a Pogostick component or widget is as simple as this:
 *
 * <code>
 * $this->hamburgerCount = 3;
 * echo $this->hamburgerCount;
 * </code>
 *
 * Once you declare an option private (suffixing with the underscore as above, you no longer need to provide the underscore when
 * accessing the option. The underscore is used ONLY when adding new options and is dropped once added.
 *
 * @package                                                                                                                     psYiiExtensions
 * @subpackage                                                                                                                  base
 *
 * @author                                                                                                                      Jerry Ablan <jablan@pogostick.com>
 * @version                                                                                                                     SVN: $Id: CPSOptionManager.php 364 2010-01-04 06:33:35Z jerryablan@gmail.com $
 * @since                                                                                                                       v1.0.0
 *
 * @deprecated                                                                                                                  This has been replaced by the CPSOptionCollection
 */
class CPSOptionManager implements IPSBase
{
}