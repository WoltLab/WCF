<div id="{$field->getPrefixedId()}Container" class="formField{foreach from=$field->getClasses() item='class'} {$class}{/foreach}"{*
	*}{foreach from=$field->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if $field->isGroup()} role="group"{if $field->getLabel() !== null} aria-labelledby="{$field->getPrefixedId()}Label"{/if}{foreach from=$field->getAriaAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{/if}{*
	*}{if !$field->checkDependencies()} style="display: none;"{/if}{*
*}>
	<div class="formField__label">{*
		*}{if $field->getLabel() !== null}{*
			*}{if $field->isGroup()}{*
				*}<span id="{$field->getPrefixedId()}Label">{unsafe:$field->getLabel()}</span>{*
			*}{else}{*
				*}<label id="{$field->getPrefixedId()}Label" for="{$field->getLabelTarget()}">{unsafe:$field->getLabel()}</label>{*
			*}{/if}{*
			*}{if $field->isRequired() && $form->marksRequiredFields()} <span class="formField__required"{if !$field->isGroup()} aria-hidden="true"{/if}>*</span>{/if}{*
		*}{/if}{*
	*}</div>
	<div class="formField__input">
		{unsafe:$field->getFieldHtml()}
	</div>

	{include file='shared_formFieldErrors'}
	{include file='shared_formFieldDescription'}
	{include file='shared_formFieldDependencies'}
	{include file='shared_formFieldDataHandler'}
</div>
