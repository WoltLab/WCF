<?php

namespace wcf\system\endpoint\controller\core\exceptions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\ExceptionLogUtil;

/**
 * Retrieves the HTML code for the rendering of the exception log entry with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[GetRequest('/core/exceptions/{id:[a-f0-9]{40}}/render')]
final class RenderException implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertExceptionLogIsAccessible();

        $exception = $this->getException($variables['id']);
        if (!$exception) {
            throw new IllegalLinkException();
        }

        return new JsonResponse([
            'template' => WCF::getTPL()->render('wcf', 'shared_exceptionLogDetails', [
                'exception' => $exception,
                'exceptionID' => $variables['id'],
            ])
        ]);
    }

    private function assertExceptionLogIsAccessible(): void
    {
        if (!WCF::getSession()->getPermission('admin.management.canViewLog')) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @return ?mixed[]
     */
    private function getException(string $exceptionID): ?array
    {
        $logFile = $this->getLogFile($exceptionID);
        if (!$logFile) {
            return null;
        }

        try {
            $exceptions = ExceptionLogUtil::splitLog(\file_get_contents($logFile));

            return ExceptionLogUtil::parseException($exceptions[$exceptionID]);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getLogFile(string $exceptionID): ?string
    {
        $fileNameRegex = new Regex('(?:^|/)\d{4}-\d{2}-\d{2}\.txt$');
        $logFiles = DirectoryUtil::getInstance(WCF_DIR . 'log/', false)->getFiles(\SORT_DESC, $fileNameRegex);
        foreach ($logFiles as $logFile) {
            $pathname = WCF_DIR . 'log/' . $logFile;
            $contents = \file_get_contents($pathname);

            if (\str_contains($contents, '<<<<<<<<' . $exceptionID . '<<<<')) {
                return $pathname;
            }
        }

        return null;
    }
}
