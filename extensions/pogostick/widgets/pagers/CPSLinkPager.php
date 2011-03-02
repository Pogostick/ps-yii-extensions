<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Basic link pager with jQuery UI icons
 * 
 * @package 	psYiiExtensions.widgets
 * @subpackage 	pagers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSLinkPager.php 367 2010-01-16 04:29:24Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 * @todo This is a work in progress
 */
class CPSLinkPager extends CLinkPager implements IPSBase
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	* Mapping of jQuery UI icons to page classes
	* 
	* @var array
	* @access protected
	*/
	protected $m_arIcons = array(
		self::CSS_FIRST_PAGE => array( 'icon' => 'arrowthickstop-1-w', 'position' => 'left' ),
		self::CSS_LAST_PAGE => array( 'icon' => 'arrowthickstop-1-e', 'position' => 'right' ),
		self::CSS_PREVIOUS_PAGE => array( 'icon' => 'arrowthick-1-w', 'position' => 'left' ),
		self::CSS_NEXT_PAGE => array( 'icon' => 'arrowthick-1-e', 'position' => 'right' ),
		self::CSS_INTERNAL_PAGE => array(),
		self::CSS_HIDDEN_PAGE => array(),
		self::CSS_SELECTED_PAGE => array(),
	);
	
	/**
	* Where to put the pager...
	* 
	* @var integer
	*/
	protected $m_iPagerLocation = PS::PL_TOP_LEFT;
	public function getPagerLocation() { return $this->m_iPagerLocation; }
	
	/**
	* Header to display for grid
	* 
	* @var string
	*/
	protected $m_sGridHeader;
	public function getGridHeader() { return $this->m_sGridHeader; }
	public function setGridHeader( $sValue ) { $this->m_sGridHeader = $sValue; }
	
	/**
	* The class for our pager
	* 
	* @var string
	*/
	protected $m_sPagerClass = 'ps-pager';
	public function getPagerClass() { return $this->m_sPagerClass; }
	public function setPagerClass( $sValue ) { $this->m_sPagerClass = $sValue; }

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	/**
	* Retrieves the entire icon mapping array
	* 
	* @return array
	*/
	public function getIconMap() { return $this->m_arIcons; }

	/**
	* Overwrites the entire icon mapping array
	* 
	* @param array $arValue
	*/
	public function setIconMap( $arValue ) { $this->m_arIcons = $arValue; }		

	/**
	* Assign a single icon to a class
	* 
	* @param string $sClass
	* @param string $sIcon
	*/
	public function setIconMapping( $sClass, $sIcon ) { $this->m_arIcons[ $sClass ] = $sIcon; }
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/** 
	* Change label names so we can use them as indexes into our icon map
	* 
	*/
	public function init()
	{
		//	Set pager css class before we call dad
		$this->htmlOptions['class'] = $this->m_sPagerClass;
		
		//	Phone home
		parent::init();
		
		//	Override default labels...
		$this->nextPageLabel = 'Next';
		$this->prevPageLabel = 'Previous';
		$this->firstPageLabel = 'First';
		$this->lastPageLabel = 'Last';
		
		$this->maxButtonCount = 5;
		
		//	No CSS file, styling provided by jQuery UI and us
		$this->cssFile = false;
		
		//	Set the pager location
		$this->setPagerLocation( $this->m_iPagerLocation );
	}
	
	/**
	* Sets the pager location relative to the grid
	* 
	* @param int $eValue
	*/
	public function setPagerLocation( $eValue )
	{
		//	Our class for paging...
		$_sClass = PS::o( $this->htmlOptions, 'class', $this->m_sPagerClass, true );

		//	Clear out duplicate types...
		if ( $eValue != $this->m_iPagerLocation )
			$_sClass = PS::removeClass( $_sClass, '/^ps-pager-/' );
			
		$this->m_iPagerLocation = $eValue;
		
		switch ( $eValue )
		{
			case PS::PL_TOP_RIGHT:
				$_sClass = PS::addClass( $_sClass, array( 'ps-pager-right', 'ps-pager-top' )  );
				break;
				
			case PS::PL_BOTTOM_RIGHT:
				$_sClass = PS::addClass( $_sClass, array( 'ps-pager-right', 'ps-pager-bottom' )  );
				break;
				
			case PS::PL_TOP_LEFT:
				$_sClass = PS::addClass( $_sClass, array( 'ps-pager-left', 'ps-pager-top' )  );
				break;
				
			case PS::PL_BOTTOM_LEFT:
				$_sClass = PS::addClass( $_sClass, array( 'ps-pager-left', 'ps-pager-bottom' )  );
				break;
		}
		
		$this->htmlOptions['class'] = $_sClass;
	}
	
	/**
	* Executes the widget.
	* This overrides the parent implementation by displaying the generated page buttons.
	*/
	public function run( $bReturnString = false )
	{
		if ( $this->nextPageLabel === null ) $this->nextPageLabel = Yii::t( 'yii', 'Next &gt;' );
		if ( $this->prevPageLabel === null ) $this->prevPageLabel = Yii::t( 'yii', '&lt; Previous' );
		if ( $this->firstPageLabel === null ) $this->firstPageLabel = Yii::t( 'yii', '&lt;&lt; First' );
		if ( $this->lastPageLabel === null ) $this->lastPageLabel = Yii::t( 'yii', 'Last &gt;&gt;' );
		
		if ( $this->header === null ) $this->header = Yii::t( 'yii', 'Go to page: ' );

		$_sButtons = $this->createPageButtons();

		if ( empty( $_sButtons ) ) return;

		$this->registerClientScript();

		$_arHtmlOptions = $this->htmlOptions;
		$_arHtmlOptions['id'] = PS::o( $_arHtmlOptions, 'id', $this->getId() );
		$_arHtmlOptions['class'] = PS::o( $_arHtmlOptions, 'class', $this->m_sPagerClass );
		
		$_sOut = '<div class="ps-button-bar">';
			$_sOut .= $this->header;
			$_sOut .= PS::tag( 'ul', $_arHtmlOptions, implode( "\n", $_sButtons ) );
			$_sOut .= $this->footer;
		$_sOut .= '</div>';
		
		//	Return results?
		if ( $bReturnString ) return $_sOut;
		
		//	Otherwise just echo...
		echo $_sOut;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	 * Creates a page button with jQuery UI buttons.
	 * 
	 * @param string the text label for the button
	 * @param integer the page number
	 * @param string the CSS class for the page button. This could be 'page', 'first', 'last', 'next' or 'previous'.
	 * @param boolean whether this page button is visible
	 * @param boolean whether this page button is selected
	 * @return string the generated button
	 */
	protected function createPageButton( $sLabel, $iPage, $sClass, $bHidden, $bSelected )
	{
		$_sIcon = null;
		$_sLinkClass = "ps-button ui-corner-all";
		
		//	If we have an icon mapping for this button, add it to the link...
		if ( isset( $this->m_arIcons, $this->m_arIcons ) && count( $this->m_arIcons[ $sClass ] ) )
		{
			$_sIconName = $this->m_arIcons[ $sClass ][ 'icon' ];
			$_sIconPosition = 'ps-button-icon-' . $this->m_arIcons[ $sClass ][ 'position' ];
			$_sIcon = '<span class="ui-icon ui-icon-' . $_sIconName . '"></span>';
			$_sLinkClass .= ' '. $_sIconPosition;
		}
		
		//	Add marker class to list element so it can be styled...
		$sClass .= ' ps-link-pager';
		
		//	Hidden or selected? Class it up...
		if ( $bHidden || $bSelected ) $sClass .= ' ' . ( $bHidden ? self::CSS_HIDDEN_PAGE : self::CSS_SELECTED_PAGE );
		$_sLinkClass .= ( $bSelected ? ' ui-state-active' : ' ui-state-default' );
		
		return '<li class="' . $sClass . '">' . CHtml::link( $_sIcon . $sLabel, $this->createPageUrl( $iPage ), array( 'class' => $_sLinkClass ) ) . '</li>';
	}

}
