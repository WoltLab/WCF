<?php
namespace wcf\system\image;
use wcf\system\exception\SystemException;
use wcf\system\image\adapter\ImageAdapter;
use wcf\system\SingletonFactory;

/**
 * Handler for all available image adapters.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.image
 * @category	Community Framework
 */
class ImageHandler extends SingletonFactory {
	/**
	 * list of valid image adapters.
	 * @var	array<string>
	 */
	protected $imageAdapters = array(
		'gd' => 'wcf\system\image\adapter\GDImageAdapter',
		'imagick' => 'wcf\system\image\adapter\ImagickImageAdapter'
	);
	
	/**
	 * image adapter class name
	 * @var	string
	 */
	protected $adapterClassName = '';
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		if (!isset($this->imageAdapters[IMAGE_ADAPTER_TYPE])) {
			throw new SystemException("Image adapter referred as '". IMAGE_ADAPTER_TYPE . "' is unknown.");
		}
		
		$imageAdapter = $this->imageAdapters[IMAGE_ADAPTER_TYPE];
		$isSupported = call_user_func(array($imageAdapter, 'isSupported'));
		
		// fallback to GD if image adapter is not available
		if (!$isSupported) {
			$imageAdapter = $this->imageAdapters['gd'];
		}
		
		$this->adapterClassName = $imageAdapter;
	}
	
	/**
	 * Returns a new ImageAdapter instance.
	 * 
	 * @return	\wcf\system\image\adapter\ImageAdapter
	 */
	public function getAdapter() {
		return new ImageAdapter($this->adapterClassName);
	}
}
