<?php
/**
 * CPSFile.php
 * 
 * Copyright (c) 2010 Jerry Ablan <jablan@pogostick.com>.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * 
 * This file is part of the Pogostick Yii Extension Library.
 * 
 * We share the same open source ideals as does the jQuery team, and
 * we love them so much we like to quote their license statement:
 * 
 * You may use our open source libraries under the terms of either the MIT
 * License or the Gnu General Public License (GPL) Version 2.
 * 
 * The MIT License is recommended for most projects. It is simple and easy to
 * understand, and it places almost no restrictions on what you can do with
 * our code.
 * 
 * If the GPL suits your project better, you are also free to use our code
 * under that license.
 * 
 * You don’t have to do anything special to choose one license or the other,
 * and you don’t have to notify anyone which license you are using.
 */

//*************************************************************************
//* File Constants
//*************************************************************************

/**#@+
 * Extra GLOB constant for safe_glob()
 */
define( 'GLOB_NODIR',	0x0100 );
define( 'GLOB_PATH',	0x0200 );
define( 'GLOB_NODOTS',	0x0400 );
define( 'GLOB_RECURSE',	0x0800 );
/**#@-*/

/**
 * A quicky down and dirty file object with a sprinkle of awesomeness
 *
 * @package 	psYiiExtensions
 * @subpackage	components
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id: CPSFile.php 389 2010-06-20 14:18:58Z jerryablan@gmail.com $
 * @since 		v1.0.0
 *
 * @filesource
 *
 * @property-read $fileHandle The handle of the current file
 * @property-read $fileName The name of the current file
 */
class CPSFile extends CPSComponent
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * The name of the current file
	 * @var string
	 */
	protected $_fileName = false;
	/**
	 * The handle of the current file
	 * @var integer
	 */
	protected $_fileHandle = false;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructo
	 */
	public function __construct( $fileName )
	{
		$this->_fileName = $fileName;
		$this->open();
	}

	/**
	 * @return bool
	 */
	public function validHandle()
	{
		return ( false !== $this->_fileHandle );
	}

	/**
	 * @return bool
	 */
	public function open()
	{
		if ( file_exists( $this->_fileName ) )
		{
			if ( false !== ( $this->_fileHandle = @fopen( $this->_fileName, 'a+' ) ) )
				$this->_fileHandle = @fopen( $this->_fileName, 'r' );
		}

		return $this->validHandle();
	}

	/**
	 */
	public function close()
	{
		@fclose( $this->_fileHandle );
		$this->_fileHandle = false;
	}

	/**
	 * @return bool
	 */
	public function filesize()
	{
		return $this->validHandle() && filesize( $this->_fileName );
	}

	/**
	 * @return bool
	 */
	public function atime()
	{
		return $this->validHandle() && fileatime( $this->_fileName );
	}

	/**
	 * @return bool
	 */
	public function fileowner()
	{
		return $this->validHandle() && fileowner( $this->_fileName );
	}

	/**
	 * @return bool
	 */
	public function filegroup()
	{
		return $this->validHandle() && filegroup( $this->_fileName );
	}

	/**
	 * @param int $iOffset
	 * @return bool
	 */
	public function fseek( $iOffset = 0 )
	{
		return $this->validHandle() && fseek( $this->_fileHandle, $iOffset );
	}

	/**
	 * @return bool
	 */
	public function ftell()
	{
		return $this->validHandle() && ftell( $this->_fileHandle );
	}

	/**
	 * Retrieves a string from the current file
	 */
	public function fgets( $iOffset = 0 )
	{
		if ( false !== $this->ftell() )
			rewind( $this->_fileHandle );

		return fgets( $this->_fileHandle );
	}

	/**
	 * As found on php.net posted by: BigueNique at yahoo dot ca 20-Apr-2010 07:15
	 * A safe empowered glob().
	 *
	 * Supported flags: GLOB_MARK, GLOB_NOSORT, GLOB_ONLYDIR
	 * Additional flags: GLOB_NODIR, GLOB_PATH, GLOB_NODOTS, GLOB_RECURSE (not original glob() flags, defined here)
	 * @author BigueNique AT yahoo DOT ca
	 * @param string $pattern
	 * @param int flags
	 * @return array|false
	 */
	public static function glob( $pattern, $flags = 0 )
	{
		$_split = explode( '/', str_replace( '\\', '/', $pattern ) );
		$_mask = array_pop( $_split );
		$_path = implode( '/', $_split );
		$_glob = false;

		if ( false !== ( $_directory = opendir( $_path ) ) )
		{
			$_glob = array();

			while ( false !== ( $_file = readdir( $_directory ) ) )
			{
				//	Recurse directories
				if ( ( $flags & GLOB_RECURSE ) && is_dir( $_file ) && ( ! in_array( $_file, array( '.', '..' ) ) ) )
				{
					$_glob = array_merge(
						$_glob,
						self::array_prepend(
							self::glob(
								$_path . '/' . $_file . '/' . $_mask,
								$flags
							),
							( $flags & GLOB_PATH ? '' : $_file . '/' )
						)
					);
				}

				// Match file mask
				if ( fnmatch( $_mask, $_file ) )
				{
					if ( ( ( !( $flags & GLOB_ONLYDIR ) ) || is_dir( "$_path/$_file" ) )
						 && ( ( !( $flags & GLOB_NODIR ) ) || ( ! is_dir( $_path . '/' . $_file ) ) )
						 && ( ( !( $flags & GLOB_NODOTS ) ) || ( ! in_array( $_file, array( '.', '..' ) ) ) )
					)
					{
						$_glob[] = ( $flags & GLOB_PATH ? $_path . '/' : '' ) . $_file . ( $flags & GLOB_MARK ? '/' : '' );
					}
				}
			}

			closedir( $_directory );

			if ( !( $flags & GLOB_NOSORT ) )
			{
				sort( $_glob );
			}
		}

		return $_glob;
	}

	/**
	 * @static
	 * @param array $array
	 * @param string $string
	 * @param bool $deep
	 * @return array
	 */
	public static function array_prepend( $array, $string, $deep = false )
	{
		if ( empty( $array ) || empty( $string ) )
			return $array;

		foreach ( $array as $key => $element )
		{
			if ( is_array( $element ) )
			{
				if ( $deep )
					$array[$key] = array_prepend( $element, $string, $deep );
				else
					trigger_error( 'array_prepend: array element', E_USER_WARNING );
			}
			else
				$array[$key] = $string . $element;
		}

		return $array;
	}

	//*************************************************************************
	//* Properties
	//*************************************************************************

	/**
	 * @return string
	 */
	public function getFileName( )
	{
		return $this->_fileName;
	}

	/**
	 * @return int
	 */
	public function getFileHandle( )
	{
		return $this->_fileHandle;
	}
}