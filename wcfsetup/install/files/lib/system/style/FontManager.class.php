<?php

namespace wcf\system\style;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientExceptionInterface;
use wcf\system\io\AtomicWriter;
use wcf\system\io\HttpFactory;
use wcf\system\SingletonFactory;
use wcf\system\style\exception\FontDownloadFailed;
use wcf\util\FileUtil;

/**
 * Manages webfont families.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Style
 * @since   5.3
 */
final class FontManager extends SingletonFactory
{
    private ClientInterface $http;

    private TreeMapper $mapper;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->http = HttpFactory::makeClient([
            'base_uri' => 'https://fonts.woltlab.com/',
            RequestOptions::TIMEOUT => 10,
        ]);

        $this->mapper = (new MapperBuilder())
            ->allowSuperfluousKeys()
            ->mapper();
    }

    /**
     * Returns the path to the family's CSS file.
     */
    public function getCssFilename(string $family): string
    {
        return WCF_DIR . 'font/families/' . $family . '/font.css';
    }

    /**
     * Returns whether the family's CSS file exists, implying that
     * the family is available.
     */
    public function isFamilyDownloaded(string $family): bool
    {
        return \is_readable($this->getCssFilename($family));
    }

    /**
     * Fetch the list of available families and returns it as an array.
     *
     * @return  string[]
     */
    public function fetchAvailableFamilies(): array
    {
        $request = new Request('GET', 'families.json');
        $response = $this->http->send($request);

        return $this->mapper->map(
            'list<string>',
            Source::json($response->getBody())
        );
    }

    /**
     * Downloads the given font family, stores it in font/families/<family> and
     * returns the decoded font manifest.
     *
     * @param string $family
     * @return  mixed[]
     */
    public function downloadFamily(string $family)
    {
        try {
            $request = new Request('GET', \rawurlencode($family) . '/manifest.json');
            $response = $this->http->send($request);
            $manifest = $this->mapper->map(
                <<<'EOT'
                    array {
                        name: string,
                        designer: string,
                        license: string,
                        license_text: string,
                        font_files: list<string>,
                        preload: list<string>,
                        css: string,
                    }
                    EOT,
                Source::json($response->getBody())
            );

            $familyDirectory = \dirname($this->getCssFilename($family));
            FileUtil::makePath($familyDirectory);

            $css = $manifest['css'];

            $preload = [];
            if (isset($manifest['preload']) && \is_array($manifest['preload'])) {
                $preload = $manifest['preload'];
            }

            $preloadRequests = "";
            foreach ($manifest['font_files'] as $filename) {
                if ($filename !== \basename($filename)) {
                    throw new \InvalidArgumentException("Invalid filename '" . $filename . "' given.");
                }

                $request = new Request('GET', \rawurlencode($family) . '/' . \rawurlencode($filename));
                $response = $this->http->send($request);

                $file = new AtomicWriter($familyDirectory . '/' . $filename);
                while (!$response->getBody()->eof()) {
                    $file->write($response->getBody()->read(8192));
                }
                $response->getBody()->close();
                $file->flush();
                $file->close();

                $css = \str_replace(
                    'url("' . $filename . '")',
                    \sprintf(
                        'url(getFont("%s", "%s", "%d"))',
                        \rawurlencode($filename),
                        \rawurlencode($family),
                        TIME_NOW
                    ),
                    $css
                );

                if (\in_array($filename, $preload)) {
                    $preloadRequests .= \sprintf(
                        <<<'EOT'
                            --woltlab-suite-preload: #{preload(
                                %s,
                                $as: "font",
                                $crossorigin: true
                            )};
                            EOT,
                        \sprintf(
                            'getFont("%s", "%s", "%d")',
                            \rawurlencode($filename),
                            \rawurlencode($family),
                            TIME_NOW
                        )
                    );
                }
            }

            $css .= "woltlab-suite-preload:root { {$preloadRequests} }";

            \file_put_contents($this->getCssFilename($family), $css);

            return $manifest;
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                throw new FontDownloadFailed("Unable to download font family '{$family}'.", 'notFound', $e);
            } else {
                throw new FontDownloadFailed("Unable to download font family '{$family}'.", '', $e);
            }
        } catch (ClientExceptionInterface $e) {
            throw new FontDownloadFailed("Unable to download font family '{$family}'.", '', $e);
        } catch (MappingError $e) {
            throw new FontDownloadFailed("Unable to parse the manifest for font family '{$family}'.", 'parsingFailed', $e);
        }
    }
}
