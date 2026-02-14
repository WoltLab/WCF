<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\article\Article;
use wcf\http\Helper;
use wcf\command\article\DisableI18n;
use wcf\command\article\EnableI18n;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\LanguageItemFormNode;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Toggles articles between i18n and monolingual mode.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ToggleArticleI18nAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        WCF::getSession()->checkPermissions(['admin.content.article.canManageArticle']);

        try {
            $parameters = Helper::mapQueryParameters(
                $_GET,
                <<<'EOT'
                    array {
                        id: positive-int
                    }
                    EOT
            );
            $article = new Article($parameters['id']);

            if (!$article->getObjectID()) {
                throw new IllegalLinkException();
            }
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $this->assertUserCanToggleI18n($article);

        $form = $this->getForm($article);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData()['data'];
            if ($article->isMultilingual) {
                $command = new DisableI18n($article, LanguageFactory::getInstance()->getLanguage($data['languageID']));
                $command();
            } else {
                $command = new EnableI18n($article);
                $command();
            }

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function assertUserCanToggleI18n(Article $article): void
    {
        WCF::getSession()->checkPermissions(['admin.content.article.canManageArticle']);

        if (\count(LanguageFactory::getInstance()->getLanguages()) < 2 && !$article->isMultilingual) {
            throw new IllegalLinkException();
        }
    }

    private function getForm(Article $article): Psr15DialogForm
    {
        $phraseType = $article->isMultilingual ? "convertFromI18n" : "convertToI18n";

        $form = new Psr15DialogForm(
            LanguageItemEditAction::class,
            WCF::getLanguage()->getDynamicVariable("wcf.article.{$phraseType}.question")
        );
        $form->appendChild(
            LanguageItemFormNode::create('explanation')
                ->languageItem("wcf.article.{$phraseType}.description")
        );
        if ($article->isMultilingual) {
            $form->appendChild(
                RadioButtonFormField::create('languageID')
                    ->label('wcf.acp.article.i18n.source')
                    ->required()
                    ->options(LanguageFactory::getInstance()->getLanguages())
            );
        }

        $form->markRequiredFields(false);
        $form->build();

        return $form;
    }
}
