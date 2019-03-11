<script data-relocate="true">
	{* register form with dependency manager before any form field-related JavaScript code is executed below *}
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Manager'], function(FormBuilderFieldDependencyManager) {
		FormBuilderFieldDependencyManager.register('{@$form->getId()}');
	});
</script>

{if $form->getAction()}
	{capture assign='__formStart'}<form method="{@$form->getMethod()}" action="{@$form->getAction()}"{/capture}
	{assign var='__formEnd' value='</form>'}
{else}
	{assign var='__formStart' value='<section'}
	{assign var='__formEnd' value='</section>'}
{/if}

{@$__formStart} id="{@$form->getId()}"{if !$form->getClasses()|empty} class="{implode from=$form->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{foreach from=$form->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}>
	{foreach from=$form item='child'}
		{if $child->isAvailable()}
			{@$child->getHtml()}
		{/if}
	{/foreach}
	
	<div class="formSubmit">
		<button class="buttonPrimary">{lang}wcf.global.button.submit{/lang}</button>
		{if $form->isCancelable()}
			<button data-type="cancel">{lang}wcf.global.button.cancel{/lang}</button>
		{/if}
	</div>
{@$__formEnd}

<script data-relocate="true">
	{* after all dependencies have been added, check them *}
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Manager'], function(FormBuilderFieldDependencyManager) {
		FormBuilderFieldDependencyManager.checkDependencies();
	});
</script>
