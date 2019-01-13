<?php
namespace wcf\form;
use wcf\system\WCF;

/**
 * Shows the article add form.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Form
 * @since       5.2
 */
class ArticleAddForm extends \wcf\acp\form\ArticleAddForm {
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(['articleIsFrontend' => true]);
	}
}
