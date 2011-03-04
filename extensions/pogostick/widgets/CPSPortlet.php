<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Portlet class written for pYe
 * 
 * @package 	psYiiExtensions
 * @subpackage 	widgets
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSPortlet.php 367 2010-01-16 04:29:24Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 * @abstract
 * 
 * @property string $title
 * @property string $cssClass
 * @property string $headerCssClass
 * @property string $contentCssClass
 * @property boolean $visible
 * @property boolean $autoRender
 */
abstract class CPSPortlet extends CPSWidget
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Preinitialize
	*/
	public function preinit()
	{
		//	Home first...
		parent::preinit();
		
		//	Add our options
 		$this->addOptions( self::getBaseOptions() );
	}
	
	/**
	* Initialize ourself
	*/
	public function init()
	{
		//	Call daddy
		parent::init();
		
		//	Start it off...
		$this->generateHtml();
	}
	
	/**
	* Run our widget
	*/
	public function run()
	{
		if ( ! $this->visible )
			return;
		
		$this->renderContent();
		
		echo '	</div><!-- ' . $this->contentCssClass . ' -->' . PHP_EOL;
		echo '</div><!-- ' . $this->cssClass . ' -->';
	}
	
	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Generate our widget's html
	* @return string
	*/
	protected function generateHtml()
	{
		if ( ! $this->visible )
			return;

		if ( $this->jqui ) 
		{
			$this->headerCssClass = trim( 'ui-widget-header ' . $this->headerCssClass );
			$this->contentCssClass = trim( 'ui-widget-content ' . $this->contentCssClass );
		}
			
		echo PS::openTag( 'div', array( 'class' => $this->cssClass ) ) . PHP_EOL;
		if ( $this->title ) echo PS::tag( 'div', array( 'class' => $this->headerCssClass ), $this->title ) . PHP_EOL;
		echo PS::openTag( 'div', array( 'class' => $this->contentCssClass ) ) . PHP_EOL;
	}
	
	/**
	* Renders the content of this portlet.
	* If object is set to auto-render, the view with the same name as this portlet will be rendered when called
	* @return string
	*/
	protected function renderContent()
	{
		$_sClass = get_class( $this );
		$_sClass[0] = strtolower( $_sClass[0] );
		if ( $this->autoRender ) $this->render( $_sClass );
	}
	
	/**
	* Our options
	* @return array
	*/
	private function getBaseOptions()
	{
		return array(
			'title_' => 'string',
			'cssClass_' => 'string:portlet-wrapper',
			'headerCssClass_' => 'string:portlet-header',
			'contentCssClass_' => 'string:portlet-content',
			'visible_' => 'bool:true',
			'autoRender_' => 'bool:true',
			'jqui_' => 'bool:true',
		);
	}
}