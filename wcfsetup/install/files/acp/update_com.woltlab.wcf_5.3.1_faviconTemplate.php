<?php

use wcf\data\style\StyleList;

/**
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

$styleList = new StyleList();
$styleList->readObjects();

foreach ($styleList as $style) {
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
}
