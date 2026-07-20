<?php

namespace wcf\form;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\data\article\Article;

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
    public string $objectEditLinkController = ArticleEditForm::class;

    #[\Override]
    protected function afterSave(): void
    {
        parent::afterSave();

        \assert($this->object instanceof Article);

        if ($this->object->publicationStatus === Article::PUBLISHED) {
            $this->setPsr7Response(new RedirectResponse($this->object->getLink(), 303));
        }
    }
}
