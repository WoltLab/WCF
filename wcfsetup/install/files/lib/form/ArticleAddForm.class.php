<?php
namespace wcf\form;
use wcf\data\article\Article;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows the article add form.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
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
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		/** @var Article $article */
		$article = $this->objectAction->getReturnValues()['returnValues'];
		if ($article->publicationStatus == Article::PUBLISHED) {
			HeaderUtil::redirect($article->getLink());
			exit;
		}
	}
}
