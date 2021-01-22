<?php

namespace wcf\system\html\output\node;

use wcf\system\html\node\IHtmlNode;

/**
 * Default interface for html output nodes.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 */
interface IHtmlOutputNode extends IHtmlNode
{
    /**
     * Sets the desired output type.
     *
     * @param string $outputType desired output type
     */
    public function setOutputType($outputType);

    /**
     * Requests the HTML output to omit any HTML anchor.
     *
     * @param bool $removeLinks
     * @since 5.2
     */
    public function setRemoveLinks($removeLinks);
}
