<input type="hidden" {*
	*}id="{$field->getPrefixedId()}" {*
	*}name="{$field->getPrefixedId()}" {*
	*}value="{$field->getValue()}"{*
*}>
<template id="{$field->getPrefixedId()}_template">
	<span class="selectOptionsListItem__handle">{icon name='up-down'}</span>
	<div class="selectOptionsListItem__inputContainer">
		<input type="text" class="selectOptionsListItem__key" placeholder="{lang}wcf.form.selectOptions.key{/lang}" required>
		<span class="selectOptionsListItem__equals">{icon name='equals'}</span>
		<input type="text" class="selectOptionsListItem__value" placeholder="{lang}wcf.form.selectOptions.value{/lang}" required>
	</div>
	<button type="button" class="button small selectOptionsListItem__remove jsTooltip" title="{lang}wcf.global.button.delete{/lang}">
		{icon name='xmark'}
	</button>
</template>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/SelectOptions'], ({ setup }) => {
		{jsphrase name='wcf.form.selectOptions.addItem'}

		const availableLanguages = {unsafe:$availableLanguages|json};
		setup(document.getElementById('{unsafe:$field->getPrefixedId()|encodeJS}'), availableLanguages);
	});
</script>
