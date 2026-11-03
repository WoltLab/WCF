<dl id="{$field->getPrefixedId()}Container"{*
	*}{if !$field->getClasses()|empty} class="{implode from=$field->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$field->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if !$field->checkDependencies()} style="display: none;"{/if}{*
*}>
	<dt></dt>
	<dd>
		<label>
			{unsafe:$field->getFieldHtml()}
			{unsafe:$field->getLabel()}{if $field->isRequired() && $form->marksRequiredFields()} <span class="formFieldRequired">*</span>{/if}
		</label>

		{include file='shared_formFieldErrors'}
		{include file='shared_formFieldDescription'}
		{include file='shared_formFieldDependencies'}
		{include file='shared_formFieldDataHandler'}
	</dd>
</dl>
