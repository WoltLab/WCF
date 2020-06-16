<?php
namespace wcf\data\box;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit boxes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Box
 * @since	3.0
 * 
 * @method static	Box	create(array $parameters = [])
 * @method		Box	getDecoratedObject()
 * @mixin		Box
 */
class BoxEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Box::class;
	
	/**
	 * Creates the template file for "tpl"-type boxes.
	 * 
	 * @param       integer         $languageID
	 * @param       string          $content
	 */
	public function writeTemplate($languageID, $content) {
		if ($this->getDecoratedObject()->boxType === 'tpl') {
			file_put_contents(WCF_DIR . 'templates/' . $this->getDecoratedObject()->getTplName(($languageID ?: null)) . '.tpl', $content);
		}
	}
}
