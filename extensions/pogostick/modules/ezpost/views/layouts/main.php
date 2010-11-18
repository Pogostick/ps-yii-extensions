<?php
/**
 * main.php layout file
 *
 * @filesource
 * @copyright Copyright &copy; 2009 What's Up Interactive, Inc.
 * @author Jerry Ablan <jablan@whatsup.com>
 * @link http://www.whatsup.com What's Up Interactive, Inc.
 * @package psYiiExtensions
 * @subpackage ezpost
 * @since v1.0.0
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
/**
 * Provides the layout for this module
 *
 * @package psYiiExtensions
 * @subpackage ezpost
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/popup.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/icons.css" />
</head>

<body>
	<div id="page">
		<?
			$_bShowMenus = true;
			require_once( Yii::app()->controller->module->getLayoutPath() . '/baseHeader.php' );
		?>

		<div id="content">
			<?php echo $content; ?>
		</div><!-- content -->

		<?
			require_once( Yii::app()->controller->module->getLayoutPath() . '/baseFooter.php' );
		?>

	</div><!-- page -->
</body>
</html>