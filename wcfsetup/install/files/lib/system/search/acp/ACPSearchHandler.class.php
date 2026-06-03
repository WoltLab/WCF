<?php

namespace wcf\system\search\acp;

use wcf\event\acp\search\provider\ProviderCollecting;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\ACPSearchProviderCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\ImplementationException;
use wcf\system\SingletonFactory;

/**
 * Handles ACP Search.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ACPSearchHandler extends SingletonFactory
{
    /**
     * list of application abbreviations
     * @var string[]
     */
    public $abbreviations = [];

    /**
     * @var array<string, IACPSearchResultProvider>
     */
    private array $providers = [];

    /**
     * @var array<string, int>
     */
    private array $showOrders = [];

    #[\Override]
    protected function init()
    {
        $event = new ProviderCollecting();
        EventHandler::getInstance()->fire($event);
        foreach ($event->getProviders() as $providerName => $provider) {
            $this->providers[$providerName] = $provider;
            $this->showOrders[$providerName] = $event->getShowOrder($providerName);
        }

        foreach (ACPSearchProviderCacheBuilder::getInstance()->getData() as $acpSearchProvider) {
            if (isset($this->providers[$acpSearchProvider->providerName])) {
                continue;
            }

            $className = $acpSearchProvider->className;
            if (!\is_subclass_of($className, IACPSearchResultProvider::class)) {
                throw new ImplementationException($className, IACPSearchResultProvider::class);
            }

            $this->providers[$acpSearchProvider->providerName] = new $className();
            $this->showOrders[$acpSearchProvider->providerName] = (int)$acpSearchProvider->showOrder;
        }

        \uksort(
            $this->providers,
            fn(string $a, string $b) => $this->showOrders[$a] <=> $this->showOrders[$b]
        );
    }

    /**
     * Returns a list of search result collections for given query.
     *
     * @return  ACPSearchResultList[]
     */
    public function search(string $query, int $limit = 10, string $providerName = '')
    {
        $data = [];
        if ($providerName) {
            $maxResultsPerProvider = $limit;
        } else {
            $maxResultsPerProvider = \ceil($limit / 2);
        }
        $totalResultCount = 0;

        foreach ($this->providers as $name => $provider) {
            if ($providerName && $name !== $providerName) {
                continue;
            }

            $results = $provider->search($query);

            if (!empty($results)) {
                $resultList = new ACPSearchResultList($name);
                foreach ($results as $result) {
                    $resultList->addResult($result);
                }

                // sort list and reduce results
                $resultList->sort();
                $resultList->reduceResultsTo($maxResultsPerProvider);

                $data[] = $resultList;
                $totalResultCount += \count($resultList);
            }
        }

        // reduce results per collection until we match the limit
        while ($totalResultCount > $limit) {
            // calculate highest value
            $max = 0;
            foreach ($data as $resultList) {
                $max = \max($max, \count($resultList));
            }

            // remove one result per result list with hits the $max value
            foreach ($data as $index => $resultList) {
                // break if we hit the $limit during reduction
                if ($totalResultCount == $limit) {
                    break;
                }

                $count = \count($resultList);
                if ($count == $max) {
                    $resultList->reduceResults(1);
                    $totalResultCount--;

                    // the last element of this result was removed
                    if ($count == 1) {
                        unset($data[$index]);
                    }
                }
            }
        }

        // sort all result lists
        foreach ($data as $resultList) {
            $resultList->sort();
        }

        return $data;
    }

    /**
     * Returns the names of all registered ACP search providers in display order.
     *
     * @return string[]
     * @since 6.3
     */
    public function getProviderNames(): array
    {
        return \array_keys($this->providers);
    }

    /**
     * Returns a list of application abbreviations.
     *
     * @return  string[]
     */
    public function getAbbreviations(string $suffix = '')
    {
        if (empty($this->abbreviations)) {
            // append the 'WCF' pseudo application
            $this->abbreviations[] = 'wcf';

            // get running application
            $this->abbreviations[] = ApplicationHandler::getInstance()->getAbbreviation(
                ApplicationHandler::getInstance()->getActiveApplication()->packageID
            );

            // get dependent applications
            foreach (ApplicationHandler::getInstance()->getDependentApplications() as $application) {
                $this->abbreviations[] = ApplicationHandler::getInstance()->getAbbreviation($application->packageID);
            }
        }

        if (!empty($suffix)) {
            $abbreviations = [];
            foreach ($this->abbreviations as $abbreviation) {
                $abbreviations[] = $abbreviation . $suffix;
            }

            return $abbreviations;
        }

        return $this->abbreviations;
    }
}
