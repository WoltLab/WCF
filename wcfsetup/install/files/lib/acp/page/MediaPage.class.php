<?php

namespace wcf\acp\page;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\page\AbstractPage;
use wcf\system\request\LinkHandler;

/**
 * Redirect all media requests to the frontend.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class MediaPage extends AbstractPage
{
    #[\Override]
    public function readParameters()
    {
        parent::readParameters();
        $args = [
            'forceFrontend' => true,
        ];
        // Sending the original request parameters to the frontend.
        // We do not check or change the request parameters to leak the data from a generated media object
        if (isset($_REQUEST['id'])) {
            $args['id'] = $_REQUEST['id'];
        }
        if (isset($_REQUEST['title'])) {
            $args['title'] = $_REQUEST['title'];
        }
        if (isset($_REQUEST['thumbnail'])) {
            $args['thumbnail'] = $_REQUEST['thumbnail'];
        }

        return new RedirectResponse(
            LinkHandler::getInstance()->getControllerLink(\wcf\page\MediaPage::class, $args),
            302
        );
    }
}
