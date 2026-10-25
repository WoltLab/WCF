<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\command\media\UpdateMedia;
use wcf\data\media\Media;
use wcf\data\media\MediaBuilder;
use wcf\data\media\MediaList;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\media\MediaCategoryFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\WCF;

/**
 * Sets the category of one or more media files.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MediaSetCategoryAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $media = $this->getMedia($request);

        $form = $this->getForm();

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $categoryID = (int)$form->getData()['data']['categoryID'] ?: null;
            foreach ($media as $mediaFile) {
                new UpdateMedia(
                    MediaBuilder::forUpdate($mediaFile)->setCategoryID($categoryID)
                )();
            }

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    /**
     * @return array<int, Media>
     */
    private function getMedia(ServerRequestInterface $request): array
    {
        try {
            $parameters = Helper::mapQueryParameters(
                $request->getQueryParams(),
                <<<'EOT'
                    array {
                        ids: non-empty-list<positive-int>
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $mediaIDs = \array_unique($parameters['ids']);

        $mediaList = new MediaList();
        $mediaList->setObjectIDs($mediaIDs);
        $mediaList->readObjects();
        $media = $mediaList->getObjects();
        if (\count($media) !== \count($mediaIDs)) {
            throw new IllegalLinkException();
        }

        foreach ($media as $mediaFile) {
            if (!$mediaFile->canManage()) {
                throw new PermissionDeniedException();
            }
        }

        return $media;
    }

    private function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.media.setCategory')
        );
        $form->appendChildren([
            MediaCategoryFormField::create(),
        ]);
        $form->build();

        return $form;
    }
}
