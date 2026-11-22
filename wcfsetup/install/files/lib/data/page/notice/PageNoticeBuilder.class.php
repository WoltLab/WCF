<?php

namespace wcf\data\page\notice;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\page\Page;
use wcf\data\user\User;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\l10n\L10nStorage;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;

/**
 * Builder for creating and updating page notices.
 *
 * The localized message is stored in the `wcf1_page_notice_l10n` table (see
 * `L10nStorage`), the embedded objects of all languages are registered for the
 * notice.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<PageNotice>
 */
final class PageNoticeBuilder extends DatabaseObjectBuilder
{
    /**
     * @var array<int, HtmlInputProcessor>
     */
    private array $htmlInputProcessors;

    /**
     * Sets the page and, if the page requires one, the object of the page the
     * notice is shown on.
     */
    public function setPage(Page $page, ?int $pageObjectID = null): static
    {
        if ($page->requireObjectID !== 0 && $pageObjectID === null) {
            throw new \InvalidArgumentException("The page '{$page->identifier}' requires an object id.");
        }

        $this->properties['pageID'] = $page->pageID;
        $this->properties['pageObjectID'] = $page->requireObjectID !== 0 ? $pageObjectID : null;

        return $this;
    }

    public function setUser(User $user): static
    {
        $this->properties['userID'] = $user->userID !== 0 ? $user->userID : null;
        $this->properties['username'] = $user->username;

        return $this;
    }

    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    /**
     * Sets the type, one of `PageNotice::TYPES`.
     */
    public function setType(string $type): static
    {
        if (!\in_array($type, PageNotice::TYPES, true)) {
            throw new \InvalidArgumentException("Unknown type '{$type}'.");
        }

        $this->properties['type'] = $type;

        return $this;
    }

    /**
     * Sets whether the notice is only visible to users who can manage page
     * notices.
     */
    public function setInternal(bool $isInternal): static
    {
        $this->properties['isInternal'] = $isInternal ? 1 : 0;

        return $this;
    }

    /**
     * Sets the message as a `languageID => input processor` map, using
     * `L10nStorage::MONOLINGUAL` as the key of a monolingual message.
     *
     * @param array<int, HtmlInputProcessor> $htmlInputProcessors
     */
    public function setMessage(array $htmlInputProcessors): static
    {
        if ($htmlInputProcessors === []) {
            throw new \InvalidArgumentException("The message must not be empty.");
        }

        $this->htmlInputProcessors = $htmlInputProcessors;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return [
            'pageID',
        ];
    }

    #[\Override]
    protected function afterValidateCreate(): void
    {
        if (!isset($this->htmlInputProcessors)) {
            throw new \BadMethodCallException("Missing value for 'message'.");
        }

        $this->properties['time'] ??= \TIME_NOW;
    }

    #[\Override]
    protected function afterCreate(): void
    {
        $this->saveMessage();
    }

    #[\Override]
    protected function afterUpdate(): void
    {
        if (isset($this->htmlInputProcessors)) {
            $this->saveMessage();
        }
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        MessageEmbeddedObjectManager::getInstance()->removeObjects(
            PageNotice::MESSAGE_OBJECT_TYPE,
            $objectIDs
        );
    }

    private function saveMessage(): void
    {
        $pageNotice = $this->getObject();

        $embeddedObjectManager = MessageEmbeddedObjectManager::getInstance();
        // Embedded objects are tracked per notice, thus the objects of all languages are registered.
        $embeddedObjectManager->removeObjects(PageNotice::MESSAGE_OBJECT_TYPE, [$pageNotice->pageNoticeID]);

        $message = [];
        $hasEmbeddedObjects = 0;
        foreach ($this->htmlInputProcessors as $languageID => $htmlInputProcessor) {
            $htmlInputProcessor->setObjectID($pageNotice->pageNoticeID);
            $message[$languageID] = $htmlInputProcessor->getHtml();

            if ($embeddedObjectManager->registerObjects($htmlInputProcessor, removeExistingObjects: false)) {
                $hasEmbeddedObjects = 1;
            }
        }

        // `L10nStorage::setValues()` replaces all rows of the notice.
        new L10nStorage(PageNotice::getL10nDefinition())->setValues(
            $pageNotice->pageNoticeID,
            ['message' => $message]
        );

        if ($hasEmbeddedObjects !== $pageNotice->hasEmbeddedObjects) {
            $sql = "UPDATE  wcf1_page_notice
                    SET     hasEmbeddedObjects = ?
                    WHERE   pageNoticeID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $hasEmbeddedObjects,
                $pageNotice->pageNoticeID,
            ]);

            $this->object = new PageNotice($pageNotice->pageNoticeID);
        }
    }
}
