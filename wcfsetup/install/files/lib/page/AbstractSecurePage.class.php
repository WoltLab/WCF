<?php

namespace wcf\page;

use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * @deprecated 5.5 Pages / GET requests must not modify server state and thus no XSRF protection is required. Use a form instead.
 */
abstract class AbstractSecurePage extends AbstractPage
{
    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        // check security token
        $this->checkSecurityToken();
    }

    /**
     * Validates the security token.
     *
     * @return void
     */
    protected function checkSecurityToken()
    {
        if (!isset($_REQUEST['t']) || !WCF::getSession()->checkSecurityToken($_REQUEST['t'])) {
            throw new IllegalLinkException();
        }
    }
}
