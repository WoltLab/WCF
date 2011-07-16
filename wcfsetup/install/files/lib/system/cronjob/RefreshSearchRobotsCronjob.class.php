<?php
namespace wcf\system\cronjob;
use wcf\system\cache\CacheHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use wcf\util\XML;

/**
 * Refreshes list of search robots.
 * 
 * @todo	Use new XML-API
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
class RefreshSearchRobotsCronjob implements Cronjob {
	/**
	 * @see wcf\system\cronjob\Cronjob::execute()
	 */
	public function execute(array $data) {
		$filename = FileUtil::downloadFileFromHttp('http://www.woltlab.com/spiderlist/spiderlist.xml', 'spiders');
		$xml = new XML($filename);
		$spiders = $xml->getElementTree('spiderlist');
		
		if (count($spiders['children'])) {
			// delete old entries
			$sql = "DELETE FROM wcf".WCF_N."_spider";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			
			$statementParameters = array();
			foreach ($spiders['children'] as $spider) {
				$identifier = $spider['attrs']['ident'];
				
				// get attributes
				foreach ($spider['children'] as $values) {
					$spider[$values['name']] = $values['cdata'];
				}
				
				$name = $spider['name'];
				$info = '';
				if (isset($spider['info'])) $info = $spider['info'];
				
				$statementParameters[] = array(
					'spiderIdentifier' => StringUtil::toLowerCase($identifier),
					'spiderName' => $name,
					'spiderURL' => $info
				);
			}
			
			if (!empty($statementParameters)) {
				$sql = "INSERT INTO	wcf".WCF_N."_spider
							(spiderIdentifier, spiderName, spiderURL)
					VALUES		(?, ?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				
				foreach ($statementParameters as $parameters) {
					$statement->execute(array(
						$parameters['spiderIdentifier'],
						$parameters['spiderName'],
						$parameters['spiderURL']
					));
				}
			}
			
			// clear spider cache
			CacheHandler::getInstance()->clear(WCF_DIR.'cache', 'cache.spiders.php');
		}
		
		// delete tmp file
		@unlink($filename);
	}
}
