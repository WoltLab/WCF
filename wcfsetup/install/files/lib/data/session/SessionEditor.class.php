<?php

namespace wcf\data\session;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit legacy sessions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   Session
 * @extends DatabaseObjectEditor<Session>
 */
class SessionEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Session::class;

    #[\Override]
    public static function create(array $parameters = []): Session
    {
        if (isset($parameters['userID']) && $parameters['userID'] === 0) {
            $parameters['userID'] = null;
        }

        return parent::create($parameters);
    }

    #[\Override]
    public function update(array $parameters = []): void
    {
        if (isset($parameters['userID']) && $parameters['userID'] === 0) {
            $parameters['userID'] = null;
        }

        parent::update($parameters);
    }
}
