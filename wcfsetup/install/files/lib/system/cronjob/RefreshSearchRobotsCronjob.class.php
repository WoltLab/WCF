<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\cache\builder\SpiderCacheBuilder;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use wcf\util\XML;

/**
 * Refreshes list of search robots.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
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
		$filename = FileUtil::downloadFileFromHttp('http://www.woltlab.com/spiderlist/spiderList2.xml', 'spiders');
		$xml = new XML();
		$xml->load($filename);
		
		$xpath = $xml->xpath();
		
		// fetch spiders
		$spiders = $xpath->query('/ns:data/ns:spider');
		
		if (!empty($spiders)) {
			// delete old entries
			$sql = "DELETE FROM wcf".WCF_N."_spider";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			
			$statementParameters = array();
			foreach ($spiders as $spider) {
				$identifier = mb_strtolower($spider->getAttribute('ident'));
				$name = $xpath->query('ns:name', $spider)->item(0);
				$info = $xpath->query('ns:url', $spider)->item(0);
				
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
				
				WCF::getDB()->beginTransaction();
				foreach ($statementParameters as $parameters) {
					$statement->execute(array(
						$parameters['spiderIdentifier'],
						$parameters['spiderName'],
						$parameters['spiderURL']
					));
				}
				WCF::getDB()->commitTransaction();
			}
			
			// clear spider cache
			SpiderCacheBuilder::getInstance()->reset();
		}
		
		// delete tmp file
		@unlink($filename);
	}
}
