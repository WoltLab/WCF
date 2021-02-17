<?php

namespace wcf\data\user\avatar;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use wcf\system\io\HttpFactory;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a gravatar.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\User\Avatar
 * @see     http://www.gravatar.com
 * @deprecated 5.4 Gravatar is questionable from a privacy perspective.
 */
class Gravatar extends DefaultAvatar
{
    /**
     * gravatar base url
     * @var string
     */
    const GRAVATAR_BASE = 'http://gravatar.com/avatar/%s?s=%d&r=g&d=%s';

    /**
     * gravatar local cache location
     * @var string
     */
    const GRAVATAR_CACHE_LOCATION = 'images/avatars/gravatars/%s-%s.%s';

    /**
     * gravatar expire time (days)
     * @var int
     */
    const GRAVATAR_CACHE_EXPIRE = 7;

    /**
     * user id
     * @var int
     */
    public $userID = 0;

    /**
     * gravatar e-mail address
     * @var string
     */
    public $gravatar = '';

    /**
     * file extension of the gravatar image
     * @var string
     */
    public $fileExtension = 'png';

    /**
     * urls of this gravatar
     * @var string
     */
    protected $url = '';

    /**
     * Creates a new Gravatar object.
     *
     * @param int $userID
     * @param string $gravatar
     * @param string $fileExtension
     */
    public function __construct($userID, $gravatar, $fileExtension = 'png')
    {
        $this->userID = $userID;
        $this->gravatar = $gravatar;
        $this->fileExtension = $fileExtension;
    }

    /**
     * @inheritDoc
     */
    public function getURL($size = null)
    {
        if (empty($this->url)) {
            // try to use cached gravatar
            $cachedFilename = \sprintf(
                self::GRAVATAR_CACHE_LOCATION,
                \md5(\mb_strtolower($this->gravatar)),
                $this->size,
                $this->fileExtension
            );
            if (\file_exists(WCF_DIR . $cachedFilename) && \filemtime(WCF_DIR . $cachedFilename) > (TIME_NOW - (self::GRAVATAR_CACHE_EXPIRE * 86400))) {
                $this->url = WCF::getPath() . $cachedFilename;
            } else {
                $this->url = LinkHandler::getInstance()->getLink('GravatarDownload', [
                    'forceFrontend' => true,
                ], 'userID=' . $this->userID);
            }
        }

        return $this->url;
    }

    /**
     * @inheritDoc
     */
    public function getSafeURL(?int $size = null): string
    {
        return $this->getURL($size);
    }

    /**
     * Checks a given email address for gravatar support.
     *
     * @param string $email
     * @return  bool
     */
    public static function test($email)
    {
        $gravatarURL = \sprintf(self::GRAVATAR_BASE, \md5(\mb_strtolower($email)), 80, GRAVATAR_DEFAULT_TYPE);
        $client = HttpFactory::getDefaultClient();
        $request = new Request('GET', $gravatarURL);

        try {
            $response = $client->send($request);

            if ($response->getStatusCode() === 200) {
                return true;
            }
        } catch (ClientExceptionInterface $e) {
            // Ignore exception, because we return false anyways.
        }

        return false;
    }
}
