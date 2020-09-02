<?php
namespace wcf\system\background\job;
use GuzzleHttp\Psr7\Request;
use wcf\data\style\Style;
use wcf\data\style\StyleEditor;
use wcf\system\io\HttpFactory;
use wcf\util\Url;

/**
 * Downloads the style's logo and stores it locally within the style's asset path.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Background\Job
 * @since	5.3
 * @deprecated	5.3 - This background job is used for the upgrade from 5.2 to 5.3.
 */
class DownloadStyleLogoJob extends AbstractBackgroundJob {
	/**
	 * @inheritDoc
	 */
	const MAX_FAILURES = 5;
	
	/**
	 * @var	int
	 */
	protected $styleID;
	
	public function __construct(Style $style) {
		$this->styleID = $style->styleID;
	}
	
	/**
	 * @return	int	every 10 minutes
	 */
	public function retryAfter() {
		return 10 * 60;
	}
	
	/**
	 * @inheritDoc
	 */
	public function perform() {
		$style = new Style($this->styleID);
		if (!$style->styleID) return;
		$styleEditor = new StyleEditor($style);
		
		$style->loadVariables();
		$variables = $style->getVariables();
		
		$http = HttpFactory::makeClient([
			'timeout' => 10,
		]);
		
		foreach (['pageLogo', 'pageLogoMobile'] as $type) {
			if ($variables[$type] && Url::is($variables[$type])) {
				$extension = pathinfo(Url::parse($variables[$type])['path'], PATHINFO_EXTENSION);
				
				if (in_array($extension, ['gif','png','jpg','jpeg','svg','webp'])) {
					$newLocation = $type . '.' . $extension;
					
					$http->send(new Request('GET', $variables[$type]), [
						'sink' => $style->getAssetPath() . $newLocation,
					]);
					
					$variables[$type] = $newLocation;
				}
				else {
					$variables[$type] = '';
				}
				
				$styleEditor->setVariables($variables);
			}
		}
		StyleEditor::resetCache();
	}
}
