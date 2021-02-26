<?php

namespace wcf\system\background\job;

use wcf\data\unfurl\url\UnfurlUrl;
use wcf\data\unfurl\url\UnfurlUrlAction;
use function wcf\functions\exception\logThrowable;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use wcf\util\UnfurlUrlUtil;

/**
 * Represents a background job to get information for an url.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Background\Job
 * @since       5.4
 */
class UnfurlURLJob extends AbstractBackgroundJob
{
    /**
     * @var UnfurlUrl
     */
    private $url;
    
    /**
     * UnfurlURLJob constructor.
     *
     * @param UnfurlUrl $url
     */
    public function __construct(UnfurlUrl $url)
    {
        $this->url = $url;
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
        try {
            $url = new UnfurlUrlUtil($this->url->url);
            
            if (empty(StringUtil::trim($url->getTitle()))) {
                $urlAction = new UnfurlUrlAction([$this->url], 'update', [
                    'data' => [
                        'title' => '',
                        'description' => '',
                        'status' => UnfurlUrl::STATUS_REJECTED,
                    ],
                ]);
                $urlAction->executeAction();
            } else {
                $title = StringUtil::truncate($url->getTitle(), 255);
                $description = $url->getDescription();
                $data = [
                    'title' => $title,
                    'description' => $description !== null ? StringUtil::truncate($description, 500) : '',
                    'status' => UnfurlUrl::STATUS_SUCCESSFUL,
                ];
                
                if ($url->getImageUrl()) {
                    $image = UnfurlUrlUtil::downloadImageFromUrl($url->getImageUrl());

                    if ($image !== null) {
                        $imageData = @\getimagesizefromstring($image);
                        
                        // filter images which are too large or too small
                        $isSquared = $imageData[0] === $imageData[1];
                        if ((!$isSquared && ($imageData[0] < 300 && $imageData[1] < 150))
                            || \min($imageData[0], $imageData[1]) < 50) {
                            $data['imageType'] = UnfurlUrl::IMAGE_NO_IMAGE;
                        } else {
                            if ($imageData[0] === $imageData[1]) {
                                $data['imageUrl'] = $url->getImageUrl();
                                $data['imageType'] = UnfurlUrl::IMAGE_SQUARED;
                            } else {
                                $data['imageUrl'] = $url->getImageUrl();
                                $data['imageType'] = UnfurlUrl::IMAGE_COVER;
                            }
                            
                            // Download image, if there is no image proxy or external source images allowed.
                            if (!(MODULE_IMAGE_PROXY || IMAGE_ALLOW_EXTERNAL_SOURCE)) {
                                if (isset($data['imageType'])) {
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
                                            throw new \RuntimeException();
                                    }
                                    
                                    $data['imageHash'] = \sha1($image) . '.' . $extension;
                                    
                                    $path = WCF_DIR . 'images/unfurlUrl/' . \substr($data['imageHash'], 0, 2);
                                    FileUtil::makePath($path);
                                    
                                    $fileLocation = $path . '/' . $data['imageHash'];
                                    
                                    \file_put_contents($fileLocation, $image);
                                    
                                    @\touch($fileLocation);
                                }
                            }
                        }
                    }
                }
                
                $urlAction = new UnfurlUrlAction([$this->url], 'update', [
                    'data' => $data,
                ]);
                $urlAction->executeAction();
            }
        } catch (\InvalidArgumentException $e) {
            logThrowable($e);
        }
    }
    
    /**
     * @inheritDoc
     */
    public function onFinalFailure()
    {
        $urlAction = new UnfurlUrlAction([$this->url], 'update', [
            'data' => [
                'title' => '',
                'description' => '',
                'status' => 'REJECTED',
            ],
        ]);
        $urlAction->executeAction();
    }
}
