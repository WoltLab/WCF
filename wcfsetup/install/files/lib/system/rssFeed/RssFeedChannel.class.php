<?php

namespace wcf\system\rssFeed;

use BadMethodCallException;

final class RssFeedChannel
{
    private string $title;
    private string $description;
    private string $link;
    private string $atomLinkSelf;
    private string $language;
    private string $copyright;
    private string $lastBuildDate;
    private string $pubDate;
    private int $ttl = 60;

    /**
     * @var RssFeedCategory[]
     */
    private array $categories = [];

    /**
     * @var RssFeedItem[]
     */
    private array $items = [];

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

    public function atomLinkSelf(string $link): static
    {
        $this->atomLinkSelf = $link;

        return $this;
    }

    public function language(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function copyright(string $copyright): static
    {
        $this->copyright = $copyright;

        return $this;
    }

    public function lastBuildDate(string $date): static
    {
        $this->lastBuildDate = $date;

        return $this;
    }

    public function lastBuildDateFromTimestamp(int $timestamp): static
    {
        return $this->lastBuildDate(\gmdate('r', $timestamp));
    }

    public function pubDate(string $date): static
    {
        $this->pubDate = $date;

        return $this;
    }

    public function pubDateFromTimestamp(int $timestamp): static
    {
        return $this->pubDate(\gmdate('r', $timestamp));
    }

    public function ttl(int $ttl): static
    {
        $this->ttl = $ttl;

        return $this;
    }

    public function category(string $name, ?string $domain = null): static
    {
        $this->categories[] = new RssFeedCategory($name, $domain);

        return $this;
    }

    public function item(RssFeedItem $item): static
    {
        $this->items[] = $item;

        return $this;
    }

    public function getXML(): \SimpleXMLElement
    {
        $this->integrityCheck();

        $element = new XmlElement(
            '<?xml version="1.0" encoding="UTF-8"?><channel></channel>',
            LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_ERR_FATAL
        );

        if (isset($this->title)) {
            $element->addChild('title', $this->title);
        }
        if (isset($this->description)) {
            $element->addChild('description', $this->description);
        }
        if (isset($this->link)) {
            $element->addChild('link', $this->link);
        }
        if (isset($this->language)) {
            $element->addChild('language', $this->language);
        }
        if (isset($this->copyright)) {
            $element->addChild('copyright', $this->copyright);
        }
        if (isset($this->lastBuildDate)) {
            $element->addChild('lastBuildDate', $this->lastBuildDate);
        }
        if (isset($this->pubDate)) {
            $element->addChild('pubDate', $this->pubDate);
        }

        if (isset($this->atomLinkSelf)) {
            $atomLink = $element->addChild('xmlns:atom:link');
            $atomLink->addAttribute('href', $this->atomLinkSelf);
            $atomLink->addAttribute('rel', 'self');
            $atomLink->addAttribute('type', 'application/rss+xml');
        }

        $element->addChild('ttl', $this->ttl);
        $element->addChild('generator', 'WoltLab Suite' . (SHOW_VERSION_NUMBER ? ' ' . \WCF_VERSION : ''));

        foreach ($this->categories as $category) {
            $categoryElement = $element->addChild('category', $category->name);
            if ($category->domain !== null) {
                $categoryElement->addAttribute('domain', $category->domain);
            }
        }

        foreach ($this->items as $item) {
            $toDom = \dom_import_simplexml($element);
            $fromDom = \dom_import_simplexml($item->getXML());
            $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
        }

        return $element;
    }

    private function integrityCheck(): void
    {
        // Title, description and link are required.
        if (!isset($this->title)) {
            throw new BadMethodCallException("missing parameter 'title'");
        }

        if (!isset($this->description)) {
            throw new BadMethodCallException("missing parameter 'description'");
        }

        if (!isset($this->link)) {
            throw new BadMethodCallException("missing parameter 'link'");
        }
    }
}
