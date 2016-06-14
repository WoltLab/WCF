<?php
namespace wcf\system\image;
use wcf\system\exception\SystemException;
use wcf\system\image\adapter\GDImageAdapter;
use wcf\system\image\adapter\ImageAdapter;
use wcf\system\image\adapter\ImagickImageAdapter;
use wcf\system\SingletonFactory;

/**
 * Handler for all available image adapters.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Image
 */
class ImageHandler extends SingletonFactory {
	/**
	 * list of valid image adapters.
	 * @var	string[]
	 */
	protected $imageAdapters = [
		'gd' => GDImageAdapter::class,
		'imagick' => ImagickImageAdapter::class
	];
	
	/**
	 * image adapter class name
	 * @var	string
	 */
	protected $adapterClassName = '';
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		if (!isset($this->imageAdapters[IMAGE_ADAPTER_TYPE])) {
			throw new SystemException("Image adapter referred as '". IMAGE_ADAPTER_TYPE . "' is unknown.");
		}
		
		$imageAdapter = $this->imageAdapters[IMAGE_ADAPTER_TYPE];
		$isSupported = call_user_func([$imageAdapter, 'isSupported']);
		
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
