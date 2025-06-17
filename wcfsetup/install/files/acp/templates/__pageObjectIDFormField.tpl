<div class="inputAddon">
	<input type="number" {*
		*}step="{$field->getStep()}" {*
		*}id="{$field->getPrefixedId()}" {*
		*}name="{$field->getPrefixedId()}" {*
		*}value="{$field->getValue()}"{*
		*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
		*}{if $field->getAutoComplete() !== null} autocomplete="{$field->getAutoComplete()}"{/if}{*
		*}{if $field->isAutofocused()} autofocus{/if}{*
		*}{if $field->isRequired()} required{/if}{*
		*}{if $field->isImmutable()} disabled{/if}{*
		*}{if $field->getMinimum() !== null} min="{$field->getMinimum()}"{/if}{*
		*}{if $field->getMaximum() !== null} max="{$field->getMaximum()}"{/if}{*
		*}{if $field->getInputMode() !== null} inputmode="{$field->getInputMode()}"{/if}{*
		*}{if $field->getPlaceholder() !== null} placeholder="{$field->getPlaceholder()}"{/if}{*
		*}{if $field->getDocument()->isAjax()} data-dialog-submit-on-enter="true"{/if}{*
		*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}>
	<button type="button" id="{$field->getPrefixedId()}Search" class="inputSuffix button jsTooltip" title="{lang}wcf.page.pageObjectID.search{/lang}">
		{icon name='magnifying-glass'}
	</button>
</div>

<script data-relocate="true">
	{jsphrase name='wcf.page.pageObjectID'}
	{jsphrase name='wcf.page.pageObjectID.search.noResults'}
	{jsphrase name='wcf.page.pageObjectID.search.results'}
	{jsphrase name='wcf.page.pageObjectID.search.terms'}

	require(['Language', 'WoltLabSuite/Core/Acp/Ui/Menu/Item/Handler'], (Language, { AcpUiMenuItemHandler }) => {
		Language.addObject({
			{foreach from=$pageNodeList item=pageNode}
			{capture assign='pageObjectIDLanguageItem'}{lang __optional=true}wcf.page.pageObjectID.{$pageNode->identifier}{/lang}{/capture}
			{if $pageObjectIDLanguageItem}
				'wcf.page.pageObjectID.{$pageNode->identifier}': '{unsafe:$pageObjectIDLanguageItem|encodeJS}',
			{/if}
			{capture assign='pageObjectIDLanguageItem'}{lang __optional=true}wcf.page.pageObjectID.search.{$pageNode->identifier}{/lang}{/capture}
			{if $pageObjectIDLanguageItem}
				'wcf.page.pageObjectID.search.{$pageNode->identifier}': '{unsafe:$pageObjectIDLanguageItem|encodeJS}',
			{/if}
			{/foreach}
		});

		new AcpUiMenuItemHandler('{unsafe:$field->getPrefixedId()|encodeJS}', new Map([
			{implode from=$pageHandlers key=handlerPageID item=requireObjectID glue=', '}[{$handlerPageID}, {if $requireObjectID}true{else}false{/if}]{/implode}
		]), new Map([
			{foreach from=$pageNodeList item=pageNode}
				[{$pageNode->pageID}, '{unsafe:$pageNode->identifier|encodeJS}'],
			{/foreach}
		]));
	});
</script>
