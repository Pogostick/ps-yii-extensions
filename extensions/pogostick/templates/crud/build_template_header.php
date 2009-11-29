<?php
/**
 * build_template_header.php file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage templates.crud
 * @since v1.0.6
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */

//	Get the defaults from the config file...
if ( ! $_sCopyright = Yii::app()->params['@copyright'] ) $_sCopyright = 'Copyright &copy; ' . date( 'Y' ) . ' You!';
if ( ! $_sAuthor = Yii::app()->params['@author'] ) $_sAuthor = 'Your Name <your@email.com>';
if ( ! $_sLink = Yii::app()->params['@link'] ) $_sLink = 'http://wwww.you.com';
if ( ! $_sPackage = Yii::app()->params['@package'] ) $_sPackage = Yii::app()->id;
if ( ! isset( $baseClass ) ) $baseClass = 'CPSModel';
if ( ! isset( $dbToUse ) ) $dbToUse = 'db';

//	Output the header...
echo <<<HTML
<?php
/**
 * $className file.
 *
 * @filesource
 * @copyright {$_sCopyright}
 * @author {$_sAuthor}
 * @link {$_sLink}
 * @package {$_sPackage}
 * @subpackage 
 * @version \$Revision\$
 * @modifiedby \$LastChangedBy\$
 * @lastmodified  \$Date\$
 */

HTML;
