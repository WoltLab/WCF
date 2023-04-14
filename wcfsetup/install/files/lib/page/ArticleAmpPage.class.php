<?php

namespace wcf\page;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\system\request\LinkHandler;

/**
 * Former page for the AMP version of articles. AMP support was removed with version 6.0.
 * For backward compatibility of links, this page redirects to the normal article page.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 * @deprecated 5.5 Support for AMP was removed in 6.0
 */
class ArticleAmpPage extends AbstractArticlePage
{
    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        return new RedirectResponse(
            LinkHandler::getInstance()->getControllerLink(ArticlePage::class, ['object' => $this->articleContent]),
            301
        );
    }
}
