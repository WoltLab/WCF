<?php
namespace wcf\system\html\output\node;
use wcf\system\html\node\AbstractHtmlNode;
use wcf\system\html\node\AbstractHtmlNodeProcessor;

/**
 * Default implementation for html output nodes.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 */
abstract class AbstractHtmlOutputNode extends AbstractHtmlNode implements IHtmlOutputNode {
	/**
	 * desired output type
	 * @var string
	 */
	protected $outputType = 'text/html';
	
	/**
	 * @inheritDoc
	 */
	public function isAllowed(AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		// there is no validation for output nodes
		return [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function setOutputType($outputType) {
		$this->outputType = $outputType;
	}
}
