{foreach from=$labelPickerGroup item=labelPicker}
    <dt><label for="{$labelPicker->getElementID()}">{$labelPicker->labelGroup->getTitle()}</label></dt>
    <dd>
        {@$labelPicker->toHtml()}
    </dd>
{/foreach}
