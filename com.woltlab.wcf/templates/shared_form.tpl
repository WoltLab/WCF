<script data-relocate="true">
	{* register form with dependency manager before any form field-related JavaScript code is executed below *}
	require([
		'WoltLabSuite/Core/Form/Builder/Field/Dependency/Manager'
		{if $form->isAjax()}
			, 'WoltLabSuite/Core/Form/Builder/Manager'
		{/if}
	], function(
		FormBuilderFieldDependencyManager
		{if $form->isAjax()}
			, FormBuilderManager
		{/if}
	) {
		FormBuilderFieldDependencyManager.register('{@$form->getId()|encodeJS}');
		
		{if $form->isAjax()}
			FormBuilderManager.registerForm('{@$form->getId()|encodeJS}');
		{/if}
	});
</script>

{if $form->hasValidationErrors() && $form->showsErrorMessage()}
	<woltlab-core-notice type="error">{@$form->getErrorMessage()}</woltlab-core-notice>
{/if}

{if $form->showsSuccessMessage()}
	<woltlab-core-notice type="success">
		<span>{@$form->getSuccessMessage()}</span>
		{if !$objectEditLink|empty}
			<span>{lang}wcf.global.success.add.editCreatedObject{/lang}</span>
		{/if}
	</woltlab-core-notice>
{/if}

{if $form->isAjax()}
	<section id="{$form->getId()}"{*
		*}{if !$form->getClasses()|empty} class="{implode from=$form->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
		*}{foreach from=$form->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}>
{else}
	<form method="{@$form->getMethod()}" {*
		*}action="{$form->getAction()}" {*
		*}id="{$form->getId()}"{*
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
				{if $button->isAvailable()}
					{@$button->getHtml()}
				{/if}
			{/foreach}
		</div>
	{/if}

{if $form->isAjax()}
	</section>
{else}
		{csrfToken}
	</form>
{/if}

{if $form->needsRequiredFieldsInfo()}
	<div class="formFieldRequiredNotice">
		<p><span class="formFieldRequired">*</span> {lang}wcf.global.form.required{/lang}</p>

		{event name='requiredFieldsInfo'}
	</div>
{/if}

<script data-relocate="true">
	{* after all dependencies have been added, check them *}
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Manager'], function(FormBuilderFieldDependencyManager) {
		FormBuilderFieldDependencyManager.checkDependencies();
	});
</script>
