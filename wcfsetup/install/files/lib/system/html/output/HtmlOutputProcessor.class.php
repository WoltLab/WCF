<?php

namespace wcf\system\html\output;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\html\AbstractHtmlProcessor;
use wcf\system\html\output\node\HtmlOutputNodeProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Processes stored HTML for final display.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class HtmlOutputProcessor extends AbstractHtmlProcessor
{
    /**
     * Generate the table of contents, implicitly enable this for certain object types on demand.
     * @var bool|null
     * @since 5.2
     */
    public $enableToc;

    /**
     * output node processor instance
     * @var HtmlOutputNodeProcessor
     */
    protected $htmlOutputNodeProcessor;

    /**
     * content language id
     * @var ?int
     */
    protected $languageID;

    /**
     * desired output type
     * @var string
     */
    protected $outputType = 'text/html';

    /**
     * enables rel=ugc for external links
     * @var bool
     */
    public $enableUgc = true;

    /**
     * Processes the input html string.
     *
     * @return void
     */
    public function process(string $html, string $objectType, int $objectID, bool $doKeywordHighlighting = true, ?int $languageID = null)
    {
        $this->languageID = $languageID;
        $this->setContext($objectType, $objectID);

        MessageEmbeddedObjectManager::getInstance()->setActiveMessage($objectType, $objectID, $this->languageID);

        try {
            $this->getHtmlOutputNodeProcessor()->setOutputType($this->outputType);
            $this->getHtmlOutputNodeProcessor()->enableKeywordHighlighting($doKeywordHighlighting);
            $this->getHtmlOutputNodeProcessor()->load($this, $html);
            $this->getHtmlOutputNodeProcessor()->process();
        } finally {
            MessageEmbeddedObjectManager::getInstance()->reset();
        }
    }

    /**
     * Sets the desired output type.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setOutputType(string $outputType)
    {
        if (!\in_array($outputType, ['text/html', 'text/simplified-html', 'text/plain'])) {
            throw new \InvalidArgumentException(
                "Expected 'text/html', 'text/simplified-html' or 'text/plain', but received '" . $outputType . "'"
            );
        }

        $this->outputType = $outputType;
    }

    #[\Override]
    public function getHtml()
    {
        $context = $this->getContext();
        MessageEmbeddedObjectManager::getInstance()->setActiveMessage($context['objectType'], $context['objectID'], $this->languageID);

        try {
            $html = $this->getHtmlOutputNodeProcessor()->getHtml();
        } finally {
            MessageEmbeddedObjectManager::getInstance()->reset();
        }

        return $html;
    }

    /**
     * @throws \InvalidArgumentException
     */
    #[\Override]
    public function setContext(string $objectType, int $objectID)
    {
        parent::setContext($objectType, $objectID);

        $objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.message', $objectType);
        if ($this->enableToc === null) {
            $this->enableToc = (!empty($objectType->additionalData['enableToc']));
        }
    }

    /**
     * Returns the output node processor instance.
     *
     * @return HtmlOutputNodeProcessor output node processor instance
     */
    protected function getHtmlOutputNodeProcessor()
    {
        if ($this->htmlOutputNodeProcessor === null) {
            $this->htmlOutputNodeProcessor = new HtmlOutputNodeProcessor();
        }

        return $this->htmlOutputNodeProcessor;
    }
}
