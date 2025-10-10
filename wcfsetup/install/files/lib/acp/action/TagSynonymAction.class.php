<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\tag\Tag;
use wcf\data\tag\TagList;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\command\tag\SetTagSynonym;
use wcf\system\WCF;

/**
 * Dialog form for setting tags as synonyms.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class TagSynonymAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!\MODULE_TAGGING) {
            throw new IllegalLinkException();
        }
        if (!WCF::getSession()->getPermission('admin.content.tag.canManageTag')) {
            throw new PermissionDeniedException();
        }

        try {
            $parameters = Helper::mapQueryParameters(
                $request->getQueryParams(),
                <<<'EOT'
                    array {
                        ids: array<positive-int>
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        if (\count($parameters['ids']) < 2) {
            throw new IllegalLinkException();
        }

        $tagList = new TagList();
        $tagList->setObjectIDs($parameters['ids']);
        $tagList->readObjects();

        $form = $this->getForm($tagList->getObjects());

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $tagID = $form->getData()["data"]["tagID"];
            (new SetTagSynonym(
                $tagList->search($tagID),
                $tagList->getObjects()
            ))();

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    /**
     * @param Tag[] $tags
     */
    private function getForm(array $tags): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            TagSynonymAction::class,
            WCF::getLanguage()->get('wcf.acp.tag.setAsSynonyms')
        );
        $form->appendChildren([
            FormContainer::create('section')
                ->label('wcf.acp.tag.setAsSynonyms.description')
                ->appendChildren([
                    RadioButtonFormField::create('tagID')
                        ->options(
                            \array_map(
                                static fn(Tag $tag) => $tag->name,
                                $tags,
                            )
                        )
                        ->required()
                ])
        ]);

        $form->build();

        return $form;
    }
}
