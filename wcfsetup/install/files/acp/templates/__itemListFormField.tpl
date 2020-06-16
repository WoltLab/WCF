<input type="text" id="{@$field->getPrefixedId()}" name="{@$field->getPrefixedId()}" class="long"{if $field->isAutofocused()} autofocus{/if}{if $field->isImmutable()} disabled{/if}>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/ItemList/Static'], function(UiItemListStatic) {
		UiItemListStatic.init(
			'{@$field->getPrefixedId()}',
			[{if $field->getValue() !== null && !$field->getValue()|empty}{implode from=$field->getValue() item=item}'{@$item|encodeJS}'{/implode}{/if}],
			{
				maxItems: {if $field->allowsMultiple()}{@$field->getMaximumMultiples()}{else}1{/if},
				submitFieldName: '{@$field->getPrefixedId()}[]'
			}
		);
	});
</script>
