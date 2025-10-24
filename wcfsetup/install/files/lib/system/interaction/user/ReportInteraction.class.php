<?php

namespace wcf\system\interaction\user;

use wcf\data\DatabaseObject;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Presents an interaction that allows a user to report content to a moderator.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class ReportInteraction extends AbstractInteraction
{
    public function __construct(
        protected readonly string $objectType,
        protected readonly string|\Closure $languageItem = 'wcf.moderation.report.reportContent',
        ?\Closure $isAvailableCallback = null
    ) {
        parent::__construct('report', $isAvailableCallback);
    }

    #[\Override]
    public function isAvailable(DatabaseObject $object): bool
    {
        if ($this->isAvailableCallback === null) {
            return !!WCF::getSession()->getPermission('user.profile.canReportContent');
        }

        return parent::isAvailable($object);
    }

    #[\Override]
    public function render(DatabaseObject $object): string
    {
        $objectType = StringUtil::encodeHTML($this->objectType);
        $objectID = StringUtil::encodeHTML($object->getObjectID());
        if (\is_string($this->languageItem)) {
            $label = WCF::getLanguage()->get($this->languageItem);
        } else {
            $label = ($this->languageItem)($object);
        }

        return <<<HTML
            <button
                type="button"
                data-report-content="{$objectType}"
                data-object-id="{$objectID}"
            >
                {$label}
            </button>
            HTML;
    }
}
