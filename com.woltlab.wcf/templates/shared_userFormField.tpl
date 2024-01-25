<input type="text" {*
	*}id="{$field->getPrefixedId()}" {*
	*}name="{$field->getPrefixedId()}" {*
	*}value="{if $field->allowsMultiple()}{if $field->getValue()|is_array}{implode from=$field->getValue() item=username}{$username}{/implode}{/if}{else}{$field->getValue()}{/if}" {*
	*}class="long"{*
	*}{if $field->isAutofocused()} autofocus{/if}{*
	*}{if $field->isImmutable()} disabled{/if}{*
*}>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/ItemList/User'], function(UiItemListUser) {
		UiItemListUser.init('{@$field->getPrefixedId()|encodeJS}', {
			{if $field->getMaximumMultiples() !== -1}
				maxItems: {@$field->getMaximumMultiples()},
			{/if}
		});
	});
</script>
