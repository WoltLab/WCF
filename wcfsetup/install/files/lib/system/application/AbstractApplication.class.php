<?php

namespace wcf\system\application;

use wcf\data\package\PackageCache;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Abstract implementation of a WoltLab Suite application.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Application
 */
abstract class AbstractApplication extends SingletonFactory implements IApplication
{
    /**
     * application's abbreviation
     * @var string
     */
    protected $abbreviation = '';

    /**
     * evaluation end date, `0` to disable
     * @var int
     */
    protected $evaluationEndDate = 0;

    /**
     * WoltLab Plugin-Store file id
     * @var int
     */
    protected $evaluationPluginStoreID = 0;

    /**
     * @deprecated 5.5 - This value is unused and will always be 'false'. The 'active' status is determined live.
     */
    protected $isActiveApplication = false;

    /**
     * application's package id
     * @var int
     */
    protected $packageID = 0;

    /**
     * qualified name of application's primary controller
     * @var string
     */
    protected $primaryController = '';

    /**
     * @inheritDoc
     */
    final protected function init()
    {
        if (empty($this->abbreviation)) {
            $classParts = \explode('\\', static::class);
            $this->abbreviation = $classParts[0];
        } elseif ($this->abbreviation == 'wcf') {
            throw new SystemException("Unable to determine application, abbreviation is missing");
        }

        $application = ApplicationHandler::getInstance()->getApplication($this->abbreviation);
        if ($application === null) {
            throw new SystemException("Unable to determine application, abbreviation is unknown");
        }

        $this->packageID = $application->packageID;
    }

    /**
     * @inheritDoc
     */
    public function __run()
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
    public function isActiveApplication()
    {
        return $this->packageID == ApplicationHandler::getInstance()->getActiveApplication()->packageID;
    }

    /**
     * @inheritDoc
     */
    public function getEvaluationEndDate()
    {
        return $this->evaluationEndDate;
    }

    /**
     * @inheritDoc
     */
    public function getEvaluationPluginStoreID()
    {
        return $this->evaluationPluginStoreID;
    }

    /**
     * Returns application package.
     *
     * @return  \wcf\data\package\Package
     */
    public function getPackage()
    {
        return PackageCache::getInstance()->getPackage($this->packageID);
    }

    /**
     * @inheritDoc
     */
    public function getPrimaryController()
    {
        return $this->primaryController;
    }

    /**
     * @since 5.2
     * @deprecated 5.5 - This function is a noop. The 'active' status is determined live.
     */
    public function rebuildActiveApplication()
    {
    }

    /**
     * @inheritDoc
     */
    public static function __callStatic($method, array $arguments)
    {
        return \call_user_func_array([WCF::class, $method], $arguments);
    }
}
