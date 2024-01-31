<input type="text" {*
	*}id="{$field->getPrefixedId()}" {*
	*}name="{$field->getPrefixedId()}"{*
	*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{if $field->isAutofocused()} autofocus{/if}{*
	*}{if $field->isImmutable()} disabled{/if}{*
	*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
*}>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/ItemList/Static'], function(UiItemListStatic) {
		UiItemListStatic.init(
			'{@$field->getPrefixedId()|encodeJS}',
			[{if $field->getValue() !== null && !$field->getValue()|empty}{implode from=$field->getValue() item=item}'{@$item|encodeJS}'{/implode}{/if}],
			{
				maxItems: {if $field->allowsMultiple()}{@$field->getMaximumMultiples()}{else}1{/if},
				submitFieldName: '{@$field->getPrefixedId()|encodeJS}[]'
			}
		);
	});
</script>
