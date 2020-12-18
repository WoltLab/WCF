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
	
	// Fix the style preview.
	if (
		$style->image === FileUtil::getRelativePath(WCF_DIR.'images/', $style->getAssetPath()) ||
		!is_file($style->image)
	) {
		$styleEditor->update([
			'image' => '',
		]);
	}
	
	if (
		$style->image2x === FileUtil::getRelativePath(WCF_DIR.'images/', $style->getAssetPath()) ||
		!is_file($style->image2x)
	) {
		$styleEditor->update([
			'image2x' => '',
		]);
	}
}
