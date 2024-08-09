<?php

namespace wcf\util\exception;

use wcf\system\exception\IExtraInformationException;
use wcf\system\exception\SystemException;
use wcf\util\HTTPRequest;
use wcf\util\StringUtil;

/**
 * Denotes failure to perform a HTTP request.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 * @deprecated  5.3 This exception is intimately tied to HTTPRequest which is deprecated. Will be removed with 7.0.
 */
class HTTPException extends SystemException implements IExtraInformationException
{
    /**
     * The HTTP request that lead to this Exception.
     *
     * @param HTTPRequest
     */
    protected $http;

    /**
     * @inheritDoc
     */
    public function __construct(HTTPRequest $http, $message, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, '', $previous);

        $this->http = $http;
    }

    /**
     * @inheritDoc
     */
    public function getExtraInformation()
    {
        $reply = $this->http->getReply();
        $body = StringUtil::truncate(
            \preg_replace('/[\x00-\x1F\x80-\xFF]/', '.', $reply['body']),
            2048,
            StringUtil::HELLIP,
            true
        );

        return [
            ['Body', $body],
            ['Status Code', $reply['statusCode']],
        ];
    }
}
