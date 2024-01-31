<?php

namespace wcf\system\html\upcast\node;

use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\html\upcast\HtmlUpcastProcessor;

/**
 * Processes a HTML string and renders the final output for edit.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @method      HtmlUpcastProcessor     getHtmlProcessor()
 */
class HtmlUpcastNodeProcessor extends AbstractHtmlNodeProcessor
{
    /**
     * @inheritDoc
     */
    protected $nodeInterface = IHtmlUpcastNode::class;

    #[\Override]
    public function process()
    {
        $this->invokeHtmlNode(new HtmlUpcastNodeWoltlabMetacode());
    }
}
