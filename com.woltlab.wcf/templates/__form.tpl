<script data-relocate="true">
	{* register form with dependency manager before any form field-related JavaScript code is executed below *}
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Manager'], function(FormBuilderFieldDependencyManager) {
		FormBuilderFieldDependencyManager.register('{@$form->getId()}');
	});
</script>

{if $form->hasValidationErrors() && $form->showsErrorMessage()}
	<p class="error" role="alert">{@$form->getErrorMessage()}</p>
{/if}

{if $form->showsSuccessMessage()}
	<p class="success">{@$form->getSuccessMessage()}</p>
{/if}

{if $form->isAjax()}
	<section id="{@$form->getId()}"{*
		*}{if !$form->getClasses()|empty} class="{implode from=$form->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
		*}{foreach from=$form->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}>
{else}
	<form method="{@$form->getMethod()}" {*
		*}action="{@$form->getAction()}" {*
		*}id="{@$form->getId()}"{*
		*}{if !$form->getClasses()|empty} class="{implode from=$form->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
		*}{foreach from=$form->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}>
{/if}
	{foreach from=$form item='child'}
		{if $child->isAvailable()}
			{@$child->getHtml()}
		{/if}
	{/foreach}
	
	{if !$form->getButtons()|empty}
		<div class="formSubmit">
			{foreach from=$form->getButtons() item=button}
				{@$button->getHtml()}
			{/foreach}
		</div>
	{/if}

{if $form->isAjax()}
	</section>
{else}
		{@SECURITY_TOKEN_INPUT_TAG}
	</form>
{/if}

<script data-relocate="true">
	{* after all dependencies have been added, check them *}
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Manager'], function(FormBuilderFieldDependencyManager) {
		FormBuilderFieldDependencyManager.checkDependencies();
	});
</script>
