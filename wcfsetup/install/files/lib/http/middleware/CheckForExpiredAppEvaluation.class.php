<?php

namespace wcf\http\middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\error\HtmlErrorRenderer;
use wcf\system\application\ApplicationHandler;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Checks whether the accessed app is an evaluation version that is expired.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class CheckForExpiredAppEvaluation implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        [$abbreviation] = \explode('\\', RequestHandler::getInstance()->getActiveRequest()->getClassName(), 2);

        if ($abbreviation !== 'wcf') {
            $application = ApplicationHandler::getInstance()->getApplication($abbreviation);
            $applicationObject = WCF::getApplicationObject($application);
            $endDate = $applicationObject->getEvaluationEndDate();

            if ($endDate && $endDate < TIME_NOW) {
                $package = $application->getPackage();

                $pluginStoreFileID = $applicationObject->getEvaluationPluginStoreID();
                $isWoltLab = false;
                if ($pluginStoreFileID === 0 && \str_starts_with($package->package, 'com.woltlab.')) {
                    $isWoltLab = true;
                }

                return new HtmlResponse(
                    (new HtmlErrorRenderer())->renderHtmlMessage(
                        WCF::getLanguage()->getDynamicVariable('wcf.global.error.title'),
                        WCF::getLanguage()->getDynamicVariable(
                            'wcf.acp.package.evaluation.expired',
                            [
                                'packageName' => $package->getName(),
                                'pluginStoreFileID' => $pluginStoreFileID,
                                'isWoltLab' => $isWoltLab,
                            ]
                        ),
                    ),
                    503
                );
            }
        }

        return $handler->handle($request);
    }
}
