<?php

namespace wcf\system\html\upcast\node;

use wcf\system\html\node\AbstractHtmlNode;
use wcf\system\html\node\AbstractHtmlNodeProcessor;

/**
 * Default implementation for html upcast nodes.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
abstract class AbstractHtmlUpcastNode extends AbstractHtmlNode implements IHtmlUpcastNode
{
    /**
     * @inheritDoc
     */
    public function isAllowed(AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        // there is no validation for upcast nodes
        return [];
    }
}
