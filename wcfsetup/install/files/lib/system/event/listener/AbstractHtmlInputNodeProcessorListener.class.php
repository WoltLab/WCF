<?php
namespace wcf\system\event\listener;
use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeCache;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\ITitledObject;
use wcf\system\exception\ImplementationException;
use wcf\system\html\input\node\HtmlInputNodeProcessor;
use wcf\system\Regex;

/**
 * Provides useful methods for event listeners replacing simple text links in texts
 * with links with the name of the linked object or with bbcodes.
 * 
 * @author	Matthias Schmidt, Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Event\Listener
 * @since	3.0
 */
abstract class AbstractHtmlInputNodeProcessorListener implements IParameterizedEventListener {
	/**
	 * Returns the ids of the objects linked in the text processed by the given
	 * processor matching the given regular expression.
	 * 
	 * @param	HtmlInputNodeProcessor		$processor
	 * @param	Regex				$regex
	 * @return	integer[]
	 */
	protected function getObjectIDs(HtmlInputNodeProcessor $processor, Regex $regex) {
		$objectIDs = [];
		
		foreach ($processor->plainLinks as $link) {
			$objectID = $link->detectObjectID($regex);
			if ($objectID) {
				$objectIDs[] = $objectID;
			}
		}
		
		return array_unique($objectIDs);
	}
	
	/**
	 * Returns a `Regex` object for matching links based on the given string
	 * followed by an object id.
	 * 
	 * @param	string		$link
	 * @param       string          $defaultAnchor
	 * @return	Regex
	 */
	protected function getRegexFromLink($link, $defaultAnchor = '') {
		return new Regex('^(' . preg_replace('~^https?~', 'https?', preg_quote($link)) . '(\d+)-[^#]*?)' . ($defaultAnchor ? '(?:#' . $defaultAnchor . ')?' : '') . '$');
	}
	
	/**
	 * Replaces relevant object links with bbcodes.
	 * 
	 * @param	HtmlInputNodeProcessor		$processor
	 * @param	Regex				$regex
	 * @param	ITitledObject[]			$objects
	 * @param	string				$bbcodeName
	 * @deprecated 5.2 Use `replaceLinks()` instead.
	 */
	protected function replaceLinksWithBBCode(HtmlInputNodeProcessor $processor, Regex $regex, array $objects, $bbcodeName) {
		$this->replaceLinks($processor, $objects, $bbcodeName);
	}
	
	/**
	 * Replaces the text content of relevant object links with the titles of
	 * the objects.
	 * 
	 * @param	HtmlInputNodeProcessor		$processor
	 * @param	Regex				$regex
	 * @param	ITitledObject[]			$objects
	 * @throws	ImplementationException
	 * @deprecated 5.2 Use `replaceLinks()` instead.
	 */
	protected function setObjectTitles(HtmlInputNodeProcessor $processor, Regex $regex, array $objects) {
		$this->replaceLinks($processor, $objects);
	}
	
	/**
	 * @param HtmlInputNodeProcessor $processor
	 * @param ITitledObject[] $objects
	 * @param string $bbcodeName
	 */
	protected function replaceLinks(HtmlInputNodeProcessor $processor, array $objects, $bbcodeName = '') {
		$bbcode = null;
		if ($bbcodeName) {
			$bbcode = BBCodeCache::getInstance()->getBBCodeByTag($bbcodeName);
		}
		
		foreach ($processor->plainLinks as $link) {
			if (!$link->isPristine()) {
				continue;
			}
			
			if (isset($objects[$link->getObjectID()])) {
				if ($bbcode === null || !$link->isStandalone()) {
					$object = $objects[$link->getObjectID()];
					if ($object instanceof DatabaseObjectDecorator) {
						$object = $object->getDecoratedObject();
					}
					
					$link->setTitle($object);
				}
				else if ($bbcode !== null) {
					$link->replaceWithBBCode($bbcode);
				}
			}
		}
	}
}
