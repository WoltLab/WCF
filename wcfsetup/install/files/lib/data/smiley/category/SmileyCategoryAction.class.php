<?php
namespace wcf\data\smiley\category;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Executes smiley category-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.smiley.category
 * @category	Community Framework
 */
class SmileyCategoryAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\category\CategoryEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('getSmilies');
	
	/**
	 * active smiley category
	 * @var	\wcf\data\smiley\category\SmileyCategory
	 */
	public $smileyCategory = null;
	
	/**
	 * Validates smiley category id.
	 */
	public function validateGetSmilies() {
		$this->smileyCategory = new SmileyCategory($this->getSingleObject()->getDecoratedObject());
		
		if ($this->smileyCategory->isDisabled) throw new IllegalLinkException();
	}
	
	/**
	 * Returns parsed template for smiley category's smilies.
	 * 
	 * @return	array
	 */
	public function getSmilies() {
		$this->smileyCategory->loadSmilies();
		
		WCF::getTPL()->assign(array(
			'smilies' => $this->smileyCategory
		));
		
		return array(
			'smileyCategoryID' => $this->smileyCategory->categoryID,
			'template' => WCF::getTPL()->fetch('__messageFormSmilies')
		);
	}
}
