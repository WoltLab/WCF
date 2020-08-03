<?php
namespace wcf\system\style;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use wcf\system\io\AtomicWriter;
use wcf\system\io\HttpFactory;
use wcf\system\SingletonFactory;
use wcf\system\style\exception\FontDownloadFailed;
use wcf\util\FileUtil;
use wcf\util\JSON;

/**
 * Manages webfont families.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Style
 * @since	5.3
 */
class FontManager extends SingletonFactory {
	/**
	 * @var ClientInterface
	 */
	protected $http;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->http = HttpFactory::makeClient([
			'base_uri' => 'https://fonts.woltlab.com/'
		]);
	}
	
	/**
	 * Returns the path to the family's CSS file.
	 * 
	 * @return	string
	 */
	public function getCssFilename($family) {
		return WCF_DIR.'font/families/'.$family.'/font.css';
	}
	
	/**
	 * Returns whether the family's CSS file exists, implying that
	 * the family is available.
	 * 
	 * @return	boolean
	 */
	public function isFamilyDownloaded($family) {
		return is_readable($this->getCssFilename($family));
	}
	
	/**
	 * Fetch the list of available families and returns it as an array.
	 * 
	 * @return	string[]
	 */
	public function fetchAvailableFamilies() {
		$response = $this->http->send(new Request('GET', 'families.json'));
		return JSON::decode($response->getBody());
	}
	
	/**
	 * Downloads the given font family, stores it in font/families/<family> and
	 * returns the decoded font manifest.
	 * 
	 * @param	string	$family
	 * @return	mixed[]
	 */
	public function downloadFamily($family) {
		try {
			$response = $this->http->send(new Request('GET', $family.'/manifest.json'));
			$manifest = JSON::decode($response->getBody());
			
			$familyDirectory = dirname($this->getCssFilename($family));
			FileUtil::makePath($familyDirectory);
			
			$css = $manifest['css'];
			
			foreach ($manifest['font_files'] as $filename) {
				if ($filename !== basename($filename)) {
					throw new \InvalidArgumentException("Invalid filename '".$filename."' given.");
				}
				
				$response = $this->http->send(new Request('GET', $family.'/'.$filename), [
					// https://github.com/guzzle/guzzle/issues/2735
					'sink' => fopen("php://temp", "w+"),
				]);
				
				$file = new AtomicWriter($familyDirectory.'/'.$filename);
				while (!$response->getBody()->eof()) {
					$file->write($response->getBody()->read(8192));
				}
				$response->getBody()->close();
				$file->flush();
				$file->close();
				
				$css = str_replace('url("' . $filename . '")', 'url(getFont("'.rawurlencode($filename).'", "'.rawurlencode($family).'", "'.TIME_NOW.'"))', $css);
			}
			
			file_put_contents($this->getCssFilename($family), $css);
			
			return $manifest;
		}
		catch (ClientException $e) {
			if ($e->getResponse()->getStatusCode() == 404) {
				throw new FontDownloadFailed("Unable to download font family '".$family."'.", 'notFound', $e);
			}
			else {
				throw new FontDownloadFailed("Unable to download font family '".$family."'.", '', $e);
			}
		}		
		catch (RequestException $e) {
			throw new FontDownloadFailed("Unable to download font family '".$family."'.", '', $e);
		}
	}
}
