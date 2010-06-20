<?php
/*
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

//	Include Files
//	Constants
//	Global Settings

/**
 * A quicky down and dirty file object
 *
 * @package 	psYiiExtensions
 * @subpackage	components
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.0
 *
 * @filesource
 *
 * @property-read fileHandle The handle of the current file
 * @property-read fileName The name of the current file
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
	protected $m_sFileName = false;
	public function getFileName() { return $this->m_sFileName; }

	/**
	 * The handle of the current file
	 * @var integer
	 */
	protected $m_iFileHandle = false;
	public function getFileHandle() { return $this->m_iFileHandle; }
	public function validHandle() { return ( false !== $this->m_iFileHandle ); }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	public function __construct( $sName )
	{
		$this->m_sFileName = $sName;
		$this->open();
	}

	public function open()
	{
		if ( file_exists( $this->m_sFileName ) )
		{
			if ( false !== ( $this->m_iFileHandle = @fopen( $this->m_sFileName, 'a+' ) ) )
				$this->m_iFileHandle = @fopen( $this->m_sFileName, 'r' );
		}

		return $this->validHandle();
	}

	public function close()
	{
		@fclose( $this->m_iFileHandle );
		$this->m_iFileSize = $this->m_iFileHandle = false;
	}

	public function filesize()
	{
		return $this->validHandle() && filesize( $this->m_sFileName );
	}

	public function atime()
	{
		return $this->validHandle() && fileatime( $this->m_sFileName );
	}

	public function fileowner()
	{
		return $this->validHandle() && fileowner( $this->m_sFileName );
	}

	public function filegroup()
	{
		return $this->validHandle() && filegroup( $this->m_sFileName );
	}

	public function fseek( $iOffset = 0 )
	{
		return $this->validHandle() && fseek( $this->m_iFileHandle, $iOffset );
	}

	public function ftell()
	{
		return $this->validHandle() && ftell( $this->m_iFileHandle );
	}

	/**
	 * Retrieves a string from the current file
	 */
	public function fgets( $iOffset = 0 )
	{
		if ( false !== $this->ftell() )
			rewind( $this->m_iFileHandle );

		return fgets( $this->m_iFileHandle );
	}

}