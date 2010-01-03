<?php
	$_sTheme = Yii::app()->user->getState( CPSjqUIWrapper::getStateName() );
	CPSjqUIWrapper::loadScripts( null, $_sTheme );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />
	<?php echo CHtml::cssFile( Yii::app()->baseUrl . '/css/main.css' ); ?>
	<?php echo CHtml::cssFile( Yii::app()->baseUrl . '/css/post.css' ); ?>
	<?php echo CHtml::cssFile( Yii::app()->baseUrl . '/css/portlet.css' ); ?>
	<title><?php echo $this->pageTitle; ?></title>
</head>

<body class="page ui-widget">

	<div class="page-wrapper ui-widget-content">

		<div class="page-header ui-widget-header">
    		<h1><?php echo PS::link( PS::encode( Yii::app()->params['title'] ), Yii::app()->homeUrl ); ?></h1>
  		</div><!-- header -->

  		<div class="page-content-wrapper ui-widget-content">

			<div class="page-content">
				<?php echo $content; ?>
			</div><!-- content -->

  			<div class="page-sidebar ui-widget-content">
				<?php $this->widget( 'UserLogin', array( 'visible' => Yii::app()->user->isGuest ) ); ?>
				<?php $this->widget( 'UserMenu', array( 'visible' => ! Yii::app()->user->isGuest ) ); ?>
				<?php $this->widget( 'PostCalendar' ); ?>
				<?php $this->widget( 'ThemeRoller' ); ?>
				<?php $this->widget( 'TagCloud' ); ?>
				<?php $this->widget( 'RecentComments' ); ?>
			</div><!-- sidebar -->
				
			<div class="ui-helper-clearfix" ></div>
		</div>

		<div class="page-footer ui-widget-header">
			<p><?php echo Yii::app()->params['copyrightInfo']; ?><br/>
			All Rights Reserved.<br/>
			<?php echo Yii::powered(); ?></p>
		</div><!-- footer -->
		
	</div><!-- container -->

</body>

</html>