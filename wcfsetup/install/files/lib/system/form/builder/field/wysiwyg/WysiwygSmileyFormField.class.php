<?php
namespace wcf\system\form\builder\field\wysiwyg;
use wcf\data\smiley\Smiley;
use wcf\system\form\builder\field\AbstractFormField;

/**
 * Implementation of a form field for the list smilies of a certain category used by a wysiwyg
 * form container.
 * 
 * This is no really a form field in that it does not read any data but only prints data.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
class WysiwygSmileyFormField extends AbstractFormField {
	/**
	 * list of available smilies
	 * @var	Smiley[]
	 */
	protected $smilies = [];
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__wysiwygSmileyFormField';
	
	/**
	 * Returns the list of available smilies.
	 * 
	 * @return	Smiley[]
	 */
	public function getSmilies() {
		return $this->smilies;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasSaveValue() {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAvailable() {
		return parent::isAvailable() && !empty($this->smilies);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		// does nothing
	}
	
	/**
	 * Sets the list of available smilies.
	 * 
	 * @param	Smiley[]	$smilies	available smilies
	 * @return	WysiwygSmileyFormField		this form field
	 */
	public function smilies(array $smilies) {
		foreach ($smilies as $smiley) {
			if (!is_object($smiley)) {
				throw new \InvalidArgumentException("Given value array contains invalid value of type " . gettype($smiley) . ".");
			}
			else if (!($smiley instanceof Smiley)) {
				throw new \InvalidArgumentException("Given value array contains invalid object of class " . get_class($smiley) . ".");
			}
		}
		
		$this->smilies = $smilies;
		
		return $this;
	}
}
