<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeList;
use wcf\event\interaction\bulk\admin\ReactionTypeBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\interaction\bulk\BulkRpcInteraction;
use wcf\system\interaction\InteractionConfirmationType;

/**
 * Bulk interaction provider for reaction types.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class ReactionTypeBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new BulkDeleteInteraction('core/reactions/types/%s'),
            new BulkRpcInteraction(
                'assignable',
                'core/reactions/types/%s/enable',
                'wcf.acp.reactionType.isAssignable',
                InteractionConfirmationType::None,
                '',
                function (ReactionType $reactionType): bool {
                    return !$reactionType->isAssignable;
                }
            ),
            new BulkRpcInteraction(
                'unset-assignable',
                'core/reactions/types/%s/disable',
                'wcf.acp.reactionType.isNotAssignable',
                InteractionConfirmationType::None,
                '',
                function (ReactionType $reactionType): bool {
                    return !!$reactionType->isAssignable;
                }
            ),
        ]);

        EventHandler::getInstance()->fire(
            new ReactionTypeBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return ReactionTypeList::class;
    }
}
