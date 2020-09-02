<?php

use wcf\data\style\StyleEditor;
use wcf\data\style\StyleList;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\background\job\DownloadStyleLogoJob;
use wcf\util\FileUtil;
use wcf\util\Url;

/**
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

$styleList = new StyleList();
$styleList->readObjects();

foreach ($styleList as $style) {
	$style->loadVariables();
	$variables = $style->getVariables();
	$styleEditor = new StyleEditor($style);
	
	// 1) Move existing asset path folder out of the way.
	// It's unlikely that one exists, but having an existing folder will create a small mess.
	$assetBackupPath = FileUtil::removeTrailingSlash($style->getAssetPath()) . '.old53/';
	if (file_exists($style->getAssetPath())) {
		if (file_exists($assetBackupPath)) {
			// If the assetBackupPath exists then we have one of the following situations.
			// 
			// 1) The source installation is configured pretty strangely.
			//    This situation can be ignored due to its unlikelyness.
			// 2) The style was successfully migrated, but the update failed at a later point.
			//    There is no harm in skipping this style.
			// 3) The style was unsuccessfully migrated and failed in steps 3 to 7.
			//    To reach this situation this script has to fail twice (except in the unlikely situation that the assetPath existed previously).
			//    It's unlikely that it succeeds this time. To skipping this style is a sane option.
			//    Also the administrator is able to manually fix up the style later, because nothing is deleted.
			//
			// Concluding: This is a sufficiently reliable test to check whether this script was executed.
			continue;
		}
		rename($style->getAssetPath(), $assetBackupPath);
	}
	
	// 2) Create asset folder.
	FileUtil::makePath($style->getAssetPath());
	
	// 3) If the file had a custom image folder we need to copy all files into the asset folder.
	// Moving the files is unsafe, because multiple styles can share a single image folder.
	$srcPath = FileUtil::addTrailingSlash(WCF_DIR.$style->imagePath);
	if ($srcPath !== WCF_DIR && $srcPath !== WCF_DIR.'images/') {
		if ($srcPath == $style->getAssetPath()) {
			$srcPath = $assetBackupPath;
		}
		
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$srcPath,
				\FilesystemIterator::SKIP_DOTS
			), 
			\RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($iterator as $file) {
			/** @var \SplFileInfo $file */
			if (!$file->isFile()) continue;
			
			$relative = FileUtil::getRelativePath($srcPath, $file->getPath());
			FileUtil::makePath($style->getAssetPath() . $relative);
			copy($file->getPathname(), $style->getAssetPath() . $relative . $file->getBasename());
		}
	}
	$styleEditor->update([
		'imagePath' => FileUtil::unifyDirSeparator(FileUtil::getRelativePath(WCF_DIR, $style->getAssetPath())),
	]);
	
	// 4) Copy style previews into the asset folder.
	// Moving *should* be safe here, unless the admin manually edited the style, but better play safe.
	if ($style->image && file_exists(WCF_DIR . 'images/' . $style->image)) {
		$extension = pathinfo($style->image, PATHINFO_EXTENSION);
		copy(
			WCF_DIR . 'images/' . $style->image,
			$style->getAssetPath() . 'stylePreview.' . $extension
		);
		$styleEditor->update([
			'image' => 'stylePreview.' . $extension,
		]);
	}

	if ($style->image2x && file_exists(WCF_DIR . 'images/' . $style->image2x)) {
		$extension = pathinfo($style->image2x, PATHINFO_EXTENSION);
		copy(
			WCF_DIR . 'images/' . $style->image2x,
			$style->getAssetPath() . 'stylePreview@2x.' . $extension
		);
		$styleEditor->update([
			'image2x' => 'stylePreview@2x.' . $extension,
		]);
	}
	
	// 5) Copy coverPhotos into the asset folder.
	// Moving is safe here, but for consistency we are copying.
	if (file_exists(WCF_DIR . 'images/coverPhotos/' . $style->styleID . '.' . $style->coverPhotoExtension)) {
		copy(
			WCF_DIR . 'images/coverPhotos/' . $style->styleID . '.' . $style->coverPhotoExtension,
			$style->getAssetPath() . 'coverPhoto.' . $style->coverPhotoExtension
		);
	}
	
	// 6) Copy favicon related files into the asset folder.
	// Moving is safe here, but for consistency we are copying.
	foreach ([
		'android-chrome-192x192.png',
		'android-chrome-256x256.png',
		'apple-touch-icon.png',
		'browserconfig.xml',
		'favicon.ico',
		'favicon-template.png',
		'manifest.json',
		'mstile-150x150.png',
	] as $filename) {
		if (file_exists(WCF_DIR . 'images/favicon/' . $style->styleID . '.' . $filename)) {
			copy(
				WCF_DIR . 'images/favicon/' . $style->styleID . '.' . $filename,
				$style->getAssetPath() . $filename
			);
		}
	}
	
	// 7) Copy styleLogo.
	// Moving is unsafe, we cannot even guarantee that the logo is a file on the local filesystem.
	$queueDownload = false;
	foreach (['pageLogo', 'pageLogoMobile'] as $type) {
		if (empty($variables[$type])) continue;
		if (Url::is($variables[$type])) {
			$queueDownload = true;
			continue;
		}
		
		$srcPath = WCF_DIR . 'images/' . $variables[$type];
		if (!file_exists($srcPath)) {
			$srcPath = WCF_DIR . $style->imagePath . '/' . $style->getVariable($type);
			if (!file_exists($srcPath)) {
				continue;
			}
		}
		copy(
			$srcPath,
			$style->getAssetPath() . basename($srcPath)
		);
		$variables[$type] = basename($srcPath);
	}
	$styleEditor->setVariables($variables);
	if ($queueDownload) {
		BackgroundQueueHandler::getInstance()->enqueueIn(new DownloadStyleLogoJob($style), 150);
	}
}
