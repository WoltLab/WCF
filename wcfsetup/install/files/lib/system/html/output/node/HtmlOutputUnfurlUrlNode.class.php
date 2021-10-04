<?php

namespace wcf\system\html\output\node;

use wcf\system\html\AbstractHtmlProcessor;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\html\node\HtmlNodeUnfurlLink;
use wcf\system\html\output\AmpHtmlOutputProcessor;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\util\StringUtil;

/**
 * Node class to replace unfurled urls in the output.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       5.4
 */
class HtmlOutputUnfurlUrlNode extends AbstractHtmlOutputNode
{
    private static $disableUnfurlingForContext = ['com.woltlab.wcf.user.signature'];

    /**
     * @inheritDoc
     */
    protected $tagName = 'a';

    /**
     * @inheritDoc
     */
    public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        if ($this->outputType !== 'text/html') {
            return;
        }

        $htmlProcessor = $htmlNodeProcessor->getHtmlProcessor();
        if ($htmlProcessor instanceof AmpHtmlOutputProcessor) {
            return;
        }

        if (
            $htmlProcessor instanceof AbstractHtmlProcessor
            && \in_array($htmlProcessor->getContext()['objectType'], self::$disableUnfurlingForContext)
        ) {
            return;
        }

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $attribute = $element->getAttribute(HtmlNodeUnfurlLink::UNFURL_URL_ID_ATTRIBUTE_NAME);
            if (
                !empty($attribute)
                && MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.unfurlUrl', $attribute) !== null
            ) {
                $enableUgc = true;
                $processor = $htmlNodeProcessor->getHtmlProcessor();
                if ($processor instanceof HtmlOutputProcessor) {
                    $enableUgc = $processor->enableUgc;
                }

                $nodeIdentifier = StringUtil::getRandomID();
                $htmlNodeProcessor->addNodeData($this, $nodeIdentifier, [
                    'urlId' => $attribute,
                    'enableUgc' => $enableUgc,
                ]);

                $htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function replaceTag(array $data)
    {
        /** @var \wcf\data\unfurl\url\UnfurlUrl $object */
        $object = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.unfurlUrl', $data['urlId']);

        return $object->render($data['enableUgc']);
    }
}
