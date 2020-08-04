<?php
namespace wcf\system\image\adapter;

/**
 * Basic interface for all image adapters.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Image\Adapter
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
	 * @param	boolean		$preserveAspectRatio
	 * @return	mixed
	 */
	public function createThumbnail($maxWidth, $maxHeight, $preserveAspectRatio = true);
	
	/**
	 * Clips a part of currently loaded image, overwrites image resource within instance.
	 * 
	 * @param	integer		$originX
	 * @param	integer		$originY
	 * @param	integer		$width
	 * @param	integer		$height
	 * @see		\wcf\system\image\adapter\IImageAdapter::getImage()
	 */
	public function clip($originX, $originY, $width, $height);
	
	/**
	 * Resizes an image with optional scaling, overwrites image resource within instance.
	 * 
	 * @param	integer		$originX
	 * @param	integer		$originY
	 * @param	integer		$originWidth
	 * @param	integer		$originHeight
	 * @param	integer		$targetWidth
	 * @param	integer		$targetHeight
	 * @see		\wcf\system\image\adapter\IImageAdapter::getImage()
	 */
	public function resize($originX, $originY, $originWidth, $originHeight, $targetWidth, $targetHeight);
	
	/**
	 * Draws a rectangle, overwrites image resource within instance.
	 * 
	 * @param	integer		$startX
	 * @param	integer		$startY
	 * @param	integer		$endX
	 * @param	integer		$endY
	 * @see		\wcf\system\image\adapter\IImageAdapter::getImage()
	 * @see		\wcf\system\image\adapter\IImageAdapter::setColor()
	 */
	public function drawRectangle($startX, $startY, $endX, $endY);
	
	/**
	 * Draws a line of text, overwrites image resource within instance.
	 * 
	 * @param	string		$text
	 * @param	integer		$x
	 * @param	integer		$y
	 * @param	string		$font		path to TrueType font file
	 * @param	integer		$size		font size
	 * @param	float		$opacity
	 * @see		\wcf\system\image\adapter\IImageAdapter::getImage()
	 * @see		\wcf\system\image\adapter\IImageAdapter::setColor()
	 */
	public function drawText($text, $x, $y, $font, $size, $opacity = 1.0);
	
	/**
	 * Draws (multiple lines of) text on the image at the given relative position
	 * with a certain margin to the image border.
	 * 
	 * @param	string		$text
	 * @param	string		$position
	 * @param	integer		$margin		in pixels
	 * @param	integer		$offsetX
	 * @param	integer		$offsetY
	 * @param	string		$font		path to TrueType font file
	 * @param	integer		$size		font size
	 * @param	float		$opacity
	 */
	public function drawTextRelative($text, $position, $margin, $offsetX, $offsetY, $font, $size, $opacity = 1.0);
	
	/**
	 * Returns true if the given text fits the image.
	 * 
	 * @param	string		$text
	 * @param	integer		$margin
	 * @param	string		$font		path to TrueType font file
	 * @param	integer		$size		font size
	 * @return	integer
	 * @return	boolean
	 */
	public function textFitsImage($text, $margin, $font, $size);
	
	/**
	 * Adjusts the given font size so that the given text fits on the current
	 * image. Returns 0 if no appropriate font size could be determined.
	 * 
	 * @param	string		$text
	 * @param	integer		$margin
	 * @param	string		$font		path to TrueType font file
	 * @param	integer		$size		font size
	 * @return	integer
	 */
	public function adjustFontSize($text, $margin, $font, $size);
	
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
	 * Returns the image type (GD only)
	 * 
	 * @return	integer
	 */
	public function getType();
	
	/**
	 * Rotates an image the specified number of degrees.
	 * 
	 * @param	float		$degrees	number of degrees to rotate the image clockwise
	 * @return	mixed
	 */
	public function rotate($degrees);
	
	/**
	 * Overlays the given image at an absolute position.
	 * 
	 * @param	string		$file
	 * @param	integer		$x
	 * @param	integer		$y
	 * @param	float		$opacity
	 */
	public function overlayImage($file, $x, $y, $opacity);
	
	/**
	 * Overlays the given image at a relative position.
	 * 
	 * @param	string		$file
	 * @param	string		$position
	 * @param	integer		$margin
	 * @param	float		$opacity
	 */
	public function overlayImageRelative($file, $position, $margin, $opacity);
	
	/**
	 * Determines if an image adapter is supported.
	 * 
	 * @return	boolean
	 */
	public static function isSupported();
}
