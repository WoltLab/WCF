<script data-relocate="true">
	{* register form with dependency manager before any form field-related JavaScript code is executed below *}
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Manager'], function(FormBuilderFieldDependencyManager) {
		FormBuilderFieldDependencyManager.register('{@$form->getId()}');
	});
</script>

<form method="{@$form->getMethod()}" action="{@$form->getAction()}" id="{@$form->getId()}"{if !$form->getClasses()|empty} class="{implode from=$form->getClasses() item='class'}{$class}{/implode}"{/if}{foreach from=$form->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}>
	{foreach from=$form item='child'}
		{if $child->isAvailable()}
			{@$child->getHtml()}
		{/if}
	{/foreach}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

<script data-relocate="true">
	{* after all dependencies have been added, check them *}
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Manager'], function(FormBuilderFieldDependencyManager) {
		FormBuilderFieldDependencyManager.checkDependencies();
	});
</script>
