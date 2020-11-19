<?php

use wcf\data\style\StyleEditor;
use wcf\data\style\StyleList;
use wcf\util\FileUtil;

/**
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

$styleList = new StyleList();
$styleList->readObjects();

foreach ($styleList as $style) {
	$styleEditor = new StyleEditor($style);
	
	// Fix the name of the favicon template.
	foreach ([
		'gif',
		'jpeg',
		'jpg',
		'png',
	] as $extension) {
		if (file_exists($style->getAssetPath() . 'favicon.template.' . $extension)) {
			rename(
				$style->getAssetPath() . 'favicon.template.' . $extension,
				$style->getAssetPath() . 'favicon-template.' . $extension
			);
		}
	}
	
	// Fix the style preview.
	if (
		$style->image === basename($style->image) &&
		file_exists($style->getAssetPath() . $style->image)
	) {
		$styleEditor->update([
			'image' => FileUtil::getRelativePath(WCF_DIR.'images/', $style->getAssetPath()) . $style->image,
		]);
	}
	
	if (
		$style->image2x === basename($style->image2x) &&
		file_exists($style->getAssetPath() . $style->image2x)
	) {
		$styleEditor->update([
			'image2x' => FileUtil::getRelativePath(WCF_DIR.'images/', $style->getAssetPath()) . $style->image2x,
		]);
	}
}
