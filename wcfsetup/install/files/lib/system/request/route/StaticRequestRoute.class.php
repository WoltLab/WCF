<?php

namespace wcf\system\request\route;

use wcf\system\request\ControllerMap;

/**
 * Static route implementation to resolve HTTP requests, handling a single controller.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class StaticRequestRoute extends DynamicRequestRoute
{
    /**
     * controller must be present and match the static controller
     * @var bool
     */
    protected $matchController = false;

    /**
     * static application identifier
     * @var string
     */
    protected $staticApplication = '';

    /**
     * static controller name, not the FQN
     * @var string
     */
    protected $staticController = '';

    /**
     * Always throws. This method only exists because StaticRequestRoute inherits from DynamicRequestRoute.
     */
    public function setIsACP($isACP)
    {
        throw new \BadMethodCallException('Calling setIsAcp() is not allowed.');
    }

    /**
     * Always returns false.
     */
    public function isACP()
    {
        return false;
    }

    /**
     * Controller must be part of the url and match the static controller, useful
     * for controllers requiring a custom set of additional parameters.
     *
     * @param bool $matchController
     */
    public function setMatchController($matchController)
    {
        $this->matchController = $matchController;
    }

    /**
     * Sets the static controller for this route.
     *
     * @param string $application
     * @param string $controller
     */
    public function setStaticController($application, $controller)
    {
        $this->staticApplication = $application;
        $this->staticController = $controller;

        $this->requireComponents['controller'] = '~^' . $this->staticController . '$~';
    }

    /**
     * @inheritDoc
     */
    public function buildLink(array $components)
    {
        if ($this->matchController) {
            return parent::buildLink($components);
        }

        // static routes don't have these components
        unset($components['application']);
        unset($components['controller']);

        return $this->buildRoute($components, '', true);
    }

    /**
     * @inheritDoc
     */
    public function canHandle(array $components)
    {
        if (isset($components['application']) && $components['application'] == $this->staticApplication) {
            if (isset($components['controller']) && $components['controller'] == $this->staticController) {
                return parent::canHandle($components);
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function matches($requestURL)
    {
        if (parent::matches($requestURL)) {
            $controller = ControllerMap::getInstance()->lookup(
                $this->staticApplication,
                $this->staticController,
                !$this->isACP()
            );
            if ($this->matchController && $this->routeData['controller'] !== $controller) {
                return false;
            }

            $this->routeData['application'] = $this->staticApplication;
            $this->routeData['controller'] = ControllerMap::transformController($this->staticController);
            $this->routeData['isDefaultController'] = false;
            $this->routeData['isRenamedController'] = (\strcasecmp($controller, $this->staticController) !== 0);

            return true;
        }

        return false;
    }
}
