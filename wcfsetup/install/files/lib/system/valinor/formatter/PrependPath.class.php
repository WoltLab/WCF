<?php

namespace wcf\system\valinor\formatter;

use CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageFormatter;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;

/**
 * Prepends the message's path to the message body.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class PrependPath implements MessageFormatter
{
    /**
     * @inheritDoc
     */
    public function format(NodeMessage $m): NodeMessage
    {
        return $m->withBody("{$m->node()->path()}: {$m->body()}");
    }
}
