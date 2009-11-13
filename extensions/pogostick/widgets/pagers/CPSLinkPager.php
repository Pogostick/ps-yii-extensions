<?php
/**
 * CPSLinkPager class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage widgets.pagers
 * @since v1.0.5
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
/**
 * Basic link pager with jQuery UI icons
 */
class CPSLinkPager extends CLinkPager
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
		self::CSS_FIRST_PAGE => array( 'icon' => 'arrowstop-1-e', 'position' => 'left' ),
		self::CSS_LAST_PAGE => array( 'icon' => 'arrowstop-1-w', 'position' => 'right' ),
		self::CSS_PREVIOUS_PAGE => array( 'icon' => 'arrow-1-w', 'position' => 'left' ),
		self::CSS_NEXT_PAGE => array( 'icon' => 'arrow-1-e', 'position' => 'right' ),
		self::CSS_INTERNAL_PAGE => array(),
		self::CSS_HIDDEN_PAGE => array(),
		self::CSS_SELECTED_PAGE => array(),
	);

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	/**
	* Retrieves the entire icon mapping array
	* 
	* @returns array
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
		//	Phone home
		parent::init();
		
		//	Override default labels...
		$this->nextPageLabel = 'Next';
		$this->prevPageLabel = 'Previous';
		$this->firstPageLabel = 'First';
		$this->lastPageLabel = 'Last';
		
		//	No CSS file, styling provided by jQuery UI and us
		$this->cssFile = false;
		
		//	Our class for paging...
		$this->htmlOptions['class'] = 'psPager';
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