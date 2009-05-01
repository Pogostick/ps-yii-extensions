<?php
/**
 * CPSOptionManager class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSOptionManager provides a collection of "smart" option objects
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @filesource
 * @package psYiiExtensions
 * @subpackage Base
 * @since 1.0.4
 */
class CPSOptionManager
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* The base option collection
	*
	* @var array
	*/
		private static $m_arOptions = array();
	/**
	* The name of the top level options array element
	*
	* @var string
	*/
	protected $m_sOptionName = 'options';
	/**
	* The delimiter for multi-part keys, defaults to '.'
	*
	* @var string
	*/
	protected $m_sDelimiter = '.';
	/**
	* Will add new options upon 'setValue' if the key does not exist. Defaults to 'true'
	*
	* @var bool
	*/
	protected $m_bAddIfNotFound = true;
	/**
	* If true, will not overwrite an existing key if (@link $m_bAddIfNotFound) is 'true'
	*
	* @var bool
	*/
	protected $m_bOverwrite = true;

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	public function getDelimiter() { return $this->m_sDelimiter; }
	public function getAddIfNotFound() { return $this->m_bAddIfNotFound; }
	public function getOverwrite() { return $this->m_bOverwrite; }
	protected function getOptionsArray( $sKey = null ) { return ( null == $sKey ) ? $this->m_arOptions : $this->m_arOptions[ $sKey ]; }

	public function setDelimiter( $sValue ) { $this->m_sDelimiter = $sValue; }
	public function setAddIfNotFound( $bValue ) { $this->m_bAddIfNotFound = $bValue; }
	public function setOverwrite( $bValue ) { $this->m_bOverwrite = $bValue; }
	protected function setOptionsArray( $oValue ) { $this->m_arOptions[ $oValue ]; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Constructs CPSOptionManager
	*
	* @param string $sDelimiter
	* @param bool $bAddIfNotFound
	* @param bool $bOverwrite
	* @return CPSOptionManager
	*/
	public function __construct( $sDelimiter = '.', $bAddIfNotFound = true, $bOverwrite = false, $sOptionName = 'options' )
	{
		$this->m_sDelimiter = $sDelimiter;
		$this->m_bAddIfNotFound = $bAddIfNotFound;
		$this->m_bOverwrite = $bOverwrite;
		$this->m_sOptionName = $sOptionName;
		$this->setOptionsArray( array( $sOptionName => array() ) );
	}

	public function getOption( $sKey ) //{ return $this->getSubOption( $sKey, null, false, false ); }
	{
		return $this->m_arOptions[ $sKey ];

    	throw new CException( Yii::t( 'psOptionManager::getOption', 'Option key "{key}" does not exist.', array( '{key}' => $sKey ) ) );
	}

	/**
	* Add bulk options to the manager.
	*
	* @param array $arOptions An array containing key => value pairs to put in option array
	* @see setOption
	*/
	public function addOptions( array $arOptions )
	{
		foreach( $arOptions as $_sKey => $_oValue )
			$this->setOption( $_sKey, $_oValue );
	}

	/**
	* Adds a single option to the array
	*
	* @param string $sKey
	* @param mixed $oValue
	* @return null|buol Returns false if the assignment cannot be made, otherwise it returns 'true'
	* @see addOptions
	*/
	public function setOption( $sKey, $oValue )
	{
		if ( ! ( isset( $this->m_arOptions[ $sKey ] ) && ( $this->m_bAddIfNotFound || $this->m_bOverwrite ) ) )
			return null;

		$this->m_arOptions[ $sKey ] = $oValue;
	}

	//********************************************************************************
	//* Magic Methods
	//********************************************************************************

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

}