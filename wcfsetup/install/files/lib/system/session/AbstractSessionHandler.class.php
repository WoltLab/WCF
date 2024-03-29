<?php

namespace wcf\system\session;

use wcf\system\SingletonFactory;

/**
 * Abstract implementation for application-specific session handlers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractSessionHandler extends SingletonFactory
{
    /**
     * SessionHandler object
     * @var SessionHandler
     */
    protected $sessionHandler;

    /**
     * @inheritDoc
     */
    final protected function init()
    {
        $this->sessionHandler = SessionHandler::getInstance();

        // initialize session
        $this->initSession();
    }

    /**
     * Forwards calls on unknown properties to stored SessionHandler
     *
     * @param string $key
     * @return  mixed
     */
    public function __get($key)
    {
        return $this->sessionHandler->{$key};
    }

    /**
     * Initializes this session.
     */
    abstract protected function initSession();
}
