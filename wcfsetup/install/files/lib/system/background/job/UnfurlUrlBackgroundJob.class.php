<?php

namespace wcf\system\background\job;

use BadMethodCallException;
use GuzzleHttp\Psr7\Response;
use wcf\data\unfurl\url\UnfurlUrl;
use wcf\data\unfurl\url\UnfurlUrlAction;
use wcf\system\message\unfurl\exception\DownloadFailed;
use wcf\system\message\unfurl\exception\ParsingFailed;
use wcf\system\message\unfurl\exception\UrlInaccessible;
use wcf\system\message\unfurl\UnfurlResponse;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Represents a background job to get information for an url.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Background\Job
 * @since       5.4
 */
final class UnfurlUrlBackgroundJob extends AbstractBackgroundJob
{
    /**
     * @var int
     */
    private $urlID;

    /**
     * UnfurlURLJob constructor.
     *
     * @param UnfurlUrl $url
     */
    public function __construct(UnfurlUrl $url)
    {
        $this->urlID = $url->urlID;
    }

    /**
     * @inheritDoc
     */
    public function retryAfter()
    {
        switch ($this->getFailures()) {
            case 1:
                // 5 minutes
                return 5 * 60;
            case 2:
                // 30 minutes
                return 30 * 60;
            case 3:
                // 2 hours
                return 2 * 60 * 60;
        }
    }

    /**
     * @inheritDoc
     */
    public function perform()
    {
        $unfurlUrl = new UnfurlUrl($this->urlID);

        try {
            $unfurlResponse = UnfurlResponse::fetch($unfurlUrl->url);

            if (empty(StringUtil::trim($unfurlResponse->getTitle()))) {
                $this->save(UnfurlUrl::STATUS_REJECTED);
                return;
            }

            $title = StringUtil::truncate($unfurlResponse->getTitle(), 255);
            $description = "";
            if ($unfurlResponse->getDescription()) {
                $description = StringUtil::truncate($unfurlResponse->getDescription());
            }

            if ($unfurlResponse->getImageUrl()) {
                try {
                    $image = $this->downloadImage($unfurlResponse->getImage());
                    $imageData = \getimagesizefromstring($image);
                    if ($imageData !== false) {
                        $imageType = $this->validateImage($imageData);
                        if (!(MODULE_IMAGE_PROXY || IMAGE_ALLOW_EXTERNAL_SOURCE)) {
                            $imageHash = $this->saveImage($imageData, $image);
                        } else {
                            $imageHash = "";
                        }
                    } else {
                        $imageType = UnfurlUrl::IMAGE_NO_IMAGE;
                    }

                    if ($imageType === UnfurlUrl::IMAGE_NO_IMAGE) {
                        $imageUrl = $imageHash = "";
                    } else {
                        $imageUrl = $unfurlResponse->getImageUrl();
                    }
                } catch (UrlInaccessible | DownloadFailed $e) {
                    $imageType = UnfurlUrl::IMAGE_NO_IMAGE;
                    $imageUrl = $imageHash = "";
                }
            } else {
                $imageType = UnfurlUrl::IMAGE_NO_IMAGE;
                $imageUrl = $imageHash = "";
            }

            $this->save(
                UnfurlUrl::STATUS_SUCCESSFUL,
                $title,
                $description,
                $imageType,
                $imageUrl,
                $imageHash
            );
        } catch (UrlInaccessible | ParsingFailed $e) {
            if (\ENABLE_DEBUG_MODE) {
                \wcf\functions\exception\logThrowable($e);
            }

            $this->save(UnfurlUrl::STATUS_REJECTED);
        }
    }

    private function downloadImage(Response $imageResponse): string
    {
        $image = "";
        while (!$imageResponse->getBody()->eof()) {
            $image .= $imageResponse->getBody()->read(8192);

            if ($imageResponse->getBody()->tell() >= UnfurlResponse::MAX_IMAGE_SIZE) {
                break;
            }
        }
        $imageResponse->getBody()->close();

        return $image;
    }

    private function validateImage(array $imageData): string
    {
        $isSquared = $imageData[0] === $imageData[1];
        if (
            (!$isSquared && ($imageData[0] < 300 && $imageData[1] < 150))
            || \min($imageData[0], $imageData[1]) < 50
        ) {
            return UnfurlUrl::IMAGE_NO_IMAGE;
        } else {
            if ($isSquared) {
                return UnfurlUrl::IMAGE_SQUARED;
            } else {
                return UnfurlUrl::IMAGE_COVER;
            }
        }
    }

    private function saveImage(array $imageData, string $image): string
    {
        switch ($imageData[2]) {
            case \IMAGETYPE_PNG:
                $extension = 'png';
                break;
            case \IMAGETYPE_GIF:
                $extension = 'gif';
                break;
            case \IMAGETYPE_JPEG:
                $extension = 'jpg';
                break;

            default:
                throw new DownloadFailed();
        }

        $imageHash = sha1($image);

        $path = WCF_DIR . 'images/unfurlUrl/' . \substr($imageHash, 0, 2);
        FileUtil::makePath($path);

        $fileLocation = $path . '/' . $imageHash . '.' . $extension;

        \file_put_contents($fileLocation, $image);

        @\touch($fileLocation);

        return $imageHash . '.' . $extension;
    }

    private function save(
        string $status,
        string $title = "",
        string $description = "",
        string $imageType = UnfurlUrl::IMAGE_NO_IMAGE,
        string $imageUrl = "",
        string $imageHash = ""
    ): void {
        switch ($status) {
            case UnfurlUrl::STATUS_PENDING:
            case UnfurlUrl::STATUS_REJECTED:
            case UnfurlUrl::STATUS_SUCCESSFUL:
                break;

            default:
                throw new BadMethodCallException("Invalid status '{$status}' given.");
        }

        switch ($imageType) {
            case UnfurlUrl::IMAGE_COVER:
            case UnfurlUrl::IMAGE_NO_IMAGE:
            case UnfurlUrl::IMAGE_SQUARED:
                break;

            default:
                throw new BadMethodCallException("Invalid imageType '{$imageType}' given.");
        }

        $urlAction = new UnfurlUrlAction([$this->urlID], 'update', [
            'data' => [
                'status' => $status,
                'title' => $title,
                'description' => $description,
                'imageType' => $imageType,
                'imageUrl' => $imageUrl,
                'imageHash' => $imageHash,
                'lastFetch' => TIME_NOW,
            ],
        ]);
        $urlAction->executeAction();
    }

    /**
     * @inheritDoc
     */
    public function onFinalFailure()
    {
        $this->save(UnfurlUrl::STATUS_REJECTED);
    }
}
