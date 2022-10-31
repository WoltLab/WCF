<?php

namespace wcf\system\event;

use wcf\data\event\listener\EventListener;
use wcf\system\cache\builder\EventListenerCacheBuilder;
use wcf\system\event\IEventListener as ILegacyEventListener;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\SingletonFactory;

/**
 * EventHandler executes all registered actions for a specific event.
 *
 * @author  Tim Duesterhus, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Event
 */
class EventHandler extends SingletonFactory
{
    /**
     * @since 5.5
     */
    public const DEFAULT_EVENT_NAME = ':default';

    /**
     * registered actions
     * @var array
     */
    protected $actions;

    /**
     * registered inherit actions
     * @var array
     */
    protected $inheritedActions;

    /**
     * instances of registered actions
     * @var array
     */
    protected $actionsObjects = [];

    /**
     * instances of registered inherit actions
     * @var array
     */
    protected $inheritedActionsObjects = [];

    /**
     * instances of listener objects
     * @var IEventListener[]
     */
    protected $listenerObjects = [];

    /**
     * Loads all registered actions of the active package.
     */
    protected function loadActions()
    {
        $environment = ((\class_exists('wcf\system\WCFACP', false) || \class_exists(
            'wcf\system\CLIWCF',
            false
        )) ? 'admin' : 'user');
        $cache = EventListenerCacheBuilder::getInstance()->getData();

        if (isset($cache['actions'][$environment])) {
            $this->actions = $cache['actions'][$environment];
        }
        if (isset($cache['inheritedActions'][$environment])) {
            $this->inheritedActions = $cache['inheritedActions'][$environment];
        }
        unset($cache);

        if (!\is_array($this->actions)) {
            $this->actions = [];
        }
        if (!\is_array($this->inheritedActions)) {
            $this->inheritedActions = [];
        }
    }

    /**
     * Executes all inherited listeners for the given event.
     *
     * @param mixed $eventObj
     * @param string $eventName
     * @param string $className
     * @param string $name
     * @param array       &$parameters
     */
    protected function executeInheritedActions($eventObj, $eventName, $className, $name, array &$parameters)
    {
        // create objects of the actions
        if (!isset($this->inheritedActionsObjects[$name]) || !\is_array($this->inheritedActionsObjects[$name])) {
            $this->inheritedActionsObjects[$name] = [];

            // get parent classes
            $familyTree = [];
            $member = (\is_object($eventObj) ? \get_class($eventObj) : $eventObj);
            while ($member != false) {
                $familyTree[] = $member;
                $member = \get_parent_class($member);
            }

            foreach ($familyTree as $member) {
                if (empty($this->inheritedActions[$member][$eventName])) {
                    continue;
                }

                /** @var EventListener $eventListener */
                foreach ($this->inheritedActions[$member][$eventName] as $eventListener) {
                    if (
                        $eventListener->validateOptions()
                        && $eventListener->validatePermissions()
                        && !isset($this->inheritedActionsObjects[$name][$eventListener->listenerClassName])
                    ) {
                        $this->inheritedActionsObjects[$name][$eventListener->listenerClassName] = $this->getListenerObject($eventListener);
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
    protected function getListenerObject(EventListener $eventListener): object
    {
        if (isset($this->listenerObjects[$eventListener->listenerClassName])) {
            return $this->listenerObjects[$eventListener->listenerClassName];
        }

        if (!\class_exists($eventListener->listenerClassName)) {
            throw new \LogicException("Unable to find class '" . $eventListener->listenerClassName . "'.");
        }

        $object = new $eventListener->listenerClassName();
        $this->listenerObjects[$eventListener->listenerClassName] = $object;

        return $object;
    }

    /**
     * @param   EventListener[]     $eventListeners
     * @since   5.5
     */
    protected function executeListeners(
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
     * @param string $eventName
     * @param array       &$parameters
     */
    public function fireAction($eventObj, $eventName, array &$parameters = [])
    {
        // get class name
        if (\is_object($eventObj)) {
            $className = \get_class($eventObj);
        } else {
            $className = $eventObj;
        }

        // load actions from cache if necessary
        if ($this->actions === null && $this->inheritedActions === null) {
            $this->loadActions();
        }

        // generate action name
        $name = self::generateKey($className, $eventName);

        // execute inherited actions first
        if (!empty($this->inheritedActions)) {
            $this->executeInheritedActions($eventObj, $eventName, $className, $name, $parameters);
        }

        // create objects of the actions
        if (!isset($this->actionsObjects[$name]) || !\is_array($this->actionsObjects[$name])) {
            if (!isset($this->actions[$name]) || !\is_array($this->actions[$name])) {
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
                    $this->actionsObjects[$name][$eventListener->listenerClassName] = $this->getListenerObject($eventListener);
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
     * Generates a unique name for an action.
     *
     * @param string $className
     * @param string $eventName
     * @return  string  unique action name
     */
    public static function generateKey($className, $eventName)
    {
        return $eventName . '@' . $className;
    }
}
