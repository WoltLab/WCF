<?php

namespace wcf\system\message\quote;

use wcf\data\IMessage;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Manages message quotes.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Message\Quote
 */
class MessageQuoteManager extends SingletonFactory
{
    /**
     * current object ids
     * @var int[]
     */
    protected $objectIDs = [];

    /**
     * current object type name
     * @var string
     */
    protected $objectType = '';

    /**
     * list of object types
     * @var ObjectType[]
     */
    protected $objectTypes = [];

    /**
     * list of stored quotes
     * @var mixed[][]
     */
    protected $quotes = [];

    /**
     * list of quote messages by quote id
     * @var array
     */
    protected $quoteData = [];

    /**
     * message id for quoting
     * @var int
     */
    protected $quoteMessageID = 0;

    /**
     * list of quote ids to be removed
     * @var string[]
     */
    protected $removeQuoteIDs = [];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        // load stored quotes from session
        $messageQuotes = WCF::getSession()->getVar('__messageQuotes');
        if (\is_array($messageQuotes)) {
            $this->quotes = $messageQuotes['quotes'] ?? [];
            $this->quoteData = $messageQuotes['quoteData'] ?? [];
            $this->removeQuoteIDs = $messageQuotes['removeQuoteIDs'] ?? [];
        }

        // load object types
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.message.quote');
        foreach ($objectTypes as $objectType) {
            $this->objectTypes[$objectType->objectType] = $objectType;
        }
    }

    /**
     * Adds a quote unless it is already stored. If you want to quote a whole
     * message while maintaining the original markup, pass $obj->getExcerpt() for
     * $message and $obj->getMessage() for $fullQuote.
     *
     * @param string $objectType
     * @param int $parentObjectID
     * @param int $objectID
     * @param string $message
     * @param string $fullQuote
     * @param bool $returnFalseIfExists
     * @return  mixed
     * @throws  SystemException
     */
    public function addQuote(
        $objectType,
        $parentObjectID,
        $objectID,
        $message,
        $fullQuote = '',
        $returnFalseIfExists = true
    ) {
        if (!isset($this->objectTypes[$objectType])) {
            throw new SystemException("Object type '" . $objectType . "' is unknown");
        }

        if (!isset($this->quotes[$objectType])) {
            $this->quotes[$objectType] = [];
        }

        if (!isset($this->quotes[$objectType][$objectID])) {
            $this->quotes[$objectType][$objectID] = [];
        }

        $quoteID = $this->getQuoteID($objectType, $objectID, $message, $fullQuote);
        if (!isset($this->quotes[$objectType][$objectID][$quoteID])) {
            $this->quotes[$objectType][$objectID][$quoteID] = 0;
            $this->quoteData[$quoteID] = $message;

            // save parent object id

            if (!isset($this->quoteData['parents'])) {
                $this->quoteData['parents'] = [];
            }

            if (!isset($this->quoteData['parents'][$objectType])) {
                $this->quoteData['parents'][$objectType] = [];
            }

            if (!isset($this->quoteData['parents'][$objectType][$parentObjectID])) {
                $this->quoteData['parents'][$objectType][$parentObjectID] = [];
            }

            $this->quoteData['parents'][$objectType][$parentObjectID][] = $objectID;
            $this->quoteData[$quoteID . '_pID'] = $parentObjectID;

            if (!empty($fullQuote)) {
                $htmlInputProcessor = new HtmlInputProcessor();
                $htmlInputProcessor->processIntermediate($fullQuote);

                if (MESSAGE_MAX_QUOTE_DEPTH) {
                    $htmlInputProcessor->enforceQuoteDepth(MESSAGE_MAX_QUOTE_DEPTH - 1, true);
                }

                $parameters = ['htmlInputProcessor' => $htmlInputProcessor];
                EventHandler::getInstance()->fireAction($this, 'addFullQuote', $parameters);

                $this->quotes[$objectType][$objectID][$quoteID] = 1;
                $this->quoteData[$quoteID . '_fq'] = $htmlInputProcessor->getHtml();
            }

            $this->updateSession();
        } elseif ($returnFalseIfExists) {
            return false;
        }

        return $quoteID;
    }

    /**
     * Returns the quote id for given quote.
     *
     * @param string $objectType
     * @param int $objectID
     * @param string $message
     * @param string $fullQuote
     * @return  string
     */
    public function getQuoteID($objectType, $objectID, $message, $fullQuote = '')
    {
        return \substr(\sha1($objectType . '|' . $objectID . '|' . $message . '|' . $fullQuote), 0, 8);
    }

    /**
     * Removes a quote from storage and returns true if the quote has successfully been removed.
     *
     * @param string $quoteID
     * @return  bool
     */
    public function removeQuote($quoteID)
    {
        if (!isset($this->quoteData[$quoteID])) {
            return false;
        }

        foreach ($this->quotes as $objectType => $objectIDs) {
            foreach ($objectIDs as $objectID => $quoteIDs) {
                foreach ($quoteIDs as $qID => $isFullQuote) {
                    if ($qID == $quoteID) {
                        unset($this->quotes[$objectType][$objectID][$qID]);

                        // clean-up structure
                        if (empty($this->quotes[$objectType][$objectID])) {
                            unset($this->quotes[$objectType][$objectID]);

                            if (empty($this->quotes[$objectType])) {
                                unset($this->quotes[$objectType]);
                            }
                        }

                        unset($this->quoteData[$quoteID]);
                        if ($isFullQuote) {
                            unset($this->quoteData[$quoteID . '_fq']);
                        }

                        // remove parent object id reference
                        if (isset($this->quoteData[$quoteID . '_pID'])) {
                            $parentObjectID = $this->quoteData[$quoteID . '_pID'];
                            if (!isset($this->quotes[$objectType][$objectID])) {
                                if (isset($this->quoteData['parents'][$objectType][$parentObjectID][$objectID])) {
                                    unset($this->quoteData['parents'][$objectType][$parentObjectID][$objectID]);

                                    // cleanup
                                    if (empty($this->quoteData['parents'][$objectType][$parentObjectID])) {
                                        unset($this->quoteData['parents'][$objectType][$parentObjectID]);

                                        if (empty($this->quoteData['parents'][$objectType])) {
                                            unset($this->quoteData['parents'][$objectType]);

                                            if (empty($this->quoteData['parents'])) {
                                                unset($this->quoteData['parents']);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $this->updateSession();

                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Returns an array containing the quote author, link and text.
     *
     * @param string $quoteID
     * @return  string[]|false
     */
    public function getQuoteComponents($quoteID)
    {
        if ($this->getQuote($quoteID, false) === null) {
            return false;
        }

        // find the quote and simulate a regular call to render quotes
        foreach ($this->quotes as $objectType => $objectIDs) {
            foreach ($objectIDs as $objectID => $quoteIDs) {
                if (isset($quoteIDs[$quoteID])) {
                    $quoteHandler = \call_user_func([$this->objectTypes[$objectType]->className, 'getInstance']);
                    $renderedQuotes = $quoteHandler->renderQuotes([
                        $objectID => [
                            $quoteID => $quoteIDs[$quoteID],
                        ],
                    ], true, false);

                    $this->markQuotesForRemoval([$quoteID]);

                    $renderedQuotes[0]['isFullQuote'] = (isset($this->quoteData[$quoteID . '_fq']));

                    return $renderedQuotes[0];
                }
            }
        }

        return false;
    }

    /**
     * Returns a list of quotes.
     *
     * @param bool $supportPaste
     * @return  string
     */
    public function getQuotes($supportPaste = false)
    {
        $template = '';

        foreach ($this->quotes as $objectType => $objectData) {
            $quoteHandler = \call_user_func([$this->objectTypes[$objectType]->className, 'getInstance']);
            $template .= $quoteHandler->render($objectData, $supportPaste);
        }

        return $template;
    }

    /**
     * Returns a list of quotes by object type and id.
     *
     * @param string $objectType
     * @param int[] $objectIDs
     * @param bool $markForRemoval
     * @return  string[]
     */
    public function getQuotesByObjectIDs($objectType, array $objectIDs, $markForRemoval = true)
    {
        if (!isset($this->quotes[$objectType])) {
            return [];
        }

        $data = [];
        $removeQuoteIDs = [];
        foreach ($this->quotes[$objectType] as $objectID => $quoteIDs) {
            if (\in_array($objectID, $objectIDs)) {
                $data[$objectID] = $quoteIDs;

                // mark quotes for removal
                if ($markForRemoval) {
                    $removeQuoteIDs = \array_merge($removeQuoteIDs, \array_keys($quoteIDs));
                }
            }
        }

        // no quotes found
        if (empty($data)) {
            return [];
        }

        // mark quotes for removal
        if (!empty($removeQuoteIDs)) {
            $this->markQuotesForRemoval($removeQuoteIDs);
        }

        $quoteHandler = \call_user_func([$this->objectTypes[$objectType]->className, 'getInstance']);

        return $quoteHandler->renderQuotes($data);
    }

    /**
     * Returns a list of quotes by object type and parent object id.
     *
     * @param string $objectType
     * @param int $parentObjectID
     * @param bool $markForRemoval
     * @return  string[]
     */
    public function getQuotesByParentObjectID($objectType, $parentObjectID, $markForRemoval = true)
    {
        if (!isset($this->quoteData['parents'][$objectType][$parentObjectID])) {
            return [];
        }

        $data = [];
        $removeQuoteIDs = [];
        foreach ($this->quoteData['parents'][$objectType][$parentObjectID] as $objectID) {
            if (isset($this->quotes[$objectType][$objectID])) {
                $data[$objectID] = $this->quotes[$objectType][$objectID];

                // mark quotes for removal
                if ($markForRemoval) {
                    $removeQuoteIDs = \array_merge($removeQuoteIDs, \array_keys($data[$objectID]));
                }
            }
        }

        // no quotes found
        if (empty($data)) {
            return [];
        }

        // mark quotes for removal
        if (!empty($removeQuoteIDs)) {
            $this->markQuotesForRemoval($removeQuoteIDs);
        }

        $quoteHandler = \call_user_func([$this->objectTypes[$objectType]->className, 'getInstance']);

        return $quoteHandler->renderQuotes($data, false);
    }

    /**
     * Returns a quote by id.
     *
     * @param string $quoteID
     * @param bool $useFullQuote
     * @return  string|null
     */
    public function getQuote($quoteID, $useFullQuote = true)
    {
        if ($useFullQuote && isset($this->quoteData[$quoteID . '_fq'])) {
            return $this->quoteData[$quoteID . '_fq'];
        } elseif (isset($this->quoteData[$quoteID])) {
            return $this->quoteData[$quoteID];
        }

        return null;
    }

    /**
     * Returns the object id by quote id.
     *
     * @param string $quoteID
     * @return  int|null
     */
    public function getObjectID($quoteID)
    {
        if (isset($this->quoteData[$quoteID])) {
            foreach ($this->quotes as $objectIDs) {
                foreach ($objectIDs as $objectID => $quoteIDs) {
                    if (isset($quoteIDs[$quoteID])) {
                        return $objectID;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Marks quote ids for removal.
     *
     * @param string[] $quoteIDs
     */
    public function markQuotesForRemoval(array $quoteIDs)
    {
        foreach ($quoteIDs as $index => $quoteID) {
            if (!isset($this->quoteData[$quoteID]) || \in_array($quoteID, $this->removeQuoteIDs)) {
                unset($quoteIDs[$index]);
            }
        }

        if (!empty($quoteIDs)) {
            $this->removeQuoteIDs = \array_merge($this->removeQuoteIDs, $quoteIDs);
            $this->updateSession();
        }
    }

    /**
     * Renders a quote for given message.
     *
     * @param IMessage $message
     * @param string $text
     * @param bool $renderAsString
     * @return  array|string
     */
    public function renderQuote(IMessage $message, $text, $renderAsString = true)
    {
        $parameters = [
            'message' => $message,
            'text' => $text,
        ];
        EventHandler::getInstance()->fireAction($this, 'beforeRenderQuote', $parameters);
        $text = $parameters['text'];

        $escapedLink = \str_replace(["\\", "'"], ["\\\\", "\\'"], $message->getLink());

        if ($renderAsString) {
            return "[quote='" . $message->getUsername() . "','" . $escapedLink . "']" . $text . "[/quote]";
        } else {
            return [
                'username' => $message->getUsername(),
                'link' => $escapedLink,
                'text' => $text,
            ];
        }
    }

    /**
     * Removes quotes marked for removal.
     */
    public function removeMarkedQuotes()
    {
        if (!empty($this->removeQuoteIDs)) {
            foreach ($this->removeQuoteIDs as $quoteID) {
                $this->removeQuote($quoteID);
            }

            // reset list of quote ids marked for removal
            $this->removeQuoteIDs = [];

            $this->updateSession();
        }
    }

    /**
     * Returns the number of stored quotes.
     *
     * @return  int
     */
    public function countQuotes()
    {
        $count = 0;
        foreach ($this->quoteData as $quoteID => $quote) {
            if (\strlen($quoteID) == 8) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Returns a list of full quotes by object id for given object types.
     *
     * @param string[] $objectTypes
     * @return  mixed[][]
     * @throws  SystemException
     */
    public function getFullQuoteObjectIDs(array $objectTypes)
    {
        $objectIDs = [];

        foreach ($objectTypes as $objectType) {
            if (!isset($this->objectTypes[$objectType])) {
                throw new SystemException("Object type '" . $objectType . "' is unknown");
            }

            $objectIDs[$objectType] = [];
            if (isset($this->quotes[$objectType])) {
                foreach ($this->quotes[$objectType] as $objectID => $quotes) {
                    foreach ($quotes as $isFullQuote) {
                        if ($isFullQuote) {
                            $objectIDs[$objectType][] = $objectID;
                            break;
                        }
                    }
                }
            }
        }

        return $objectIDs;
    }

    /**
     * Sets object type and object ids.
     *
     * @param string $objectType
     * @param int[] $objectIDs
     * @throws  SystemException
     */
    public function initObjects($objectType, array $objectIDs)
    {
        if (!isset($this->objectTypes[$objectType])) {
            throw new SystemException("Object type '" . $objectType . "' is unknown");
        }

        $this->objectIDs = ArrayUtil::toIntegerArray($objectIDs);
        $this->objectType = $objectType;
    }

    /**
     * Reads the quote message id.
     */
    public function readParameters()
    {
        if (isset($_REQUEST['quoteMessageID'])) {
            $this->quoteMessageID = (int)$_REQUEST['quoteMessageID'];
        }
    }

    /**
     * Reads a list of quote ids to remove.
     */
    public function readFormParameters()
    {
        if (isset($_REQUEST['__removeQuoteIDs']) && \is_array($_REQUEST['__removeQuoteIDs'])) {
            $quoteIDs = ArrayUtil::trim($_REQUEST['__removeQuoteIDs']);
            foreach ($quoteIDs as $index => $quoteID) {
                if (!isset($this->quoteData[$quoteID])) {
                    unset($quoteIDs[$index]);
                }
            }

            if (!empty($quoteIDs)) {
                $this->removeQuoteIDs = \array_merge($this->removeQuoteIDs, $quoteIDs);
            }
        }
    }

    /**
     * Removes quotes after saving current message.
     */
    public function saved()
    {
        $this->removeMarkedQuotes();
    }

    /**
     * Assigns variables on page load.
     */
    public function assignVariables()
    {
        $fullQuoteObjectIDs = [];
        if (!empty($this->objectType) && !empty($this->objectIDs) && isset($this->quotes[$this->objectType])) {
            foreach ($this->quotes[$this->objectType] as $objectID => $quotes) {
                if (!\in_array($objectID, $this->objectIDs)) {
                    continue;
                }

                foreach ($quotes as $isFullQuote) {
                    if ($isFullQuote) {
                        $fullQuoteObjectIDs[] = $objectID;
                        break;
                    }
                }
            }
        }

        WCF::getTPL()->assign([
            '__quoteCount' => $this->countQuotes(),
            '__quoteFullQuote' => $fullQuoteObjectIDs,
            '__quoteRemove' => $this->removeQuoteIDs,
        ]);
    }

    /**
     * Returns quote message id.
     *
     * @return  int
     */
    public function getQuoteMessageID()
    {
        return $this->quoteMessageID;
    }

    /**
     * Removes orphaned quote ids
     *
     * @param int[] $quoteIDs
     */
    public function removeOrphanedQuotes(array $quoteIDs)
    {
        foreach ($quoteIDs as $quoteID) {
            $this->removeQuote($quoteID);
        }

        $this->updateSession();
    }

    /**
     * Returns true if a quote id represents a full quote.
     *
     * @param string $quoteID
     * @return      bool
     */
    public function isFullQuote($quoteID)
    {
        return isset($this->quoteData[$quoteID . '_fq']);
    }

    /**
     * Updates data stored in session,
     */
    protected function updateSession()
    {
        WCF::getSession()->register('__messageQuotes', [
            'quotes' => $this->quotes,
            'quoteData' => $this->quoteData,
            'removeQuoteIDs' => $this->removeQuoteIDs,
        ]);
    }
}
