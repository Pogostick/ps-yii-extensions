<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * The CPSjqRatingWidget allows the {@link http://www.fyneworks.com/jquery/star-rating/ JQ Rating} to be used in Yii.
 * 
 * @package 	psYiiExtensions.widgets
 * @subpackage 	jqRating
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSjqRatingWidget.php 358 2010-01-02 23:33:40Z jerryablan@gmail.com $
 * @since 		v1.0.0
 *  
 * @filesource
 */
class CPSjqRatingWidget extends CPSWidget
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Initialize
	*/
	public function preinit()
	{
		//	Phone home. Call first to get base behaviors loaded...
		parent::preinit();

		//	Add these options in the constructor so the Yii base can pre-fill them from the config files.
		$this->addOptions(
			array(
				'ajaxCallback' => 'string',
				'cancel' => 'string',
				'cancelValue' => 'string',
				'half' => 'bool:false',
				'hoverTips' => 'array:array()',
				'readOnly' => 'bool:false',
				'required' => 'bool:false',
				'selectValue' => 'double:0',
				'split' => 'int:1',
				'starClass' => 'string:star',
				'starCount' => 'int:5',
				'starTitles' => 'array:array()',
				'starValues' => 'array:array()',
				'supressScripts' => 'bool:false',
			)
		);

		//	These are the valid callbacks for this class
		$this->validCallbacks = array(
			'callback',
			'focus',
			'blur',
		);

		//	Set our view name...
		$this->viewName = __CLASS__ . 'View';
	}

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Validate baseUrl
		if ( $this->isEmpty( $this->baseUrl ) )
			$this->baseUrl = $this->extLibUrl . '/jqRating';

		//	Register the scripts/css
		$this->registerClientScripts();

		$this->html = $this->render( 
			$this->viewName,
			array( "options" => $this->makeOptions() ),
			$this->returnString
		);

		return $this->html;
	}

	/**
	* Registers the needed CSS and JavaScript.
	*
	* @param string $sId
	*/
	public function registerClientScripts()
	{
		//	Daddy...
		parent::registerClientScripts();

		//	Register scripts necessary
		$this->pushScriptFile( "{$this->baseUrl}/jquery.MetaData.js" );
		$this->pushScriptFile( "{$this->baseUrl}/jquery.rating.js" );

		//	Get the javascript for this widget
		if ( ! $this->supressScripts && ! $this->returnString )
			$this->registerWidgetScript();

		//	Register css files...
		PS::_rcf( "{$this->baseUrl}/jquery.rating.css", 'screen' );
	}

	//********************************************************************************
	//* Private methods
	//********************************************************************************

	/**
	* Generates the javascript code for the widget
	* @return string
	*/
	protected function generateJavascript()
	{
		//	No callback set? then make the ajax callback
		if ( ! isset( $this->callbacks[ 'callback' ] ) && ! empty( $this->ajaxCallback ) )
		{
			$_arTemp = array(
				'type' => 'GET',
				'url' => PS::_cu( $this->ajaxCallback ),
				'dataType' => 'html'
			);

			$_sCBBody = 'function(value,link){var arTemp = ' . CJavaScript::encode( $_arTemp ) . '; arTemp[\'data\'] = \'value=\'+value+\'&link=\'+link; jQuery.ajax(arTemp);}';

			$this->callbacks[ 'callback' ] = $_sCBBody;
		}

		$_arOptions = $this->makeOptions();

		//	Now rating apply...
		$this->script .= 'jQuery(\'.' . $this->starClass . '\').rating(' . $_arOptions . '); ';

		return $this->script;
	}

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateHtml()
	{
		$_iMaxCount = $this->starCount;

		//	Handle multiple star outputs...
		if ( $this->half )
			$this->split = 2;

		if ( $this->split > 1 )
			$_iMaxCount *= $this->split;

		for ( $_i = 0; $_i < $_iMaxCount; $_i++ )
		{
			$_sHtml .= '<input type="radio" class="' . $this->starClass;

			if ( $this->half )
				$_sHtml .= ' {half:true}';
			else if ( $this->split > 1 )
				$_sHtml .= ' {split:' . $this->split . '}';

			$_sHtml .= '" name="' . $this->name . '" ';

			if ( is_array( $this->starTitles ) && sizeof( $this->starTitles ) > 0 )
				$_sHtml .= 'title="' . $this->starTitles[ $_i ] . '" ';

			if ( is_array( $this->starValues ) && sizeof( $this->starValues ) > 0 )
				$_sHtml .= 'value="' . $this->starValues[ $_i ] . '" ';
			else
				$_sHtml .= 'value="' . ( $_i + 1 ) . '" ';

			if ( $this->selectValue != 0 && ( $this->selectValue * $this->split ) == ( $_i + 1 ) )
				$_sHtml .= 'checked="checked" ';

			$_sHtml .= ' />';
		}

		return( $_sHtml );
	}

 	/**
	* Convenience function to create a star rating widget
	*
	* Available options:
	*
	* suppressScripts	boolean		If true, scripts will be stored in the member variable 'scripts' and not output
	* returnString		boolean		If true, the output of this widget will be stored in a string and not echo'd. It is available through the member variable 'html'
	* baseUrl			string		The location of the jqRating installation
	* id				string		The HTML id of the widget. Defaults to null
	* name				string		The HTML name of the widget. Defaults to rating{x}, x is incremented with each use.
	* starClass			string		The HTML class name of the widget's output
	* split				integer		The number of times to split each star. Allows for 1/2 and 1/4 ratings, etc. Default 0
	* starCount			integer		The number of stars to display. Default 5
	* selectValue		integer		The value to mark as 'preselected' when displaying
	* readOnly			boolean		Makes the widget read-only, no input allowed.
	* required			boolean		Disables the 'cancel' button so user can only select one of the specified values
	* cancel			string		The tooltip text for the cancel button, defaults to 'Cancel Rating'
	* cancelValue		string		The value assigned to the widget when the cancel button is selected
	* ajaxCallback		function	The URL to call when a star is clicked. This URL is called via AJAX. Will be overriden by a value in 'callback' below...
	*
	* Available Callbacks
	*
	* callback			function	The Javascript function executed when a star is clicked
	* blur				function	The Javascript function executed when stars are blurred
	* focus				function	The Javascript function executed when stars are focused
	*
	* @param array $arOptions
	* @return CPSjqRatingWidget
	*/
	public static function createRating( $arOptions )
	{
		static $_iIdCount = 0;

		//	Fix up the base url...
		$_sBaseUrl = PS::o( $arOptions, 'baseUrl', '' );

		if ( empty( $_sBaseUrl ) )
			$_sBaseUrl = Yii::getPathOfAlias( 'pogostick' ) . '/jqRating';

		//	Put it back in the array
		PS::so( $arOptions, 'baseUrl', $_sBaseUrl );

		$sId = PS::o( $arOptions, 'id' );
		$sName = PS::o( $arOptions, 'name' );

		//	Build the options...
		$_arOptions = array(
			'supressScripts' => PS::o( $arOptions, 'supressScripts', false ),
			'returnString' => PS::o( $arOptions, 'returnString', false ),
			'readOnly' => PS::o( $arOptions, 'readOnly', false ),
			'required' => PS::o( $arOptions, 'required', false ),
			'baseUrl' => PS::o( $arOptions, 'baseUrl', $_sBaseUrl ),
			'name' => ( $sName == null ? 'rating' . $_iIdCount : $sName . $_iIdCount ),
			'starClass' => PS::o( $arOptions, 'starClass', 'star' ),
			'split' => PS::o( $arOptions, 'split', 1 ),
			'starCount' => PS::o( $arOptions, 'starCount', 5 ),
			'selectValue' => ( double )PS::o( $arOptions, 'selectValue', 0 ),
			'ajaxCallback' => PS::o( $arOptions, 'ajaxCallback' ),
			'starTitles' => PS::o( $arOptions, 'starTitles' ),
			'starValues' => PS::o( $arOptions, 'starValues' ),
			'hoverTips' => PS::o( $arOptions, 'hoverTips' ),
			'callbacks' =>
				array(
					'callback' => PS::o( $arOptions, 'callback', null ),
					'focus' => PS::o( $arOptions, 'focus', null ),
					'blur' => PS::o( $arOptions, 'blur', null ),
			),
		);

		$_oWidget = Yii::app()->controller->widget(
			'pogostick.widgets.jqRating.CPSjqRatingWidget',
			$_arOptions
		);

		//	Return my created widget
		return $_oWidget;
 	}

}
