<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\object\filter\AbstractBooleanObjectFilter;
use wcf\system\object\filter\IObjectListFilter;

/**
 * Filters users by whether their account has been activated. Guests never match.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractBooleanObjectFilter<User>
 * @implements IObjectListFilter<User, bool>
 */
final class UserActivatedObjectFilter extends AbstractBooleanObjectFilter implements IObjectListFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userActivated';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.user.activated';
    }

    #[\Override]
    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void
    {
        if ($value) {
            $conditions->add('activationCode = 0');
        } else {
            $conditions->add('activationCode <> 0');
        }
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        if ($object->isGuest()) {
            return false;
        }

        return $configuredValue === !$object->pendingActivation();
    }
}
