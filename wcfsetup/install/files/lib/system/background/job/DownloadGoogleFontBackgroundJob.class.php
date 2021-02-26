<?php

namespace wcf\system\background\job;

use wcf\system\style\FontManager;

/**
 * Downloads a Google Font family.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Background\Job
 * @since   5.4
 */
final class DownloadGoogleFontBackgroundJob extends AbstractBackgroundJob
{
    /**
     * @inheritDoc
     */
    const MAX_FAILURES = 5;

    /**
     * @var string
     */
    protected $family;

    public function __construct(string $family)
    {
        $this->family = $family;
    }

    /**
     * @return  int every 10 minutes
     */
    public function retryAfter()
    {
        return 10 * 60;
    }

    /**
     * @inheritDoc
     */
    public function perform()
    {
        FontManager::getInstance()->downloadFamily($this->family);
    }
}
