<?php

namespace wcf\action;

use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\system\io\File;
use wcf\system\io\HttpFactory;
use wcf\system\WCF;
use wcf\util\CryptoUtil;
use wcf\util\exception\CryptoException;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Proxies requests for embedded images.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Action
 * @since   3.0
 */
class ImageProxyAction extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_IMAGE_PROXY'];

    /**
     * The image key created by CryptoUtil::createSignedString()
     * @var string
     */
    public $key = '';

    /**
     * 10 Mebibyte
     */
    const MAX_SIZE = (10 * (1 << 20));

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['key'])) {
            $this->key = StringUtil::trim($_REQUEST['key']);
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        if (isset($_SERVER['HTTP_VIA']) && \strpos($_SERVER['HTTP_VIA'], 'wsc') !== false) {
            throw new IllegalLinkException();
        }

        try {
            $url = CryptoUtil::getValueFromSignedString($this->key);
            if ($url === null) {
                throw new IllegalLinkException();
            }

            $fileName = \sha1($this->key);
            $dir = WCF_DIR . 'images/proxy/' . \substr($fileName, 0, 2);

            // ensure that the directory exists
            if (!\file_exists($dir)) {
                FileUtil::makePath($dir);
            }

            // check whether we already downloaded the image
            $fileLocation = null;
            foreach (['png', 'jpg', 'gif'] as $extension) {
                if (\is_file($dir . '/' . $fileName . '.' . $extension)) {
                    $fileLocation = $dir . '/' . $fileName . '.' . $extension;
                    break;
                }
            }

            if ($fileLocation === null) {
                $tmp = FileUtil::getTemporaryFilename('image_proxy_');

                try {
                    // rewrite schemaless URLs to https
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
                            'timeout' => 10,
                        ]);
                        $request = new Request('GET', $url, [
                            'via' => '1.1 wsc',
                            'accept' => 'image/*',
                            'range' => 'bytes=0-' . (self::MAX_SIZE - 1),
                        ]);
                        $response = $client->send($request);

                        $file = new File($tmp);
                        while (!$response->getBody()->eof()) {
                            $file->write($response->getBody()->read(8192));

                            if ($response->getBody()->tell() >= self::MAX_SIZE) {
                                break;
                            }
                        }
                        $response->getBody()->close();
                        $file->flush();
                        $file->close();
                    } catch (TransferException $e) {
                        throw new \DomainException('Failed to request', 0, $e);
                    }

                    // check file type
                    $imageData = @\getimagesize($tmp);
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
                } catch (\DomainException $e) {
                    // save a dummy image in case the server sent us junk, otherwise we might try to download the file over and over and over again.
                    // taken from the public domain gif at https://commons.wikimedia.org/wiki/File%3aBlank.gif
                    \file_put_contents(
                        $tmp,
                        "\x47\x49\x46\x38\x39\x61\x01\x00\x01\x00\x80\x00\x00\xFF\xFF\xFF\x00\x00\x00\x21\xF9\x04\x00\x00\x00\x00\x00\x2C\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02\x44\x01\x00\x3B"
                    );
                    $extension = 'gif';
                }

                $fileLocation = $dir . '/' . $fileName . '.' . $extension;

                \rename($tmp, $fileLocation);

                // update mtime for correct expiration calculation
                @\touch($fileLocation);
            }

            $path = FileUtil::getRelativePath(WCF_DIR, \dirname($fileLocation)) . \basename($fileLocation);

            $this->executed();

            HeaderUtil::redirect(WCF::getPath() . $path, true, false);

            exit;
        } catch (SystemException $e) {
            \wcf\functions\exception\logThrowable($e);
            throw new IllegalLinkException();
        } catch (CryptoException $e) {
            \wcf\functions\exception\logThrowable($e);
            throw new IllegalLinkException();
        }
    }
}
