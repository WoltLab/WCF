<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\flood\FloodControl;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\moderation\queue\ModerationQueueReportManager;
use wcf\system\WCF;
use wcf\util\HtmlString;

/**
 * Reports an object to be reviewed by a moderator.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class ReportAction implements RequestHandlerInterface
{
    /**
     * @var int
     */
    private const ALLOWED_REPORTS_PER_10M = 10;

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    objectType: non-empty-string,
                    objectID: positive-int
                }
                EOT
        );

        $objectType = $parameters['objectType'];
        $objectID = $parameters['objectID'];

        $this->assertCanBeReported($objectType, $objectID);

        $form = $this->getForm();

        if ($request->getMethod() === 'GET') {
            if (ModerationQueueReportManager::getInstance()->hasPendingReport($objectType, $objectID)) {
                throw new NamedUserException(HtmlString::fromSafeHtml(WCF::getLanguage()->getDynamicVariable('wcf.moderation.report.alreadyReported')));
            }

            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData()['data'];

            // if the specified content was already reported, e.g. a different user reported this
            // item meanwhile, silently ignore it. Just display a success and the user is happy :)
            if (!ModerationQueueReportManager::getInstance()->hasPendingReport($objectType, $objectID)) {
                ModerationQueueReportManager::getInstance()->addReport(
                    $objectType,
                    $objectID,
                    $data['reason']
                );
            }

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function assertCanBeReported(string $objectType, int $objectID): void
    {
        WCF::getSession()->checkPermissions(['user.profile.canReportContent']);

        if (!ModerationQueueReportManager::getInstance()->isValid($objectType, $objectID)) {
            throw new IllegalLinkException();
        }
        if (!ModerationQueueReportManager::getInstance()->canReport($objectType, $objectID)) {
            throw new PermissionDeniedException();
        }

        $requests = FloodControl::getInstance()->countContent(
            'com.woltlab.wcf.moderation.report',
            new \DateInterval('PT10M')
        );
        if ($requests['count'] >= self::ALLOWED_REPORTS_PER_10M) {
            throw new NamedUserException(
                HtmlString::fromSafeHtml(
                    WCF::getLanguage()->getDynamicVariable('wcf.page.error.flood')
                )
            );
        }
    }

    private function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            self::class,
            WCF::getLanguage()->get('wcf.moderation.report.reportContent')
        );

        $form->appendChildren([
            MultilineTextFormField::create('reason')
                ->label('wcf.moderation.report.reason')
                ->description('wcf.moderation.report.reason.description')
                ->maximumLength(64000)
                ->required(),
        ]);

        $form->markRequiredFields(false);
        $form->build();

        return $form;
    }
}
