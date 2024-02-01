<?php

namespace wcf\system\rssFeed;

final class RssFeed
{
    /**
     * @var RssFeedChannel[]
     */
    private array $channels = [];

    public function channel(RssFeedChannel $channel): static
    {
        $this->channels[] = $channel;

        return $this;
    }

    public function render(): string
    {
        $header = <<<'EOT'
            <?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0"
                xmlns:atom="http://www.w3.org/2005/Atom"
                xmlns:content="http://purl.org/rss/1.0/modules/content/"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
                xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
            >
        EOT;

        $element = new XmlElement(
            $header,
            LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_ERR_FATAL
        );

        foreach ($this->channels as $channel) {
            $toDom = \dom_import_simplexml($element);
            $fromDom = \dom_import_simplexml($channel->getXML());
            $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->appendChild($dom->importNode(\dom_import_simplexml($element), true));
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
