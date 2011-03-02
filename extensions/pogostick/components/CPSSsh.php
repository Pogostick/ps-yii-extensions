<?php
/**
 * CPSSsh.php
 * 
 * Copyright (c) 2010 Jerry Ablan <jablan@pogostick.com>.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * 
 * This file is part of Pogostick : Yii Extensions.
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
 * CPSSsh
 *
 * @package		psYiiExtensions
 * @subpackage 	components
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id: CPSSsh.php 399 2010-08-09 03:03:15Z jerryablan@gmail.com $
 * @since 		v1.0.0
 *
 * @filesource
 */
class CPSSsh extends CPSComponent
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var string The remote host name
	 */
	protected $_hostName;
	public function getHostName() { return $this->_hostName; }
	public function setHostName( $value ) { $this->_hostName = $value; }

	/**
	 * @var string The remote host port
	 */
	protected $_hostPort;
	public function getHostPort() { return $this->_hostPort; }
	public function setHostPort( $value ) { $this->_hostPort = $value; }

	/**
	 * @var string The remote host user
	 */
	protected $_userName;
	public function getUserName() { return $this->_userName; }
	public function setUserName( $value ) { $this->_userName = $value; }

	/**
	 * @var string The remote host password
	 */
	protected $_password;
	public function getPassword() { return $this->_password; }
	public function setPassword( $value ) { $this->_password = $value; }

	/**
	 * @var string The remote host public key file
	 */
	protected $_publicKey;
	public function getPublicKey() { return $this->_publicKey; }
	public function setPublicKey( $value ) { $this->_publicKey = $value; }

	/**
	 * @var string The current connection
	 */
	protected $_sessionId;

	/**
	 * @var string The current sftp connection
	 */
	protected $_sftpId;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Connects to a host via ssh.
	 * 
	 * @param string $userName
	 * @param string $password
	 * @param string $hostName
	 * @param integer $hostPort
	 * @return boolean
	 */
	public function connect( $hostName = null, $userName = null, $password = null, $hostPort = null )
	{
		if ( ! function_exists( 'ssh2_connect' ) )
			throw new CException( 500, 'You must install the ssh2 extensions on your server to use this class.' );

		if ( $this->_sessionId )
			$this->disconnect();

		if ( false !== ( $this->_sessionId = ssh2_connect( PS::nvl( $hostName, $this->_hostName, 'localhost' ), PS::nvl( $hostPort, $this->_hostPort, 22 ) ) ) )
		{
			if ( ssh2_auth_password( $this->_sessionId, PS::nvl( $userName, $this->_userName ), PS::nvl( $password, $this->_password ) ) )
			{
				$this->_sftpId = ssh2_sftp( $this->_sessionId );
				return true;
			}
		}

		return false;
	}

	/**
	 * Resets the current session
	 */
	public function disconnect()
	{
		//	There is apparently no "disconnect" or "close" ssh method.
		$this->_sessionId = null;
		$this->_sftpId = null;
	}

	/**
	 * Opens a remote file
	 * 
	 * @param string $path
	 * @param string $mode
	 * @return mixed
	 */
	public function fopen( $path, $mode = 'r' )
	{
		return fopen( "ssh2.sftp://{$this->_sftpId}{$path}", $mode );
	}

	/**
	 * Stats a symbolic link on the remote filesystem without  following the link.
	 *
	 * @param string $path
	 * @return array
	 */
	public function lstat( $path )
	{
		return ssh2_sftp_lstat( $this->_sftpId, $path );
	}

	/**
	 *
	 * @param <type> $directoryName
	 * @param <type> $mode
	 * @param <type> $recursive
	 * @return string
	 */
	public function mkdir( $directoryName, $mode = 0777, $recursive = false )
	{
		return ssh2_sftp_mkdir( $this->_sftpId, $directoryName, $mode, $recursive );
	}

	/**
	 * Returns the target of a symbolic link
	 * 
	 * @param string $link Path of the symbolic link
	 * @return string
	 */
	public function readlink( $link )
	{
		return ssh2_sftp_readlink( $this->_sftpId, $link );
	}


	/**
	 * Translates filename into the effective real path on the remote filesystem
	 *
	 * @param string $path Path of the file
	 * @return string
	 */
	public function realpath( $path )
	{
		return ssh2_sftp_realpath( $this->_sftpId, $path );
	}

	/**
	 * Renames a file on the remote filesystem
	 * 
	 * @param string $path
	 * @param string $fromName
	 * @param string $toName
	 * @return boolean
	 */
	public function rename( $path, $fromName, $toName )
	{
		return ssh2_sftp_rename( $this->_sftpId, $fromName, $toName );
	}

	/**
	 * Remove a directory
	 *
	 * @param string $path
	 * @return boolean
	 */
	public function rmdir( $path )
	{
		return ssh2_sftp_rmdir( $this->_sftpId, $path );
	}

	/**
	 * Stats a file on the remote filesystem following any symbolic links
	 *
	 * @param string $path
	 * @return array
	 */
	public function stat( $path )
	{
		return ssh2_sftp_stat( $this->_sftpId, $path );
	}

	/**
	 * Create a symlink
	 *
	 * @param string $targetName
	 * @param string $link
	 * @return boolean
	 */
	public function symlink( $targetName, $link )
	{
		return ssh2_sftp_symlink( $this->_sftpId, $targetName, $link );
	}

	/**
	 * Deletes a file on the remote filesystem
	 *
	 * @param string $path
	 * @return boolean
	 */
	public function unlink( $path )
	{
		return ssh2_sftp_unlink( $this->_sftpId, $path );
	}

	public function exec( $command, $terminalType = 'vanilla', $environment = array(), $width = 80, $height = 25, $widthHeightType = SSH2_TERM_UNIT_CHARS )
	{
		return $this->consumeStream( ssh2_exec( $this->_sessionId, $command, $terminalType, $environment, $width, $height, $widthHeightType ) );
	}

	public function shell( $terminalType = 'vanilla', $environment = array(), $width = 80, $height = 25, $widthHeightType = SSH2_TERM_UNIT_CHARS )
	{
		return ssh2_shell( $this->_sessionId, $terminalType, $environment, $width, $height, $widthHeightType );
	}

	public function tunnel( $hostName, $port )
	{
		return ssh2_tunnel( $this->_sessionId, $hostName, $port );
	}

	public function consumeStream( $stream, $bufferSize = 4096 )
	{
		$_result = null;
		
		stream_set_blocking( $stream, true );

		while ( $_buffer = fread( $stream, $bufferSize ) )
			$_result .= $_buffer;

		fclose( $stream );

		return $_result;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
}
