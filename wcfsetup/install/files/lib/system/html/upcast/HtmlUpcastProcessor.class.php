<?php

namespace wcf\system\html\upcast;

use wcf\system\html\AbstractHtmlProcessor;
use wcf\system\html\upcast\node\HtmlUpcastNodeProcessor;

/**
 * Processes stored HTML for edit view.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
class HtmlUpcastProcessor extends AbstractHtmlProcessor
{
    private HtmlUpcastNodeProcessor $htmlUpcastNodeProcessor;

    /**
     * Processes the input html string.
     *
     * @param string $html html string
     * @param string $objectType object type identifier
     * @param int $objectID object id
     */
    public function process(string $html, string $objectType, int $objectID = 0): void
    {
        $this->setContext($objectType, $objectID);
        $this->getHtmlUpcastNodeProcessor()->load($this, $html);
        $this->getHtmlUpcastNodeProcessor()->process();
    }

    private function getHtmlUpcastNodeProcessor(): HtmlUpcastNodeProcessor
    {
        if (!isset($this->htmlUpcastNodeProcessor)) {
            $this->htmlUpcastNodeProcessor = new HtmlUpcastNodeProcessor();
        }

        return $this->htmlUpcastNodeProcessor;
    }

    #[\Override]
    public function getHtml(): string
    {
        return $this->getHtmlUpcastNodeProcessor()->getHtml();
    }
}
