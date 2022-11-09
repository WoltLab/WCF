<?php

namespace wcf\system\language\preload;

use wcf\data\language\Language;
use wcf\system\event\EventHandler;
use wcf\system\Regex;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;

final class LanguagePreloader
{
    private readonly EventHandler $eventHandler;
    private readonly Language $language;

    private const STORAGE_PATH = \WCF_DIR . 'js/preload/';

    public function __construct(Language $language)
    {
        $this->eventHandler = EventHandler::getInstance();
        $this->language = $language;
    }

    public function rebuild(): void
    {
        $registerPreloadPhrases = new RegisterPreloadPhrases($this->language);
        $this->eventHandler->fire($registerPreloadPhrases);

        $payload = $this->generatePreloadPayload($registerPreloadPhrases->getPreloadPhrases());

        $filename = $this->getPreloadFileName();
        \file_put_contents($filename, $payload);
        FileUtil::makeWritable($filename);
    }

    public function mustBeRebuild(): bool
    {
        return \file_exists($this->getPreloadFileName());
    }

    private function getPreloadFileName(): string
    {
        return \sprintf(
            '%s%s.preload.js',
            self::STORAGE_PATH,
            $this->language->getLocale(),
        );
    }

    /**
     * @param PreloadPhrase[] $preloadPhrases
     */
    private  function generatePreloadPayload(array $preloadPhrases): string
    {
        return '';
    }

    public static function reset(): void
    {
        DirectoryUtil::getInstance(self::STORAGE_PATH)->removePattern(new Regex('.*\.preload\.js$'));
    }
}
