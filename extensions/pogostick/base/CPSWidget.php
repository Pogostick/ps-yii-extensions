<?php
<?php
/**
 * CPSWidget class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.gnu.org/licenses/gpl.html
 */

/**
 * The CPSWidget is the base class for all Pogostick widgets for Yii
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Base
 * @filesource
 * @since 1.0.3
 */
class CPSWidget extends CInputWidget
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Attach behaviors during construction...
	*
	* @param CBaseController $oOwner
	*/
	public function __construct( $oOwner = null )
	{
		//	Log it and check for issues...
		$this->psLog( '{class} constructor called' );

		//	Call daddy...
		parent::__construct( $oOwner );

		//	Attach our widget behaviors
		$this->attachBehaviors(
        	array(
        		'psWidget' => 'pogostick.behaviors.CPSWidgetBehavior',
        	)
        );
	}

	/**
	* Yii widget init
	*
	*/
	public function init()
	{
		//	Call daddy
		parent::init();

		//	Get the id/name of this widget
		list( $this->name, $this->id ) = $this->resolveNameID();
	}

	/***
	* Handles registration of scripts & css files...
	* @returns CClientScript Returns the current applications CClientScript object {@link CWebApplication::getClientScript}
	*/
	public function registerClientScripts()
	{
		//	Get the clientScript
		$_oCS = Yii::app()->getClientScript();

		//	Register a special CSS file if we have one...
		if ( ! empty( $this->cssFile ) )
			$_oCS->registerCssFile( Yii::app()->baseUrl . "{$this->cssFile}", 'screen' );

		//	Send upstream for convenience
		return( $_oCS );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Outputs to the log and optionally throws an exception
	*
	* @param string $sMessage The message to log. Supports the '{class}' parameter substitution.
	* @param string $sLevel The level of the logged message. Defaults to 'info'
	* @param string $sExceptionMessage If supplied, will throw an exception
	* @param string $sExceptionMessage If supplied, will throw an exception
	* @param string $sCategory The category of the logged message. Defaults to 'application'
	* @param string $sSource If supplied, will pass to Yii:t() method
	* @param string $sLanguage If supplied, will pass to Yii:t() method
	*/
	protected function psLog( $sMessage, $sLevel = 'info', $sExceptionMessage = null, $sCategory = 'application', $sSource = null, $sLanguage = null )
	{
		//	Log the message
		Yii::log( Yii::t( $sCategory, $sMessage, array( '{class}' => __CLASS__ ), $sSource, $sLanguage ), $sLevel, $sCategory );

		//	Make sure we have the proper support
		if ( null != $sExceptionMessage )
			throw new CException( Yii::t( $sCategory, Yii::t( $sCategory, $sMessage, array( '{class}' => __CLASS__ ) ), $sLevel, $sCategory ), $sLevel, $sCategory );
	}

}