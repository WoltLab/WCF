<?php

namespace wcf\system\search\acp;

/**
 * Represents an ACP search result.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ACPSearchResult implements \Stringable
{
    /**
     * item link
     * @var string
     */
    protected $link = '';

    /**
     * item subtitle
     * @var string
     */
    protected $subtitle = '';

    /**
     * item title
     * @var string
     */
    protected $title = '';

    public function __construct(string $title, string $link, string $subtitle = '')
    {
        $this->title = $title;
        $this->link = $link;
        $this->subtitle = $subtitle;
    }

    /**
     * Returns the item link.
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * Returns the item title.
     *
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the item subtitle.
     *
     * @return  string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getTitle();
    }
}
