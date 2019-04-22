<?php
namespace wcf\system\form\builder;

/**
 * Form node whose contents can be set directly.
 * 
 * This node should generally not be used. Instead, `TemplateFormNode` should be used.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	5.2
 */
class CustomFormNode implements IFormChildNode {
	use TFormChildNode;
	use TFormNode;
	
	/**
	 * content of the custom form node
	 * @var	string
	 */
	protected $content = '';
	
	/**
	 * Sets the content of this form node and returns this form node.
	 * 
	 * @param	string		$content
	 * @return	static		this form node
	 */
	public function content($content) {
		$this->content = $content;
		
		return $this;
	}
	
	/**
	 * Returns the content of the custom form node.
	 * 
	 * @return	string
	 */
	public function getContent() {
		return $this->content;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		return $this->getContent();
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		// does nothing
	}
}
