<?php

namespace wcf\util;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use wcf\system\io\HttpFactory;

/**
 * Helper class to unfurl specific urls.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Util
 * @since       5.4
 */
final class UnfurlUrlUtil
{
    /**
     * 10 Mebibyte
     */
    private const MAX_SIZE = (10 * (1 << 20));

    /**
     * 3 Mebibyte
     */
    private const MAX_IMAGE_SIZE = (3 * (1 << 20));

    /**
     * @var string
     */
    private $url;
    
    /**
     * @var string
     */
    private $body;
    
    /**
     * @var \DOMDocument
     */
    private $domDocument;
    
    public function __construct(string $url)
    {
        if (!Url::is($url)) {
            throw new \InvalidArgumentException('Given URL "' . $url . '" is not a valid URL.');
        }
        
        $this->url = $url;
        
        $this->fetchUrl();
    }
    
    /**
     * Fetches the body of the given url and converts the body to utf-8.
     */
    private function fetchUrl(): void
    {
        try {
            $client = HttpFactory::makeClient([
                RequestOptions::TIMEOUT => 10,
                RequestOptions::STREAM => true,
            ]);
            $request = new Request('GET', $this->url, [
                'range' => \sprintf('bytes=%d-%d', 0, self::MAX_SIZE - 1),
            ]);
            $response = $client->send($request);
            
            $this->body = "";
            while (!$response->getBody()->eof()) {
                $this->body .= $response->getBody()->read(8192);

                if ($response->getBody()->tell() >= self::MAX_SIZE) {
                    break;
                }
            }
            $response->getBody()->close();
            
            if (\mb_detect_encoding($this->body) !== 'UTF-8') {
                $this->body = StringUtil::convertEncoding(\mb_detect_encoding($this->body), 'UTF-8', $this->body);
            }
        } catch (TooManyRedirectsException | BadResponseException | TransferException $e) {
            // Ignore these exceptions.
        }
    }
    
    /**
     * Returns the dom document of the website.
     */
    private function getDomDocument(): \DOMDocument
    {
        if ($this->domDocument === null) {
            \libxml_use_internal_errors(true);
            $this->domDocument = new \DOMDocument();
            $this->domDocument->loadHTML('<?xml version="1.0" encoding="UTF-8"?>' . $this->body);
        }
        
        return $this->domDocument;
    }
    
    /**
     * Determines the title of the website.
     */
    public function getTitle(): ?string
    {
        if (!empty($this->body)) {
            $metaTags = $this->getDomDocument()->getElementsByTagName('meta');
            
            // og
            foreach ($metaTags as $metaTag) {
                foreach ($metaTag->attributes as $attr) {
                    if ($attr->nodeName == 'property' && $attr->value == 'og:title') {
                        foreach ($attr->parentNode->attributes as $attr) {
                            if ($attr->nodeName == 'content') {
                                return $attr->value;
                            }
                        }
                    }
                }
            }
            
            // title tag
            $title = $this->getDomDocument()->getElementsByTagName('title');
            if ($title->length) {
                return $title->item(0)->nodeValue;
            }
        }

        return null;
    }
    
    /**
     * Determines the description of the website.
     */
    public function getDescription(): ?string
    {
        if (!empty($this->body)) {
            $metaTags = $this->getDomDocument()->getElementsByTagName('meta');

            // og:description
            foreach ($metaTags as $metaTag) {
                foreach ($metaTag->attributes as $attr) {
                    if ($attr->nodeName == 'property' && $attr->value == 'og:description') {
                        foreach ($attr->parentNode->attributes as $attr) {
                            if ($attr->nodeName == 'content') {
                                return $attr->value;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }
    
    /**
     * Returns the image url for the current url.
     */
    public function getImageUrl(): ?string
    {
        if (!empty($this->body)) {
            $metaTags = $this->getDomDocument()->getElementsByTagName('meta');
            
            // og:image
            foreach ($metaTags as $metaTag) {
                foreach ($metaTag->attributes as $attr) {
                    if ($attr->nodeName == 'property' && $attr->value == 'og:image') {
                        foreach ($attr->parentNode->attributes as $attr) {
                            if ($attr->nodeName == 'content') {
                                return $attr->value;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }
    
    /**
     * Downloads the image from a url and returns the image body.
     */
    public static function downloadImageFromUrl(string $url): ?string
    {
        try {
            // Rewrite schemaless URLs to https.
            $scheme = \parse_url($url, \PHP_URL_SCHEME);
            if (!$scheme) {
                if (StringUtil::startsWith($url, '//')) {
                    $url = 'https:' . $url;
                } else {
                    throw new \DomainException();
                }
            }

            // download image
            try {
                $client = HttpFactory::makeClient([
                    RequestOptions::TIMEOUT => 10,
                    RequestOptions::STREAM => true,
                ]);
                $request = new Request('GET', $url, [
                    'via' => '1.1 wsc',
                    'accept' => 'image/*',
                    'range' => 'bytes=0-' . (self::MAX_IMAGE_SIZE - 1),
                ]);
                $response = $client->send($request);

                $image = "";
                while (!$response->getBody()->eof()) {
                    $image .= $response->getBody()->read(8192);

                    if ($response->getBody()->tell() >= self::MAX_IMAGE_SIZE) {
                        break;
                    }
                }
                $response->getBody()->close();
            } catch (TransferException $e) {
                throw new \DomainException('Failed to request', 0, $e);
            }

            // check file type
            $imageData = @\getimagesizefromstring($image);
            if (!$imageData) {
                throw new \DomainException();
            }

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
                    throw new \DomainException();
            }

            return $image;
        } catch (\DomainException $e) {
            return null;
        }
    }
}
