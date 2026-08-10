<?php

namespace wcf\system\html\simple;

use wcf\system\message\embedded\object\ISimpleMessageEmbeddedObjectHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\SingletonFactory;
use wcf\util\ArrayUtil;

/**
 * Parses content for simple placeholders.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class HtmlSimpleParser extends SingletonFactory
{
    /**
     * embedded object context
     * @var array{objectType: string, objectID: int}
     */
    protected $context = [
        'objectType' => '',
        'objectID' => 0,
    ];

    /**
     * @var array<string, ISimpleMessageEmbeddedObjectHandler>
     */
    protected $handlers = [];

    /**
     * regex for simple placeholders
     * @var string
     */
    protected $regexHandlers = '~\{\{ ((?:[a-z][a-zA-Z]+="(?:\\\\"|[^"])+" ?)*) \}\}~';

    #[\Override]
    protected function init()
    {
        $this->handlers = MessageEmbeddedObjectManager::getInstance()->getSimpleMessageEmbeddedObjectHandlers();
    }

    /**
     * Sets the embedded object context.
     *
     * @return void
     */
    public function setContext(string $objectType, int $objectID)
    {
        MessageEmbeddedObjectManager::getInstance()->setActiveMessage($objectType, $objectID);

        $this->context = [
            'objectType' => $objectType,
            'objectID' => $objectID,
        ];
    }

    /**
     * Parses a message to identify any embedded content using simple placeholders.
     *
     * @return bool true if there is at least one embedded content found
     */
    public function parse(string $objectType, int $objectID, string $message)
    {
        \preg_match_all($this->regexHandlers, $message, $matches);

        $data = [];
        foreach ($matches[1] as $attributesString) {
            $attributes = $this->parseAttributes($attributesString);
            $handler = $attributes['handler'];
            if (!isset($this->handlers[$handler])) {
                // unknown handler, ignore
                continue;
            }

            if (!isset($data[$handler])) {
                $data[$handler] = [];
            }

            $data[$handler][] = $attributes['value'];
        }

        $embeddedContent = [];
        foreach ($data as $handler => $values) {
            $values = ArrayUtil::toIntegerArray($values);
            $values = $this->handlers[$handler]->validateValues($objectType, $objectID, $values);
            if ($values !== []) {
                $embeddedContent[$this->handlers[$handler]->objectTypeID] = \array_unique($values);
            }
        }

        return MessageEmbeddedObjectManager::getInstance()
            ->registerSimpleObjects($objectType, $objectID, $embeddedContent);
    }

    /**
     * Replaces simple placeholders with embedded content data.
     *
     * @return string parsed and replaced string
     */
    public function replaceTags(string $objectType, int $objectID, string $message)
    {
        MessageEmbeddedObjectManager::getInstance()->setActiveMessage($objectType, $objectID);
        $this->setContext($objectType, $objectID);

        return \preg_replace_callback($this->regexHandlers, function ($matches) {
            $data = $this->parseAttributes($matches[1]);

            return $this->replaceTag($data);
        }, $message);
    }

    /**
     * Replaces a placeholder.
     *
     * @param array{
     *  attributes: array<string, string>,
     *  handler: string,
     *  raw: string,
     *  value: string,
     * } $data placeholder data
     * @return string placeholder replacement
     */
    public function replaceTag(array $data)
    {
        $handler = $data['handler'];

        if (!isset($this->handlers[$handler])) {
            // unknown handler, return raw value
            return $data['raw'];
        }

        $value = $this->handlers[$handler]->replaceSimple(
            $this->context['objectType'],
            $this->context['objectID'],
            $data['value'],
            $data['attributes']
        );
        if ($value === null) {
            // invalid value
            return $data['raw'];
        }

        return $value;
    }

    /**
     * Parses the template by replacing the simple embedded object syntax
     * with a custom template plugin. This step ensures proper replacement
     * without causing conflicts with existing syntax.
     *
     * @return string template content with custom template plugin
     */
    public function parseTemplate(string $template)
    {
        return \preg_replace_callback($this->regexHandlers, function ($matches) {
            $data = $this->parseAttributes($matches[1]);
            $handler = $data['handler'];

            if (!isset($this->handlers[$handler])) {
                // unknown handler, return raw value
                return $matches[0];
            }

            return '{embeddedObject}' . \base64_encode(\serialize($data)) . '{/embeddedObject}';
        }, $template);
    }

    /**
     * Parses the attribute string and return individual components.
     *
     * @param string $attributesString attributes string, e.g. `foo="1" bar="baz"`
     * @return array{
     *  attributes: array<string, string>,
     *  handler: string,
     *  raw: string,
     *  value: string,
     * }
     */
    protected function parseAttributes(string $attributesString)
    {
        \preg_match_all('~([a-z][a-zA-Z]+)="((?:\\\\"|[^"])+)"~', $attributesString, $attributes);

        $additionalAttributes = [];
        for ($i = 1, $length = \count($attributes[0]); $i < $length; $i++) {
            $additionalAttributes[$attributes[1][$i]] = $attributes[2][$i];
        }

        return [
            'attributes' => $additionalAttributes,
            'handler' => $attributes[1][0],
            'raw' => '{{ ' . $attributesString . ' }}',
            'value' => $attributes[2][0],
        ];
    }
}
