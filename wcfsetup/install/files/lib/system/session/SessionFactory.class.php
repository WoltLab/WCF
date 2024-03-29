<?php

namespace wcf\system\session;

use wcf\data\session\SessionEditor;

/**
 * Handles the session of the active user.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SessionFactory extends ACPSessionFactory
{
    /**
     * @inheritDoc
     */
    protected $cookieSuffix = 'user_';

    /**
     * @inheritDoc
     */
    protected $sessionEditor = SessionEditor::class;
}
