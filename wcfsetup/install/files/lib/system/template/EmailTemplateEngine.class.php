<?php
namespace wcf\system\template;
use wcf\system\WCF;

/**
 * Loads and displays templates in emails.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template
 */
class EmailTemplateEngine extends TemplateEngine {
	/**
	 * @inheritDoc
	 */
	protected $environment = 'email';
	
	/**
	 * @inheritDoc
	 */
	public function getTemplateGroupID() {
		static $initialized = false;
		
		if (!$initialized) {
			$sql = "SELECT	templateGroupID
				FROM	wcf".WCF_N."_template_group
				WHERE	templateGroupFolderName = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(['_wcf_email/']);
			
			parent::setTemplateGroupID($statement->fetchSingleColumn());
			$initialized = true;
		}
		
		return parent::getTemplateGroupID();
	}
	
	/**
	 * This method always throws, because changing the template group is not supported.
	 * 
	 * @param	integer		$templateGroupID
	 * @throws	\BadMethodCallException
	 */
	public function setTemplateGroupID($templateGroupID) {
		throw new \BadMethodCallException("You may not change the template group of the email template engine");
	}
}
