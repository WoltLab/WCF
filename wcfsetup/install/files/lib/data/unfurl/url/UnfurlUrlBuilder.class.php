<?php

namespace wcf\data\unfurl\url;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\background\job\UnfurlUrlBackgroundJob;
use wcf\util\Url;

/**
 * Builder for creating, updating and deleting unfurled urls.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UnfurlUrl>
 */
final class UnfurlUrlBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the url and derives the hash used to identify it.
     *
     * @throws \InvalidArgumentException if the given url is invalid
     */
    public function setUrl(string $url): static
    {
        if (!Url::is($url)) {
            throw new \InvalidArgumentException("Given URL is not a valid URL.");
        }

        $this->properties['url'] = $url;
        $this->properties['urlHash'] = \sha1($url);

        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->properties['title'] = $title;

        return $this;
    }

    public function setDescription(?string $description): static
    {
        $this->properties['description'] = $description;

        return $this;
    }

    public function setImageID(?int $imageID): static
    {
        $this->properties['imageID'] = $imageID;

        return $this;
    }

    /**
     * @throws \InvalidArgumentException if the given status is unknown
     */
    public function setStatus(string $status): static
    {
        switch ($status) {
            case UnfurlUrl::STATUS_PENDING:
            case UnfurlUrl::STATUS_REJECTED:
            case UnfurlUrl::STATUS_SUCCESSFUL:
                break;

            default:
                throw new \InvalidArgumentException("Invalid status '{$status}' given.");
        }

        $this->properties['status'] = $status;

        return $this;
    }

    public function setLastFetch(int $lastFetch): static
    {
        $this->properties['lastFetch'] = $lastFetch;

        return $this;
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        BackgroundQueueHandler::getInstance()->enqueueIn([
            new UnfurlUrlBackgroundJob($object),
        ]);

        BackgroundQueueHandler::getInstance()->forceCheck();
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['url', 'urlHash'];
    }
}
