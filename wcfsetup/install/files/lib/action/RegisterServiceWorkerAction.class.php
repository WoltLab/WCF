<?php

namespace wcf\action;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\service\worker\ServiceWorkerEditor;
use wcf\data\service\worker\ServiceWorkerList;
use wcf\http\Helper;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class RegisterServiceWorkerAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return new TextResponse('Unsupported', 400);
        }
        if (!WCF::getUser()->userID) {
            // Notification not sent to guests
            throw new PermissionDeniedException();
        }
        $parameters = Helper::mapRequestBody(
            $request->getParsedBody(),
            <<<'EOT'
                array {
                    endpoint: non-empty-string,
                    publicKey: non-empty-string,
                    authToken: non-empty-string,
                    contentEncoding: "aesgcm" | "aes128gcm",
                }
            EOT,
        );
        $serviceWorkerList = new ServiceWorkerList();
        $serviceWorkerList->getConditionBuilder()->add('userID = ?', [WCF::getUser()->userID]);
        $serviceWorkerList->readObjects();
        // Check if this service worker is already registered.
        foreach ($serviceWorkerList as $serviceWorker) {
            if ($serviceWorker->endpoint === $parameters['endpoint']) {
                // Update existing service worker
                $editor = new ServiceWorkerEditor($serviceWorker);
                $editor->update([
                    'publicKey' => $parameters['publicKey'],
                    'authToken' => $parameters['authToken'],
                    'contentEncoding' => $parameters['contentEncoding'],
                ]);
                return new EmptyResponse(204);
            }
        }
        ServiceWorkerEditor::fastCreate([
            'userID' => WCF::getUser()->userID,
            'endpoint' => $parameters['endpoint'],
            'publicKey' => $parameters['publicKey'],
            'authToken' => $parameters['authToken'],
            'contentEncoding' => $parameters['contentEncoding'],
        ]);

        return new EmptyResponse(204);
    }
}
