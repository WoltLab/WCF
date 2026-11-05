<div id="{$field->getPrefixedId()}Container" class="formField formField--inlineLabel{foreach from=$field->getClasses() item='class'} {$class}{/foreach}"{*
	*}{foreach from=$field->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if !$field->checkDependencies()} style="display: none;"{/if}{*
*}>
	<div class="formField__input">
		<label id="{$field->getPrefixedId()}Label" class="formField__inlineLabel">
			{unsafe:$field->getFieldHtml()}
			<span class="formField__inlineLabelText">{unsafe:$field->getLabel()}{if $field->isRequired() && $form->marksRequiredFields()} <span class="formField__required" aria-hidden="true">*</span>{/if}</span>
		</label>
	</div>

	{include file='shared_formFieldErrors'}
	{include file='shared_formFieldDescription'}
	{include file='shared_formFieldDependencies'}
	{include file='shared_formFieldDataHandler'}
</div>
