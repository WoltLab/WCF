<?php
namespace wcf\system\image\adapter;

/**
 * Basic interface for all image adapters.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.image.adapter
 * @category	Community Framework
 */
interface IImageAdapter {
	/**
	 * Loads an image resource.
	 * 
	 * @param	mixed		$image
	 * @param	integer		$type
	 */
	public function load($image, $type = 0);
	
	/**
	 * Loads an image from file.
	 * 
	 * @param	string		$file
	 */
	public function loadFile($file);
	
	/**
	 * Creates a new empty image.
	 * 
	 * @param	integer		$width
	 * @param	integer		$height
	 */
	public function createEmptyImage($width, $height);
	
	/**
	 * Creates a thumbnail from previously loaded image.
	 * 
	 * @param	integer		$maxWidth
	 * @param	integer		$maxHeight
	 * @param	boolean		$obtainDimensions
	 * @return	mixed
	 */
	public function createThumbnail($maxWidth, $maxHeight, $obtainDimensions = true);
	
	/**
	 * Clips a part of currently loaded image, overwrites image resource within instance.
	 * 
	 * @param	integer		$originX
	 * @param	integer		$originY
	 * @param	integer		$width
	 * @param	integer		$height
	 * @see		wcf\system\image\adapter\IImageAdapter::getImage()
	 */
	public function clip($originX, $originY, $width, $height);
	
	/**
	 * Resizes an image with optional scaling, overwrites image resource within instance.
	 * 
	 * @param	integer		$originX
	 * @param	integer		$originY
	 * @param	integer		$originWidth
	 * @param	integer		$originHeight
	 * @param	integer		$targetX
	 * @param	integer		$targetY
	 * @param	integer		$targetWidth
	 * @param	integer		$targetHeight
	 * @see		wcf\system\image\adapter\IImageAdapter::getImage()
	 */
	public function resize($originX, $originY, $originWidth, $originHeight, $targetX, $targetY, $targetWidth, $targetHeight);
	
	/**
	 * Draws a rectangle, overwrites image resource within instance.
	 * 
	 * @param	integer		$startX
	 * @param	integer		$startY
	 * @param	integer		$endX
	 * @param	integer		$endY
	 * @see		wcf\system\image\adapter\IImageAdapter::getImage()
	 * @see		wcf\system\image\adapter\IImageAdapter::setColor()
	 */
	public function drawRectangle($startX, $startY, $endX, $endY);
	
	/**
	 * Draws a line of text, overwrites image resource within instance.
	 * 
	 * @param	string		$string
	 * @param	integer		$x
	 * @param	integer		$y
	 * @see		wcf\system\image\adapter\IImageAdapter::getImage()
	 * @see		wcf\system\image\adapter\IImageAdapter::setColor()
	 */
	public function drawText($string, $x, $y);
	
	/**
	 * Sets active color.
	 * 
	 * @param	integer		$red
	 * @param	integer		$green
	 * @param	integer		$blue
	 */
	public function setColor($red, $green, $blue);
	
	/**
	 * Returns true if a color has been set.
	 * 
	 * @return	boolean
	 */
	public function hasColor();
	
	/**
	 * Sets a color to be transparent with alpha 0.
	 *
	 * @param	integer		$red
	 * @param	integer		$green
	 * @param	integer		$blue
	 */
	public function setTransparentColor($red, $green, $blue);
	
	/**
	 * Writes an image to disk.
	 * 
	 * @param	mixed		$image
	 * @param	string		$filename
	 */
	public function writeImage($image, $filename);
	
	/**
	 * Returns image resource.
	 * 
	 * @return	mixed
	 */
	public function getImage();
	
	/**
	 * Returns image width.
	 * 
	 * @return	integer
	 */
	public function getWidth();
	
	/**
	 * Returns image height
	 * 
	 * @return	integer
	 */
	public function getHeight();
	
	/**
	 * Determines if an image adapter is supported.
	 * 
	 * @return	boolean
	 */
	public static function isSupported();
}
