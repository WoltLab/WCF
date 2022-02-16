<input type="text" {*
	*}id="{@$field->getPrefixedId()}" {*
	*}name="{@$field->getPrefixedId()}" {*
	*}value="{implode from=$field->getUsers() item=fieldUser}{$fieldUser->username}{/implode}" {*
	*}class="long"{*
	*}{if $field->isAutofocused()} autofocus{/if}{*
	*}{if $field->isImmutable()} disabled{/if}{*
*}>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/ItemList/User'], function(UiItemListUser) {
		UiItemListUser.init('{@$field->getPrefixedId()}', {
			{if $field->getMaximumMultiples() !== -1}
				maxItems: {@$field->getMaximumMultiples()},
			{/if}
			callbackSetupValues() {
				return [
					{implode from=$field->getUsers() item=fieldUser}
						{
							value: {@$fieldUser->username|json},
							objectId: {$fieldUser->userID},
						}
					{/implode}
				];
			},
		});
	});
</script>
