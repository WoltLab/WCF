<?php

namespace wcf\system\view\grid\action;

use wcf\action\ApiAction;
use wcf\data\DatabaseObject;
use wcf\data\ITitledObject;
use wcf\system\request\LinkHandler;
use wcf\system\view\grid\AbstractGridView;
use wcf\system\WCF;
use wcf\util\StringUtil;

class DeleteAction implements IGridViewAction
{
    public function __construct(
        private readonly string $endpoint,
    ) {}

    #[\Override]
    public function render(mixed $row): string
    {
        \assert($row instanceof DatabaseObject);

        $endpoint = StringUtil::encodeHTML(
            LinkHandler::getInstance()->getControllerLink(ApiAction::class, ['id' => 'rpc']) .
                \sprintf($this->endpoint, $row->getObjectID())
        );
        $label = WCF::getLanguage()->get('wcf.global.button.delete');
        if ($row instanceof ITitledObject) {
            $objectName = StringUtil::encodeHTML($row->getTitle());
        } else {
            $objectName = '';
        }

        return <<<HTML
            <button type="button" data-action="delete" data-object-name="{$objectName}" data-endpoint="{$endpoint}">
                {$label}
            </button>
            HTML;
    }

    #[\Override]
    public function renderInitialization(AbstractGridView $gridView): ?string
    {
        $id = StringUtil::encodeJS($gridView->getID());

        return <<<HTML
            <script data-relocate="true">
                require(['WoltLabSuite/Core/Component/GridView/Action/Delete'], ({ setup }) => {
                    setup(document.getElementById('{$id}_table'));
                });
            </script>
            HTML;
    }
}
