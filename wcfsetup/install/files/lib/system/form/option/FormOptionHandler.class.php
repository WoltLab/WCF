<?php

namespace wcf\system\form\option;

use wcf\event\form\option\FormOptionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Provides the available form options.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class FormOptionHandler extends SingletonFactory
{
    /**
     * @var array<string, IFormOption>
     */
    private array $options;

    #[\Override]
    protected function init()
    {
        $this->options = \array_merge($this->getDefaultFormOptions(), $this->getEventFormOptions());
    }

    /**
     * @return array<string, IFormOption>
     */
    private function getDefaultFormOptions(): array
    {
        $options = [];

        foreach (
            [
                new BooleanFormOption(),
                new CheckboxesFormOption(),
                new CurrencyFormOption(),
                new DateFormOption(),
                new EmailFormOption(),
                new FloatFormOption(),
                new IconFormOption(),
                new IntegerFormOption(),
                new RadioButtonFormOption(),
                new RatingFormOption(),
                new SelectFormOption(),
                new SourceCodeFormOption(),
                new TextFormOption(),
                new TextareaFormOption(),
                new TimezoneFormOption(),
                new UrlFormOption(),
                new WysiwygFormOption(),
            ] as $defaultOption
        ) {
            $options[$defaultOption->getId()] = $defaultOption;
        }

        return $options;
    }

    /**
     * @return array<string, IFormOption>
     */
    private function getEventFormOptions(): array
    {
        $event = new FormOptionCollecting();
        EventHandler::getInstance()->fire($event);

        return $event->getOptions();
    }

    /**
     * @return array<string, IFormOption>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $identifier): ?IFormOption
    {
        return $this->options[$identifier] ?? null;
    }

    /**
     * Returns the titles of the given form options, or all registered form
     * options, sorted by their title.
     *
     * @param ?array<string, IFormOption> $options
     * @return array<string, string>
     */
    public function getSortedOptionTypes(?array $options = null): array
    {
        $optionTypes = \array_map(fn($option) => $option->getTitle(), $options ?? $this->getOptions());

        $collator = new \Collator(WCF::getLanguage()->getLocale());
        \uasort(
            $optionTypes,
            static fn(string $a, string $b) => $collator->compare($a, $b)
        );

        return $optionTypes;
    }
}
