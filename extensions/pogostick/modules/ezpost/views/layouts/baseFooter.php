		<div id="footer">
<?
	if ( $_bShowMenus )
	{
?>
				<div id="footmenu">
					<?php $this->widget('application.components.MainMenu', array(
						'items' => array(
							array( 'label' => 'privacy', 'url' => 'http://www.freeprivacypolicy.org/generic.php', 'htmlOptions' => array( 'target' => '_blank', 'title' => 'View our privacy policy' ) ),
							array( 'label' => 'contact', 'url' => array( '/contact' ), 'htmlOptions' => array( 'title' => 'Leave us some feedback' )  ),
							array( 'label' => 'WTF?', 'url' => array( '/about' ), 'htmlOptions' => array( 'title' => 'Click me for a good time' )  ),
						),
					)); ?>
				</div><!-- footmenu -->
<?php
	}
?>
				<div id="footcopy" <?=( !$_bShowMenus ? 'style="padding-top:10px;"' : '' )?>>Copyright &copy; <?=( date("Y") == 2009 ? date("Y") : '2009-' . date("Y") )?> by <a href='http://www.pogostick.com/'>Pogostick, LLC.</a>, All Rights Reserved.</div>
				<div id="es_sni_container">
					<span class="es_sni"><a href="http://www.gravatar.com" target="_blank" title="Get your Gravatar!" ><img title="Get a Gravatar!" border="0" src="<?=CPSHelp::getGravatarUrl( Yii::app()->user->getUser()->email_addr_text, 32 )?>" hspace="5" /></a></span>
				</div>
		</div><!-- footer -->
<!-- GA -->
<?=CPSActiveWidgets::googleAnalytics( Yii::app()->getParam( 'googleAnalyticsId' ) );?>