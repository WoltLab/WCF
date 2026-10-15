<?php

namespace wcf\system\endpoint\controller\core\devtools\notifications;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\notification\event\ITestableUserNotificationEvent;
use wcf\system\user\notification\TestableUserNotificationEventHandler;
use wcf\system\WCF;

/**
 * Tests a user notification event by rendering all possible notifications.
 * Testing creates test content, like users and comments, as a side effect.
 *
 * @author      Marcel Werk, Matthias Schmidt
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/devtools/notifications/{id:\d+}/test')]
final class TestNotificationEvent implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertDeveloperToolsAreAvailable();

        $event = Helper::fetchObjectFromRequestParameter($variables['id'], UserNotificationEvent::class);
        if (!\is_subclass_of($event->className, ITestableUserNotificationEvent::class)) {
            throw new UserInputException('id');
        }

        return new JsonResponse([
            'template' => $this->renderTestResults($event),
        ]);
    }

    private function renderTestResults(UserNotificationEvent $userNotificationEvent): string
    {
        $events = [];
        $errors = 0;
        $hasEmailSupport = false;

        $originalLanguage = WCF::getLanguage();
        $handler = TestableUserNotificationEventHandler::getInstance();

        foreach ($handler->getUserNotificationEvents($userNotificationEvent) as $event) {
            WCF::setLanguage($event->getLanguage()->languageID);

            $eventData = ['description' => $event->getTestCaseDescription()];

            try {
                $eventData['title'] = $event->getTitle();
            } catch (\Throwable $e) {
                $eventData['titleException'] = $this->getRenderedException($e);
                $errors++;
            }

            try {
                $eventData['message'] = $event->getMessage();
            } catch (\Throwable $e) {
                $eventData['messageException'] = $this->getRenderedException($e);
                $errors++;
            }

            try {
                $eventData['link'] = $event->getLink();
            } catch (\Throwable $e) {
                $eventData['linkException'] = $this->getRenderedException($e);
                $errors++;
            }

            if ($event->supportsEmailNotification()) {
                $hasEmailSupport = true;

                try {
                    $eventData['dailyEmail'] = $handler->getEmailBody($event, 'daily');
                } catch (\Throwable $e) {
                    $eventData['dailyEmailException'] = $this->getRenderedException($e);
                    $errors++;
                }

                // for instant emails, a notification can only be triggered once
                if ($event->getNotification()->timesTriggered === 1) {
                    try {
                        $eventData['instantEmail'] = $handler->getEmailBody($event, 'instant');
                    } catch (\Throwable $e) {
                        $eventData['instantEmailException'] = $this->getRenderedException($e);
                        $errors++;
                    }
                }
            }

            $events[] = $eventData;
        }

        if ($errors > 0 && \ob_get_level() > 0) {
            // discard any output generated before the exception occurred
            while (\ob_get_level()) {
                \ob_end_clean();
            }
        }

        WCF::setLanguage($originalLanguage->languageID);

        return WCF::getTPL()->render('wcf', 'shared_devtoolsNotificationTestDialog', [
            'events' => $events,
            'errors' => $errors,
            'hasEmailSupport' => $hasEmailSupport,
        ]);
    }

    private function getRenderedException(\Throwable $e): string
    {
        \wcf\functions\exception\logThrowable($e);

        return $e->getMessage();
    }

    private function assertDeveloperToolsAreAvailable(): void
    {
        if (\ENABLE_DEVELOPER_TOOLS === 0 || !WCF::getSession()->hasPermission('admin.configuration.package.canInstallPackage')) {
            throw new PermissionDeniedException();
        }
    }
}
