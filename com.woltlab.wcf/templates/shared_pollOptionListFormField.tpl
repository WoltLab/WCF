<input type="hidden" {*
	*}id="{$field->getPrefixedId()}" {*
	*}name="{$field->getPrefixedId()}" {*
	*}value="{$field->getValue()|json}"{*
*}>
<template id="{$field->getPrefixedId()}_template">
	<span class="pollOptionListItem__handle">{icon name='grip-vertical'}</span>
	<input type="text" class="long pollOptionListItem__value" maxlength="255">
	<button type="button" class="button small pollOptionListItem__remove jsTooltip" title="{lang}wcf.poll.button.removeOption{/lang}">
		{icon name='xmark'}
	</button>
</template>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Component/Poll/OptionList'], ({ setup }) => {
		{jsphrase name='wcf.poll.button.addOption'}

		setup(
			document.getElementById('{unsafe:$field->getPrefixedId()|encodeJS}'),
			{unsafe:$availableLanguages|json},
			{POLL_MAX_OPTIONS}
		);
	});
</script>
