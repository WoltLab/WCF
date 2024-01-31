<?php

namespace wcf\system\html\upcast\node;

use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\html\output\HtmlOutputProcessor;

/**
 * Processes a HTML string and renders the final output for edit.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @method      HtmlOutputProcessor     getHtmlProcessor()
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
