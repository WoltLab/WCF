<ul class="scrollableCheckboxList" {*
    *}id="lineBreakSeparatedTextOption_{@$option->optionID}"{*
    *}{if $values|empty} style="display: none"{/if}{*
*}>
    {foreach from=$values item=value}
        <li data-value="{$value}">
            <span class="icon icon16 fa-times jsDeleteItem jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}"></span>
            <span>{$value}</span>
        </li>
    {/foreach}
</ul>

<script data-relocate="true">
    require(['Language', 'WoltLabSuite/Core/Ui/ItemList/LineBreakSeparatedText'], (Language, { UiItemListLineBreakSeparatedText }) => {
        Language.addObject({
            'wcf.acp.option.type.lineBreakSeparatedText.placeholder': '{jslang}wcf.acp.option.type.lineBreakSeparatedText.placeholder{/jslang}',
            'wcf.acp.option.type.lineBreakSeparatedText.error.duplicate': '{jslang __literal=true}wcf.acp.option.type.lineBreakSeparatedText.error.duplicate{/jslang}',
            'wcf.acp.option.type.lineBreakSeparatedText.clearList.confirmMessage': '{jslang}wcf.acp.option.type.lineBreakSeparatedText.clearList.confirmMessage{/jslang}',
        });
        
        new UiItemListLineBreakSeparatedText(
            document.getElementById("lineBreakSeparatedTextOption_{@$option->optionID}"),
            {
                submitFieldName: "values[{$option->optionName}]"
            }
        );
    });
</script>
