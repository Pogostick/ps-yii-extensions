<?php
/**
* CPSjQueryWidget class file.
*
* @author Jerry Ablan <jablan@pogostick.com>
* @link http://ps-yii-extensions.googlecode.com
* @copyright Copyright &copy; 2009 Pogostick, LLC
* @license http://www.gnu.org/licenses/gpl.html
*/

/**
* The CPSjQueryWidget is a wrapper for any jQuery widget
* 
* @author Jerry Ablan <jablan@pogostick.com>
* @version $Id$
* @filesource
* @package psYiiExtensions
* @subpackage Widgets
* @since 1.0.0
*/
class CPSjQueryWidget extends CPSWidget
{
	//********************************************************************************
	//* Member variables
	//********************************************************************************

	/**
	* Any additional widget scripts
	* 
	* @var array
	*/
	protected $m_arScripts = array();

	//********************************************************************************
	//* Methods
	//********************************************************************************

	/**
	* Constructs a CPSjqUIWraqpper
	*
	* @param mixed $oOwner
	* @return CPSjqUIWraqpper
	*/
	function __construct( $oOwner = null )
	{
		parent::__construct( $oOwner );
		
		//	Add the default options for jqUI stuff
		$this->addOptions( 
			array(
				//	The name of the widget you'd like to create (i.e. draggable, accordion, etc.)
				'autoRun_' => array( CPSOptionManager::META_REQUIRED => true, CPSOptionManager::META_DEFAULTVALUE => true, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				//	The name of the widget you'd like to create (i.e. draggable, accordion, etc.)
				'widgetName_' => array( CPSOptionManager::META_REQUIRED => true, CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
			)
		);
	}
	
	/**
	* Adds a user script to the output array
	* 
	* @param array $arScript
	*/
	public function addScripts( $arScripts = array() )
	{
		foreach ( $arScripts as $_sScript )
			$this->m_arScripts[] = $_sScript;
	}

	/**
	* Initialize the widget
	* 	
	*/
	public function init()
	{
		//	Daddy
		parent::init();
		
		//	Validate baseUrl
		if ( $this->isEmpty( $this->baseUrl ) )
			$this->baseUrl = $this->extLibUrl;
	}

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Register the scripts/css
		$this->registerClientScripts();

		//	Generate the HTML if available
		if ( method_exists( $this, 'generateHtml' ) )
			echo $this->generateHtml();
	}

	/**
	* Registers the needed CSS and JavaScript.
	*
	* @param string $sId
	* @returns CClientScript The current app's ClientScript object
	*/
	public function registerClientScripts()
	{
		static $_iScriptCount = 0;
		
		//	Daddy...
		$_oCS = parent::registerClientScripts();

		//	Register scripts necessary
		$_oCS->registerCoreScript( 'jquery' );
	
		//	Additional scripts		
		foreach ( $this->m_arScripts as $_sScript )
			$_oCS->registerScript( 'psjqw.script' . $_iScriptCount++ . '#' . $this->id, $_sScript, CClientScript::POS_READY );

		//	Don't forget subclasses
		return $_oCS;
	}

	//********************************************************************************
	//* Private methods
	//********************************************************************************

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateJavascript( $sClassName = null, $arOptions = null )
	{
		$_arOptions = ( null != $arOptions ) ? $arOptions : $this->makeOptions();
		$_sId = ( null != $sClassName ) ? '.' . $sClassName : '#' . $this->id;
		
		$this->script =<<<CODE
$('{$_sId}').{$this->widgetName}({$_arOptions});
CODE;

		return( $this->script );
	}

	//********************************************************************************
	//* Static methods
	//********************************************************************************
	
	/**
	* Constructs and returns a jQuery widget
	* 
	* The options passed in are dynamically added to the options array and will be accessible 
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	* 
	* @param string $sName The type of jq widget to create
	* @param array $arOptions The options for the widget
	* @param string $sId The DOM id of the widget if other than $sName
	* @param string $sClass The class of the calling object if different
	* @return CPSjQueryWidget
	*/
	public static function create( $sName, array $arOptions = array(), $sClass = __CLASS__ )
	{
		//	Instantiate...
		$_oWidget = new $sClass();

		//	Set default options...
		$_oWidget->id = isset( $arOptions[ 'id' ] ) ? $arOptions[ 'id' ] : $sName;
		$_oWidget->name = isset( $arOptions[ 'name' ] ) ? $arOptions[ 'name' ] : $sName;
		if ( empty( $_oWidget->name ) ) $_oWidget->name = $_oWidget->id;
		$_oWidget->widgetName = $sName;
		
		return $_oWidget->finalizeCreate( $arOptions );
	}

	/**
	* Finalize the creation of a widget
	* 
	* This allows subclasses to initialize their class then finalize the creation here.
	* 	
	* @param CPSjQueryWidget $oWidget The widget to finalize
	* @param array $arOptions Options for this widget
	* @returns CPSjQueryWidget
	*/
	protected function finalizeCreate( $arOptions = array() )
	{
		//	Set variable options...
		if ( is_array( $arOptions ) )
		{
			//	Check for scripts...
			if ( isset( $arOptions[ '_scripts' ] ) && is_array( $arOptions[ '_scripts' ] ) )
			{
				//	Add them...
				$this->addScripts( $arOptions[ '_scripts' ] );
					
				//	Kill _scripts option...
				unset( $arOptions[ '_scripts' ] );
				
			}

			//	Now process the rest of the options...			
			foreach( $arOptions as $_sKey => $_oValue )
			{
				//	Add option and set it...
				$this->addOption( $_sKey, null, false );
				$this->setOption( $_sKey, $_oValue );
			}
		}
		
		//	Initialize the widget
		$this->init();

		//	Does user want us to run it?
		if ( $this->autoRun ) $this->run();

		//	And return...
		return $this;
	}

}