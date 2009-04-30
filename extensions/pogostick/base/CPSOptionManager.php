<?php
/**
 * CPSOptionCollection class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSOptionCollection provides a collection of "smart" option objects
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @filesource
 * @package psYiiExtensions
 * @subpackage Base
 * @since 1.0.4
 */
class CPSOptionCollection
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* The base option collection
	*
	* @var array
	*/
	protected static $m_arOptions = array();
	/**
	* The delimiter for multi-part keys, defaults to '.'
	*
	* @var string
	*/
	protected static $m_sDelimiter = '.';
	/**
	* Will add new options upon 'setValue' if the key does not exist. Defaults to 'true'
	*
	* @var bool
	*/
	protected static $m_bAddIfNotFound = true;

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	/**
	* Getters
	*
	*/
	public function getOption( $sKey ) { return $this->getSubOption( $sKey ); }
	public function getDelimiter() { return( $this->m_sDelimiter ); }
	public function getAddIfNotFound() { return( $this->m_bAddIfNotFound ); }

	/**
	* Setters
	*
	* @param mixed $sValue
	*/
	public function setOption( $sKey, $oValue )
	{
		$_oObject =& $this->getSubOption( $sKey );

		if ( null !== $_oObject )
			$_oObject = $oValue;
		else
			throw new CException( Yii::t( 'psOptionManager', 'Option Value not found for key "{key}"', array( '{key}' => $sKey ) ) );
	}

	public function setDelimiter( $sValue ) { $this->m_sDelimiter = $sValue; }
	public function setAddIfNotFound( $bValue ) { $this->m_bAddIfNotFound = $bValue; }

	//********************************************************************************
	//* Magic Methods
	//********************************************************************************

	/**
	* Getter
	*
	* @param string $sKey
	*/
	public function __get( $sKey )
	{
		//	Look in the array...
		$_oObject =& getSubOption( $sKey, $this->m_sDelimiter, false );

		//	If we found one, return it...
		if ( null != $_oObject )
			return $_oObject;

		//	Call our property accessor if there...
		$_sFuncName = 'get' . $sKey;
		if ( method_exists( $this, $_sFuncName ) )
			return $this->{$_sFuncName}( $sKey );

		//	No luck? Evil!
		throw new CException( Yii::t( 'psOptionManager', 'Option key "{key}" not found', array( '{key}' => $sKey ) ) );
	}

	/**
	* Setter
	*
	* @param string $sKey
	* @param mixed $oValue
	*/
	public function __set( $sKey, $oValue )
	{
		//	Call our property accessor if there...
		$_sFuncName = 'set' . $sKey;
		if ( method_exists( $this, $_sFuncName ) )
			return $this->{$_sFuncName}( $sKey );

		//	No luck? Evil!
		throw new CException( Yii::t( 'psOptionManager', 'Option key "{key}" not found', array( '{key}' => $sKey ) ) );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Constructs CPSOptionCollection
	*
	* @param string $sDelimiter
	* @param string $bAddIfNotFound
	* @return CPSOptionCollection
	*/
	public function __construct( $sDelimiter = '.', $bAddIfNotFound = true )
	{
		$this->m_sDelimiter = $sDelimiter;
		$this->m_bAddIfNotFound = $bAddIfNotFound;
	}

	/**
	* Add bulk options to the manager.
	*
	* @param array $arOptions An array containing key => value pairs to put in option array
	* @see addOption
	*/
	public function addOptions( array $arOptions )
	{
		foreach( $arOptions as $_sKey => $_oValue )
			$this->addOption( $_sKey, $_oValue );
	}

	/**
	* Adds a single option to the behavior
	*
	* @param string $sKey
	* @param mixed $oValue
	* @return array The last option processed
	* @see addOptions
	*/
	public function &addOption( $sKey, $oValue = null )
	{
		//	Is the key bogus?
		if ( null == $sKey || '' == trim( $sKey ) )
			throw new CException( Yii::t( 'psOptionManager', 'Invalid property name "{property}".', array( '{property}' => $sKey ) ) );

		//	Make sure to add if not found..
		$_oObject =& $this->getSubOption( $sKey, null, true );

		//	If it worked, return the object...
		if ( null !== $_oObject )
		{
			$_oObject = $oValue;
			return $_oObject;
		}

		//	Something's fishy...
		throw new CException( Yii::t( 'psOptionManager', 'Unable to add value for key "{key}"', array( '{key}' => $sKey ) ) );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Finds the option value using the global delimiter {@link CPSBaseOption::$m_sDelimiter}.
	*
	* Example:
	*
	*	$this->getSubOption( 'baseUrl' );
	*	$this->getSubOption( 'us.ga.atlanta.businesses' );
	*
	* @param string $sKey
	* @param string $sDelimiter
	* @param bool $bAddIfNotFound
	* @return mixed If {@link CPSBaseOption::$m_bAddIfNotFound} is set to false, null will be returned.
	* Otherwise a new array() will be created at that spot in the array and returend.
	*/
	protected function &getSubOption( $sKey, $sDelimiter = null, $bAddIfNotFound = null )
	{
		//	Check for overrides
		$_sDelimiter = ( isset( $sDelimiter ) ) ? $sDelimiter : $this->m_sDelimiter;
		$_bAddIfNotFound = ( isset( $bAddIfNotFound ) ) ? $_bAddIfNotFound : $this->m_nAddIfNotFound;

		//	Start at the top...
		$_oObject =& $this->m_arOptions;

		foreach( explode( $_sDelimiter, $_oObject ) as $_sKey => $_oValue )
		{
			if ( ! array_key_exists( $_sKey, $_oObject ) )
			{
				if ( $_bAddIfNotFound )
				{
					//	Add a new array and return it...
					$_oObject[ $_sKey ] = array();
					continue;
				}

				//	Not there... bail
				return null;
			}
			else //	Lather, rinse, repeat
				$_oObject =& $_oObject[ $_sKey ];
		}

		//	Return the object
		return $_oObject;
	}

}