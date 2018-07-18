{include file='__formFieldHeader'}

<input type="text" id="{@$field->getPrefixedId()}" name="{@$field->getPrefixedId()}" value="{$field->getValue()}" class="long"{if $field->isAutofocused()} autofocus{/if}{if $field->isImmutable()} disabled{/if}>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/ItemList/Static'], function(UiItemListStatic) {
		UiItemListStatic.init(
			'{@$field->getPrefixedId()}',
			[{if $field->getValue() !== null && !$field->getValue()|empty}{implode from=$field->getValue() item=item}'{@$item|encodeJS}'{/implode}{/if}],
			{
				submitFieldName: '{@$field->getPrefixedId()}[]'
			}
		);
	});
</script>

{include file='__formFieldFooter'}
