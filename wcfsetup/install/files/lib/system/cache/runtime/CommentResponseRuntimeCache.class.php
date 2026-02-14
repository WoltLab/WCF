<?php

namespace wcf\system\cache\runtime;

use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseList;

/**
 * Runtime cache implementation for comment responses.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @extends AbstractRuntimeCache<CommentResponse, CommentResponseList>
 */
class CommentResponseRuntimeCache extends AbstractRuntimeCache
{
    /**
     * @inheritDoc
     */
    protected $listClassName = CommentResponseList::class;

    /**
     * @since 6.2
     */
    public function cacheResponse(CommentResponse $response): void
    {
        $this->objects[$response->responseID] = $response;
    }
}
