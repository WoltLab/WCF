<?php
namespace wcf\acp\form;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\registry\RegistryHandler;
use wcf\system\WCF;
use wcf\system\worker\SitemapRebuildWorker;

/**
 * Shows the sitemap edit form.
 * 
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.1
 */
class SitemapEditForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $templateName = 'sitemapEdit';
	
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.maintenance.sitemap';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.management.canRebuildData'];

	/**
	 * The sitemap object type name.
	 * @var string
	 */
	public $objectTypeName = null;

	/**
	 * The sitemap object type.
	 * @var ObjectType
	 */
	public $objectType = null;
	
	/**
	 * The priority for this sitemap object.
	 * @var float
	 */
	public $priority = 0.5;
	
	/**
	 * The changeFreq for this sitemap object.
	 * @var string
	 */
	public $changeFreq = 'monthly';
	
	/**
	 * An array with valid changeFreq values.
	 *
	 * @var array<string>
	 */
	public $validChangeFreq = [
		'always',
		'hourly',
		'daily',
		'weekly',
		'monthly',
		'yearly',
		'never'
	];
	
	/**
	 * `1` iff the sitemap is disabled. Otherwise `0`.
	 * @var integer
	 */
	public $isDisabled = 0;

	/**
	 * The time in seconds how long the sitemap should be cached. 
	 * @var integer
	 */
	public $rebuildTime = 172800; // two days
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_GET['objectType'])) $this->objectTypeName = $_GET['objectType'];
		$this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.sitemap.object', $this->objectTypeName);
		
		if ($this->objectType === null) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$sitemapData = RegistryHandler::getInstance()->get('com.woltlab.wcf', SitemapRebuildWorker::REGISTRY_PREFIX . $this->objectTypeName);
			$sitemapData = @unserialize($sitemapData);
			
			if (is_array($sitemapData)) {
				$this->priority = $sitemapData['priority'];
				$this->changeFreq = $sitemapData['changeFreq'];
				$this->rebuildTime = $sitemapData['rebuildTime'];
				$this->isDisabled = $sitemapData['isDisabled'];
			}
			else {
				if ($this->objectType->priority !== null) $this->priority = $this->objectType->priority;
				if ($this->objectType->changeFreq !== null) $this->changeFreq = $this->objectType->changeFreq;
				if ($this->objectType->rebuildTime !== null) $this->rebuildTime = $this->objectType->rebuildTime;
				if ($this->objectType->isDisabled !== null) $this->isDisabled = $this->objectType->isDisabled;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['priority'])) $this->priority = round(floatval($_POST['priority']), 1);
		if (isset($_POST['changeFreq'])) $this->changeFreq = $_POST['changeFreq'];
		if (isset($_POST['rebuildTime'])) $this->rebuildTime = intval($_POST['rebuildTime']);
		$this->isDisabled = (isset($_POST['isDisabled'])) ? 1 : 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if ($this->priority > 1 || $this->priority < 0) {
			throw new UserInputException('priority', 'invalid');
		}
		
		if (!in_array($this->changeFreq, $this->validChangeFreq)) {
			throw new UserInputException('changeFreq');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		RegistryHandler::getInstance()->set('com.woltlab.wcf', SitemapRebuildWorker::REGISTRY_PREFIX . $this->objectTypeName, serialize([
			'priority' => $this->priority,
			'changeFreq' => $this->changeFreq,
			'rebuildTime' => $this->rebuildTime,
			'isDisabled' => $this->isDisabled,
		]));
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'objectType' => $this->objectType,
			'priority' => $this->priority,
			'changeFreq' => $this->changeFreq,
			'rebuildTime' => $this->rebuildTime,
			'validChangeFreq' => $this->validChangeFreq,
			'isDisabled' => $this->isDisabled
		]);
	}
}
