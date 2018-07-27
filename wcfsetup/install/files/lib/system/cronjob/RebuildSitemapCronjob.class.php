<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\worker\SitemapRebuildWorker;

/**
 * Rebuild the sitemap. 
 * 
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 * @since	3.1
 */
class RebuildSitemapCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		$worker = new SitemapRebuildWorker([]);
		$count = 0;
		
		while ($worker->getProgress() < 100) {
			$worker->setLoopCount($count);
			$worker->execute();
			$count++;
		}
	}
}
