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
	 * @param	int		$type
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
	 * @param	int		$width
	 * @param	int		$height
	 */
	public function createEmptyImage($width, $height);
	
	/**
	 * Creates a thumbnail from previously loaded image.
	 * 
	 * @param	int		$maxWidth
	 * @param	int		$maxHeight
	 * @param	bool		$preserveAspectRatio
	 * @return	mixed
	 */
	public function createThumbnail($maxWidth, $maxHeight, $preserveAspectRatio = true);
	
	/**
	 * Clips a part of currently loaded image, overwrites image resource within instance.
	 * 
	 * @param	int		$originX
	 * @param	int		$originY
	 * @param	int		$width
	 * @param	int		$height
	 * @see		\wcf\system\image\adapter\IImageAdapter::getImage()
	 */
	public function clip($originX, $originY, $width, $height);
	
	/**
	 * Resizes an image with optional scaling, overwrites image resource within instance.
	 * 
	 * @param	int		$originX
	 * @param	int		$originY
	 * @param	int		$originWidth
	 * @param	int		$originHeight
	 * @param	int		$targetWidth
	 * @param	int		$targetHeight
	 * @see		\wcf\system\image\adapter\IImageAdapter::getImage()
	 */
	public function resize($originX, $originY, $originWidth, $originHeight, $targetWidth, $targetHeight);
	
	/**
	 * Draws a rectangle, overwrites image resource within instance.
	 * 
	 * @param	int		$startX
	 * @param	int		$startY
	 * @param	int		$endX
	 * @param	int		$endY
	 * @see		\wcf\system\image\adapter\IImageAdapter::getImage()
	 * @see		\wcf\system\image\adapter\IImageAdapter::setColor()
	 */
	public function drawRectangle($startX, $startY, $endX, $endY);
	
	/**
	 * Draws a line of text, overwrites image resource within instance.
	 * 
	 * @param	string		$text
	 * @param	int		$x
	 * @param	int		$y
	 * @param	string		$font		path to TrueType font file
	 * @param	int		$size		font size
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
	 * @param	int		$margin		in pixels
	 * @param	int		$offsetX
	 * @param	int		$offsetY
	 * @param	string		$font		path to TrueType font file
	 * @param	int		$size		font size
	 * @param	float		$opacity
	 */
	public function drawTextRelative($text, $position, $margin, $offsetX, $offsetY, $font, $size, $opacity = 1.0);
	
	/**
	 * Returns true if the given text fits the image.
	 * 
	 * @param	string		$text
	 * @param	int		$margin
	 * @param	string		$font		path to TrueType font file
	 * @param	int		$size		font size
	 * @return	int
	 * @return	bool
	 */
	public function textFitsImage($text, $margin, $font, $size);
	
	/**
	 * Adjusts the given font size so that the given text fits on the current
	 * image. Returns 0 if no appropriate font size could be determined.
	 * 
	 * @param	string		$text
	 * @param	int		$margin
	 * @param	string		$font		path to TrueType font file
	 * @param	int		$size		font size
	 * @return	int
	 */
	public function adjustFontSize($text, $margin, $font, $size);
	
	/**
	 * Sets active color.
	 * 
	 * @param	int		$red
	 * @param	int		$green
	 * @param	int		$blue
	 */
	public function setColor($red, $green, $blue);
	
	/**
	 * Returns true if a color has been set.
	 * 
	 * @return	bool
	 */
	public function hasColor();
	
	/**
	 * Sets a color to be transparent with alpha 0.
	 * 
	 * @param	int		$red
	 * @param	int		$green
	 * @param	int		$blue
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
	 * @return	int
	 */
	public function getWidth();
	
	/**
	 * Returns image height
	 * 
	 * @return	int
	 */
	public function getHeight();
	
	/**
	 * Returns the image type (GD only)
	 * 
	 * @return	int
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
	 * @param	int		$x
	 * @param	int		$y
	 * @param	float		$opacity
	 */
	public function overlayImage($file, $x, $y, $opacity);
	
	/**
	 * Overlays the given image at a relative position.
	 * 
	 * @param	string		$file
	 * @param	string		$position
	 * @param	int		$margin
	 * @param	float		$opacity
	 */
	public function overlayImageRelative($file, $position, $margin, $opacity);
	
	/**
	 * Saves an image using a different file type.
	 * 
	 * @since 5.4
	 */
	public function saveImageAs($image, string $filename, string $type, int $quality = 100): void;
	
	/**
	 * Determines if an image adapter is supported.
	 * 
	 * @return	bool
	 */
	public static function isSupported();
}
