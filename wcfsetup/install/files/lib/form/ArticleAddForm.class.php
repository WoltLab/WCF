<?php

namespace wcf\form;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\data\article\Article;
use wcf\system\WCF;

/**
 * Shows the article add form.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
class ArticleAddForm extends \wcf\acp\form\ArticleAddForm
{
    /**
     * @inheritDoc
     */
    public $objectEditLinkController = ArticleEditForm::class;

    #[\Override]
    public function save(): void
    {
        parent::save();

        /** @var Article $article */
        $article = $this->objectAction->getReturnValues()['returnValues'];
        if ($article->publicationStatus === Article::PUBLISHED) {
            $this->setPsr7Response(new RedirectResponse($article->getLink(), 303));
        }
    }
}
