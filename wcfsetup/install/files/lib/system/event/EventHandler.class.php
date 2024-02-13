<?php

namespace wcf\system\event;

use wcf\data\event\listener\EventListener;
use wcf\system\cache\builder\EventListenerCacheBuilder;
use wcf\system\event\IEventListener as ILegacyEventListener;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\exception\ClassNotFoundException;
use wcf\system\SingletonFactory;

/**
 * EventHandler executes all registered actions for a specific event.
 *
 * @author  Tim Duesterhus, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class EventHandler extends SingletonFactory
{
    /**
     * @since 5.5
     */
    public const DEFAULT_EVENT_NAME = ':default';

    /**
     * @var array<string, EventListener>
     */
    private array $actions = [];

    /**
     * @var array<class-string, array<string, EventListener>>
     */
    private array $inheritedActions = [];

    /**
     * @template T of object
     * @var array<string, array<class-string<T>, T>>
     */
    private array $actionsObjects = [];

    /**
     * @var array<string, array<class-string, object>>
     */
    private array $inheritedActionsObjects = [];

    /**
     * @template T of object
     * @var array<class-string<T>, T>
     */
    private array $listenerObjects = [];

    /**
     * @var array<class-string, callable>
     */
    private array $psr14Listeners = [];

    /**
     * @var array<class-string, class-string[]>
     */
    private array $psr14ListenerClasses = [];

    /**
     * Loads all registered actions of the active package.
     */
    protected function init(): void
    {
        $environment = ((\class_exists('wcf\system\WCFACP', false) || \class_exists(
            'wcf\system\CLIWCF',
            false
        )) ? 'admin' : 'user');
        $cache = EventListenerCacheBuilder::getInstance()->getData();

        $this->actions = $cache['actions'][$environment];
        $this->inheritedActions = $cache['inheritedActions'][$environment];
    }

    /**
     * Executes all inherited listeners for the given event.
     *
     * @param mixed $eventObj
     */
    private function executeInheritedActions($eventObj, string $eventName, string $className, string $name, array &$parameters)
    {
        // create objects of the actions
        if (!isset($this->inheritedActionsObjects[$name])) {
            $this->inheritedActionsObjects[$name] = [];

            // get parent classes
            $familyTree = [];
            $member = (\is_object($eventObj) ? \get_class($eventObj) : $eventObj);
            while ($member != false) {
                $familyTree[] = $member;
                $member = \get_parent_class($member);
            }

            foreach ($familyTree as $member) {
                if (!isset($this->inheritedActions[$member][$eventName])) {
                    continue;
                }

                /** @var EventListener $eventListener */
                foreach ($this->inheritedActions[$member][$eventName] as $eventListener) {
                    if (
                        $eventListener->validateOptions()
                        && $eventListener->validatePermissions()
                        && !isset($this->inheritedActionsObjects[$name][$eventListener->listenerClassName])
                    ) {
                        $this->inheritedActionsObjects[$name][$eventListener->listenerClassName] = $this->getListenerObject($eventListener->listenerClassName);
                    }
                }
            }
        }

        $this->executeListeners(
            $this->inheritedActionsObjects[$name],
            $eventObj,
            $className,
            $eventName,
            $parameters
        );
    }

    /**
     * @since   5.5
     */
    private function getListenerObject(string $className): object
    {
        if (isset($this->listenerObjects[$className])) {
            return $this->listenerObjects[$className];
        }

        if (!\class_exists($className)) {
            throw new ClassNotFoundException($className);
        }

        $object = new $className();
        $this->listenerObjects[$className] = $object;

        return $object;
    }

    /**
     * @param   (callable[])|((IParameterizedEventListener|ILegacyEventListener)[])     $eventListeners
     * @since   5.5
     */
    private function executeListeners(
        array $eventListeners,
        $eventObj,
        string $className,
        string $eventName,
        array &$parameters
    ): void {
        foreach ($eventListeners as $actionObj) {
            $actionClassName = \get_class($actionObj);
            if ($eventObj instanceof IEvent) {
                if (!\is_callable($actionObj)) {
                    throw new \LogicException("Event listener object of class '{$actionClassName}' is not callable.");
                }

                $actionObj($eventObj);
            } elseif ($actionObj instanceof IParameterizedEventListener) {
                $actionObj->execute($eventObj, $className, $eventName, $parameters);

                if (!\is_array($parameters)) {
                    throw new \LogicException("'{$actionClassName}' breaks the '\$parameters' array.");
                }
            } elseif ($actionObj instanceof ILegacyEventListener) {
                $actionObj->execute($eventObj, $className, $eventName);
            } else {
                throw new \LogicException("Cannot execute event listener '{$actionClassName}'.");
            }
        }
    }

    /**
     * Executes all registered listeners for the given event.
     *
     * $parameters is an optional array of parameters. Event listeners
     * are able to modify these. Any modification will be passed on to
     * the next event listener and be available after execution of every
     * event listener.
     *
     * @param mixed $eventObj
     */
    public function fireAction($eventObj, string $eventName, array &$parameters = [])
    {
        // get class name
        if (\is_object($eventObj)) {
            $className = \get_class($eventObj);
        } else {
            $className = $eventObj;
        }

        // generate action name
        $name = self::generateKey($className, $eventName);

        if ($eventObj instanceof IEvent && $eventName === self::DEFAULT_EVENT_NAME) {
            foreach ($this->getListenersForEvent($eventObj) as $listener) {
                $listener($eventObj);
            }
        }

        // execute inherited actions first
        $this->executeInheritedActions($eventObj, $eventName, $className, $name, $parameters);

        // create objects of the actions
        if (!isset($this->actionsObjects[$name])) {
            if (!isset($this->actions[$name])) {
                // no action registered
                return;
            }

            $this->actionsObjects[$name] = [];
            /** @var EventListener $eventListener */
            foreach ($this->actions[$name] as $eventListener) {
                if (
                    $eventListener->validateOptions()
                    && $eventListener->validatePermissions()
                    && !isset($this->actionsObjects[$name][$eventListener->listenerClassName])
                ) {
                    $this->actionsObjects[$name][$eventListener->listenerClassName] = $this->getListenerObject($eventListener->listenerClassName);
                }
            }
        }

        $this->executeListeners(
            $this->actionsObjects[$name],
            $eventObj,
            $className,
            $eventName,
            $parameters
        );
    }

    /**
     * Calls fireAction() for the given event with the `:default` event name.
     *
     * @see EventHandler::fireAction()
     * @since 5.5
     */
    public function fire(IEvent $event): void
    {
        $this->fireAction($event, self::DEFAULT_EVENT_NAME);
    }

    /**
     * This method matches PSR-14's ListenerProviderInterface, except that
     * it is private. We do not want to provide PSR-14 compatibility as part
     * of the public API yet.
     *
     * @return iterable<callable>
     */
    private function getListenersForEvent(object $event): iterable
    {
        $classes = \array_values([
            $event::class,
            ...\class_implements($event),
            ...\class_parents($event),
        ]);

        foreach ($classes as $class) {
            yield from $this->getPsr14Listeners($class);
        }
    }

    /**
     * @param class-string $eventClass
     * @return iterable<callable>
     */
    private function getPsr14Listeners(string $eventClass): iterable
    {
        if (isset($this->psr14ListenerClasses[$eventClass])) {
            $this->psr14Listeners[$eventClass] ??= [];

            foreach ($this->psr14ListenerClasses[$eventClass] as $listenerClass) {
                $object = $this->getListenerObject($listenerClass);

                $this->psr14Listeners[$eventClass][] = $object;
            }

            unset($this->psr14ListenerClasses[$eventClass]);
        }

        return $this->psr14Listeners[$eventClass] ?? [];
    }

    /**
     * Returns a new event listener for the given event. The listener
     * must either be a class name of a class that implements __invoke()
     * or a callable.
     *
     * @param class-string $event
     * @param class-string|callable $listener
     */
    public function register(string $event, string|callable $listener): void
    {
        if (\is_string($listener)) {
            $this->psr14ListenerClasses[$event] ??= [];
            $this->psr14ListenerClasses[$event][] = $listener;
        } else {
            $this->psr14Listeners[$event] ??= [];
            $this->psr14Listeners[$event][] = $listener;
        }
    }

    /**
     * Generates an unique name for an action.
     */
    public static function generateKey(string $className, string $eventName): string
    {
        return $eventName . '@' . $className;
    }
}
