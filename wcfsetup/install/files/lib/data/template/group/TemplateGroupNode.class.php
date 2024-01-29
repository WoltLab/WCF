<?php

namespace wcf\data\template\group;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\IObjectTreeNode;
use wcf\data\TObjectTreeNode;

/**
 * Represents a template group node.
 *
 * @author  Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  TemplateGroup    getDecoratedObject()
 * @mixin   TemplateGroup
 */
final class TemplateGroupNode extends DatabaseObjectDecorator implements IObjectTreeNode
{
    use TObjectTreeNode;

    /**
     * @inheritDoc
     */
    protected static $baseClass = TemplateGroup::class;
}
