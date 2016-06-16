<?php
namespace wcf\system\email\mime;
use wcf\data\style\ActiveStyle;
use wcf\system\cache\builder\StyleCacheBuilder;
use wcf\system\email\Mailbox;
use wcf\system\template\EmailTemplateEngine;
use wcf\system\WCF;

/**
 * Default implementation of a recipient aware TextMimePart.
 * 
 * This implementation generates the final content by passing the content
 * to a specified template. The language will be changed to the Mailbox' language,
 * before evaluating the template.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email\Mime
 * @since	3.0
 */
class RecipientAwareTextMimePart extends TextMimePart implements IRecipientAwareMimePart {
	/**
	 * template to use for this email
	 * @var	string
	 */
	protected $template = '';
	
	/**
	 * application of this template
	 * @var	string
	 */
	protected $application = 'wcf';
	
	/**
	 * the recipient of the email containing this mime part
	 * @var	\wcf\system\email\Mailbox
	 */
	protected $mailbox = null;
	
	/**
	 * Creates a new AbstractRecipientAwareTextMimePart.
	 * 
	 * @param	string	$mimeType	Mime type to provide in the email. You *must* not provide a charset. UTF-8 will be used automatically.
	 * @param	string	$template	Template to evaluate
	 * @param	string	$application	Application of the template to evaluate (default: wcf)
	 * @param	string	$content	Content of this text part (this is passed to the template).
	 */
	public function __construct($mimeType, $template, $application = 'wcf', $content = '') {
		parent::__construct($content, $mimeType);
		
		$this->template = $template;
		$this->application = $application;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setRecipient(Mailbox $mailbox = null) {
		$this->mailbox = $mailbox;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContent() {
		$language = WCF::getLanguage();
		
		try {
			if ($this->mailbox) WCF::setLanguage($this->mailbox->getLanguage()->languageID);
			
			$result = EmailTemplateEngine::getInstance()->fetch($this->template, $this->application, $this->getTemplateVariables(), true);
			
			if ($this->mimeType === 'text/html') {
				$emogrifier = new \Pelago\Emogrifier();
				$emogrifier->disableInvisibleNodeRemoval();
				
				$emogrifier->setHtml($result);
				
				$result = $emogrifier->emogrify();
			}
			else if ($this->mimeType === 'text/plain') {
				$counter = 1;
				$urls = [];
				$result = preg_replace_callback('~\[URL:(https?://[^\]\s]*)\]~', function ($matches) use (&$counter, &$urls) {
					if (!isset($urls[$matches[1]])) {
						$urls[$matches[1]] = $counter++;
					}
					
					return '['.$urls[$matches[1]].']';
				}, $result);
				$result = preg_replace_callback('/(\r?\n-- \r?\n|$)/', function ($matches) use ($urls) {
					$list = '';
					foreach ($urls as $url => $number) {
						$list .= "\r\n[".$number."] ".$url;
					}
					return $list."\r\n".$matches[0];
				}, $result, 1);
			}
			
			return $result;
		}
		finally {
			WCF::setLanguage($language->languageID);
		}
	}
	
	/**
	 * Returns the templates variables to be passed to the EmailTemplateEngine.
	 * 
	 * @return	mixed[]
	 */
	protected function getTemplateVariables() {
		$styleCache = StyleCacheBuilder::getInstance()->getData();
		
		return [
			'content' => $this->content,
			'mimeType' => $this->mimeType,
			'mailbox' => $this->mailbox,
			'style' => new ActiveStyle($styleCache['styles'][$styleCache['default']])
		];
	}
}
