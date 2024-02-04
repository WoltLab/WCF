<?php

namespace wcf\system\rssFeed;

use BadMethodCallException;

/**
 * Represents an rss feed item.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class RssFeedItem
{
    private string $title;
    private string $link;
    private string $description;
    private string $author;
    private string $comments;
    private int $slashComments;
    private RssFeedEnclosure $enclosure;
    private string $guid;
    private bool $guidIsPermalink = true;
    private string $pubDate;
    private string $creator;
    private string $contentEncoded;
    private RssFeedSource $source;

    /**
     * @var RssFeedCategory[]
     */
    private array $categories = [];

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function link(string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function pubDate(string $pubDate): static
    {
        $this->pubDate = $pubDate;

        return $this;
    }

    public function pubDateFromTimestamp(int $timestamp): static
    {
        return $this->pubDate(\gmdate('r', $timestamp));
    }

    public function creator(string $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    public function guid(string $guid, bool $isPermalink = true): static
    {
        $this->guid = $guid;
        $this->guidIsPermalink = $isPermalink;

        return $this;
    }

    public function enclosure(string $url, int $length, string $type): static
    {
        $this->enclosure = new RssFeedEnclosure($url, $length, $type);

        return $this;
    }

    public function contentEncoded(string $content): static
    {
        $this->contentEncoded = $content;

        return $this;
    }

    public function comments(string $url): static
    {
        $this->comments = $url;

        return $this;
    }

    public function slashComments(int $comments): static
    {
        $this->slashComments = $comments;

        return $this;
    }

    public function category(string $name, ?string $domain = null): static
    {
        $this->categories[] = new RssFeedCategory($name, $domain);

        return $this;
    }

    public function author(string $email): static
    {
        $this->author = $email;

        return $this;
    }

    public function source(string $name, string $url): static
    {
        $this->source = new RssFeedSource($name, $url);

        return $this;
    }

    public function getXML(): \SimpleXMLElement
    {
        $this->integrityCheck();

        $element = new XmlElement(
            '<?xml version="1.0" encoding="UTF-8"?><item></item>',
            LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_ERR_FATAL
        );

        if (isset($this->title)) {
            $element->addChild('title', $this->title);
        }
        if (isset($this->link)) {
            $element->addChild('link', $this->link);
        }
        if (isset($this->author)) {
            $element->addChild('author', $this->author);
        }
        if (isset($this->description)) {
            $element->addChildCData('description', $this->description);
        }
        if (isset($this->comments)) {
            $element->addChild('comments', $this->comments);
        }
        if (isset($this->slashComments)) {
            $element->addChild('xmlns:slash:comments', $this->slashComments);
        }
        if (isset($this->guid)) {
            $guidElement = $element->addChild('guid', $this->guid);
            if (!$this->guidIsPermalink) {
                $guidElement->addAttribute('isPermaLink', 'false');
            }
        }
        if (isset($this->pubDate)) {
            $element->addChild('pubDate', $this->pubDate);
        }
        if (isset($this->creator)) {
            $element->addChild('xmlns:dc:creator', $this->creator);
        }
        if (isset($this->contentEncoded)) {
            $element->addChildCData('xmlns:content:encoded', $this->contentEncoded);
        }
        if (isset($this->source)) {
            $sourceElement = $element->addChild('source', $this->source->name);
            $sourceElement->addAttribute('url', $this->source->url);
        }
        if (isset($this->enclosure)) {
            $enclosureElement = $element->addChild('enclosure');
            $enclosureElement->addAttribute('url', $this->enclosure->url);
            $enclosureElement->addAttribute('type', $this->enclosure->type);
            $enclosureElement->addAttribute('length', $this->enclosure->length);
        }

        foreach ($this->categories as $category) {
            $categoryElement = $element->addChild('category', $category->name);
            if ($category->domain !== null) {
                $categoryElement->addAttribute('domain', $category->domain);
            }
        }

        return $element;
    }

    private function integrityCheck(): void
    {
        // All elements of an item are optional, however at least one of title or description must be present.
        if (!isset($this->title) && !isset($this->description)) {
            throw new BadMethodCallException("feed item needs either a 'title' or 'description'");
        }
    }
}
