<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Image processing utility class.
 * 
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSImage.php 356 2010-01-02 22:19:52Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 * @property integer $thumbnailWidth Width of created thumbnails. Defaults to 75
 * @property integer $thumbnailHeight Height of created thumbnails. Defaults to 75
 * @property string $thumbnailTemplate The template for creating thumbnail file names
*/
class CPSImage implements IPSBase
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* The template for thumbnail images
	* 
	* @staticvar string
	* @access protected
	*/
	protected static $m_sThumbnailTempate = 't.{fileName}';
	public static function getThumbnailTemplate() { return self::$m_sThumbnailTemplate; }
	public static function setThumbnailTemplate( $sValue ) { self::$m_sThumbnailTemplate = $sValue; }
	
	/**
	* The width of generated thumbnails
	* 
	* @staticvar int
	* @access protected
	*/
	protected static $m_iThumbnailWidth = 75;
	public static function getThumbnailWidth() { return self::$m_iThumbnailWidth; }
	public static function setThumbnailWidth( $iValue ) { self::$m_iThumbnailWidth = $iValue; }	
	
	/**
	* The height of generated thumbnails
	* 
	* @staticvar int
	* @access protected
	*/
	protected static $m_iThumbnailHeight = 75;
	public static function getThumbnailHeight() { return self::$m_iThumbnailHeight; }
	public static function setThumbnailHeight( $iValue ) { self::$m_iThumbnailHeight = $iValue; }
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Creates a thumbnail for an image. Requires GD to be installed.
	* 
	* @param string $sFileName Absolute path to file
	* @param string $sThumbName
	* @param int $iThumbWidth
	* @param int $iThumbHeight
	* @return boolean 
	* @static
	*/
	public static function createThumbnail( $sFileName, $sThumbName = null, $iThumbWidth = null, $iThumbHeight = null )
	{
		$_iType = null;
		$_bResult = false;
		
		$iThumbWidth = PS::nvl( $iThumbWidth, self::$m_iThumbnailWidth );
		$iThumbHeight = PS::nvl( $iThumbHeight, self::$m_iThumbnailHeight );
		$sThumbName = PS::nvl( $sThumbName, self::getThumbnailFilename( $sFileName ) );
		
		//	Get some info on the file...
		if ( $_arSize = getimagesize( $sFileName ) )
		{
			switch ( $_arSize[ 2 ] )
			{
				case IMAGETYPE_GIF:
					$_sSourceImage = imagecreatefromgif( $sFileName );
					break;
					
				case IMAGETYPE_PNG:
					$_sSourceImage = imagecreatefrompng( $sFileName );
					break;
					
				case IMAGETYPE_JPEG:
					$_sSourceImage = imagecreatefromjpeg( $sFileName );
					break;
			}
		
			if ( $_sSourceImage )
			{
				$_iSourceWidth = imageSX( $_sSourceImage );
				$_iSourceHeight = imageSY( $_sSourceImage );
				
				//	Calculate correct size keeping aspect ratio...
				if ( $_iSourceWidth > $_iSourceHeight )
				{
					$_iDestWidth = $iThumbWidth;
					$_iDestHeight = $_iSourceHeight * ( $iThumbHeight / $_iSourceWidth );
				}
				else if ( $_iSourceWidth < $_iSourceHeight )
				{
					$_iDestWidth = $_iSourceWidth * ( $iThumbWidth / $_iSourceHeight );
					$_iDestHeight = $iThumbHeight;
				}
				else
				{
					$_iDestWidth = $iThumbWidth;
					$_iDestHeight = $iThumbHeight;
				}
				
				//	Makethe new image
				if ( $_sDestImage = imagecreatetruecolor( $_iDestWidth, $_iDestHeight ) )
				{
					imagecopyresampled( $_sDestImage, $_sSourceImage, 0, 0, 0, 0, $_iDestWidth, $_iDestHeight, $_iSourceWidth, $_iSourceHeight );

					switch ( $_arSize[ 2 ] )
					{
						case IMAGETYPE_GIF:
							imagegif( $_sDestImage, $sThumbName );
							break;
							
						case IMAGETYPE_PNG:
							imagepng( $_sDestImage, $sThumbName );
							break;
							
						case IMAGETYPE_JPEG:
							imagejpeg( $_sDestImage, $sThumbName );
							break;
					}

					imagedestroy( $_sDestImage ); 
					$_bResult = true;
				}

				imagedestroy( $_sSourceImage ); 
			}
		}
		
		//	Did it work?
		return $_bResult;
	}
	
	/***
	* Creates a thumbnail image file name from the class template
	* 
	* @param string $sBaseName
	* @return string
	* @static
	*/
	public static function getThumbnailFilename( $sBaseName )
	{
		//	Strip off the path...
		$_arFileInfo = pathinfo( $sBaseName );
		$_sThumbName = str_ireplace( '{filename}', $_arFileInfo['basename'], self::$m_sThumbnailTempate );
		return ( $_arFileInfo['dirname'] != '.' ? $_arFileInfo['dirname'] . DIRECTORY_SEPARATOR : '' ) . $_sThumbName;
	}

}