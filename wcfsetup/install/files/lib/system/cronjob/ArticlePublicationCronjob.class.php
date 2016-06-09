<?php
namespace wcf\system\cronjob;
use wcf\data\article\ArticleEditor;
use wcf\data\article\ArticleList;
use wcf\data\cronjob\Cronjob;

/**
 * Publishes delayed articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 * @since       2.2
 */
class ArticlePublicationCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		$articleList = new ArticleList();
		$articleList->getConditionBuilder()->add('article.publicationStatus = ?', [2]);
		$articleList->getConditionBuilder()->add('article.publicationDate > ?', [0]);
		$articleList->getConditionBuilder()->add('article.publicationDate <= ?', [TIME_NOW]);
		$articleList->decoratorClassName = ArticleEditor::class;
		$articleList->readObjects();
		
		foreach ($articleList as $editor) {
			/** @var ArticleEditor $editor */
			$editor->update([
				'time' => $editor->publicationDate,
				'publicationStatus' => 1,
				'publicationDate' => 0
			]);
		}
	}
}
