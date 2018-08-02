<?php
use wcf\data\option\OptionEditor;

/**
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

// disable the deprecated option `like_show_summary`
OptionEditor::import([
	'like_show_summary' => 0
]);
