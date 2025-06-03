{assign var='field' value=$container->getField()}
{assign var='prefixField' value=$container->getPrefixField()}

<dl id="{$field->getPrefixedId()}Container"{*
	*}{if !$field->getClasses()|empty} class="{implode from=$field->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$field->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if !$field->checkDependencies()} style="display: none
;"{/if}{*
*}>
	<dt>{if $container->getLabel() !== null}<label for="{$field->getPrefixedId()}">{unsafe:$container->getLabel()}</label>{if $field->isRequired() && $form->marksRequiredFields()} <span class="formFieldRequired">*</span>{/if}{/if}</dt>
	<dd>
		<div class="inputAddon">
			{if $prefixField->isAvailable()}
				{if !$container->prefixHasSelectableOptions()}
					<span class="inputPrefix">{unsafe:$prefixField->getFieldHtml()}</span>
				{else}
					<span class="inputPrefix dropdown" id="{$prefixField->getPrefixedId()}_dropdown">
						<span class="dropdownToggle">{unsafe:$container->getSelectedPrefixOption()[label]} {icon name='caret-down' type='solid'}</span>

						<ul class="dropdownMenu">
							{foreach from=$prefixField->getNestedOptions() item=__fieldNestedOption}
								<li{if ($prefixField->getValue() == $__fieldNestedOption[value] && $__fieldNestedOption[isSelectable]) || !$__fieldNestedOption[isSelectable]} class="{if $prefixField->getValue() == $__fieldNestedOption[value] && $__fieldNestedOption[isSelectable]}active{if !$__fieldNestedOption[isSelectable]} disabled{/if}{else}disabled{/if}"{/if} data-value="{$__fieldNestedOption[value]}" data-label="{$__fieldNestedOption[label]}"><span>{unsafe:'&nbsp;'|str_repeat:$__fieldNestedOption[depth] * 4}{unsafe:$__fieldNestedOption[label]}</span></li>
							{/foreach}
						</ul>
						<input type="hidden" id="{$prefixField->getPrefixedId()}" name="{$prefixField->getPrefixedId()}" value="{if $prefixField->getValue() === null}{$container->getSelectedPrefixOption()[value]}{else}{$prefixField->getValue()}{/if}" />
					</span>
				{/if}
				{include file='shared_formFieldDependencies' field=$prefixField sandbox=true}
				{include file='shared_formFieldDataHandler' field=$prefixField sandbox=true}
			{/if}
			{unsafe:$field->getFieldHtml()}
		</div>

		{if $container->getDescription() !== null}
			<small>{unsafe:$container->getDescription()}</small>
		{/if}

		{include file='shared_formFieldErrors' field=$field sandbox=true}

		{if $prefixField !== null && $prefixField->isAvailable()}
			{foreach from=$prefixField->getValidationErrors() item='validationError'}
				{unsafe:$validationError->getHtml()}
			{/foreach}
		{/if}

		{include file='shared_formFieldDependencies' field=$field sandbox=true}
		{include file='shared_formFieldDataHandler' field=$field sandbox=true}
	</dd>
</dl>

{if $prefixField->isAvailable() && !$prefixField->isImmutable() && $container->prefixHasSelectableOptions()}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Form/Builder/Container/SuffixFormField'], function(FormBuilderPrefixFormFieldContainer) {
			new FormBuilderPrefixFormFieldContainer(
				'{unsafe:$container->getDocument()->getId()|encodeJS}',
				'{unsafe:$prefixField->getPrefixedId()|encodeJS}',
			);
		});
	</script>
{/if}
