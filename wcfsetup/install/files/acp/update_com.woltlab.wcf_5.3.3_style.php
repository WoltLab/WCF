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
	if (!$style->image) {
		$basename = 'stylePreview';
		
		// Check all possible extensions for preview images on the file system.
		$files = [];
		foreach ([
			'png',
			'gif',
			'jpg',
			'jpeg',
			'svg',
		] as $extension) {
			$fileName = $style->getAssetPath().$basename.'.'.$extension;
			if (is_readable($fileName)) {
				$files[$extension] = filemtime($fileName);
			}
		}
		
		// Sort by modification time in descending order.
		arsort($files);
		
		if (!empty($files)) {
			// This loop will pick the newest file first.
			foreach ($files as $extension => $unused) {
				$newName = $basename.'.'.$extension;
				
				$styleEditor->update([
					'image' => FileUtil::getRelativePath(WCF_DIR.'images/', $style->getAssetPath()).$newName,
				]);
				
				// break after handling the newest file, simulating
				// array_key_first().
				break;
			}
		}
	}
	
	if (!$style->image2x) {
		$basename = 'stylePreview@2x';
		
		$files = [];
		foreach ([
			'png',
			'gif',
			'jpg',
			'jpeg',
			'svg',
		] as $extension) {
			$fileName = $style->getAssetPath().$basename.'.'.$extension;
			if (is_readable($fileName)) {
				$files[$extension] = filemtime($fileName);
			}
		}
		arsort($files);
		
		if (!empty($files)) {
			foreach ($files as $extension => $unused) {
				$newName = $basename.'.'.$extension;
				
				$styleEditor->update([
					'image2x' => FileUtil::getRelativePath(WCF_DIR.'images/', $style->getAssetPath()).$newName,
				]);
				
				break;
			}
		}
	}
}
