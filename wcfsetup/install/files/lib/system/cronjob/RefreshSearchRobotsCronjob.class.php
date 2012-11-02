<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\cache\CacheHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use wcf\util\XML;

/**
 * Refreshes list of search robots.
 * 
 * @todo	Add xsd to spiderlist on server
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class RefreshSearchRobotsCronjob implements ICronjob {
	/**
	 * @see	wcf\system\ICronjob::execute()
	 */
	public function execute(Cronjob $cronjob) {
		$filename = FileUtil::downloadFileFromHttp('http://www.woltlab.com/spiderlist/spiderlist.xml', 'spiders');
		$xml = new XML();
		$xml->load($filename);
		
		$xpath = $xml->xpath();
		
		// fetch spiders
		$spiders = $xpath->query('/spiderlist/spider');
		
		if (!empty($spiders)) {
			// delete old entries
			$sql = "DELETE FROM wcf".WCF_N."_spider";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			
			$statementParameters = array();
			foreach ($spiders as $spider) {
				$identifier = StringUtil::toLowerCase($spider->getAttribute('ident'));
				$name = $xpath->query('name', $spider)->item(0);
				$info = $xpath->query('info', $spider)->item(0);
				
				$statementParameters[$identifier] = array(
					'spiderIdentifier' => $identifier,
					'spiderName' => $name->nodeValue,
					'spiderURL' => $info ? $info->nodeValue : ''
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
