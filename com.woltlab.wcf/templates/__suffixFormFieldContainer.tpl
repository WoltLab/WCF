<dl id="{$element->getField()->getPrefixedId()}Container"{*
	*}{if !$element->getField()->getClasses()|empty} class="{implode from=$element->getField()->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$element->getField()->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if !$element->getField()->checkDependencies()} style="display: none;"{/if}{*
*}>
	<dt>{if $element->getLabel() !== null}<label for="{$element->getField()->getPrefixedId()}">{@$element->getLabel()}</label>{/if}</dt>
	<dd>
		<div class="inputAddon">
			{@$element->getField()->getFieldHtml()}
			
			{if $element->getSuffixField() !== null && $element->getSuffixField()->isAvailable()}
				{if !$element->suffixHasSelectableOptions()}
					{if $element->getSuffixLabel() !== ''}
						<span class="inputSuffix">{@$element->getSuffixLabel()}</span>
					{/if}
				{else}
					<span class="inputSuffix dropdown" id="{$element->getSuffixField()->getPrefixedId()}_dropdown">
						<span class="dropdownToggle">{@$element->getSelectedSuffixOption()[label]} {icon name='caret-down' type='solid'}</span>
						
						<ul class="dropdownMenu">
							{foreach from=$element->getSuffixField()->getNestedOptions() item=__fieldNestedOption}
								<li{if ($element->getSuffixField()->getValue() == $__fieldNestedOption[value] && $__fieldNestedOption[isSelectable]) || !$__fieldNestedOption[isSelectable]} class="{if $element->getSuffixField()->getValue() == $__fieldNestedOption[value] && $__fieldNestedOption[isSelectable]}active{if !$__fieldNestedOption[isSelectable]} disabled{/if}{else}disabled{/if}"{/if} data-value="{$__fieldNestedOption[value]}" data-label="{$__fieldNestedOption[label]}"><span>{@'&nbsp;'|str_repeat:$__fieldNestedOption[depth] * 4}{@$__fieldNestedOption[label]}</span></li>
							{/foreach}
						</ul>
						<input type="hidden" id="{$element->getSuffixField()->getPrefixedId()}" name="{$element->getSuffixField()->getPrefixedId()}" value="{if $element->getSuffixField()->getValue() === null}{$element->getSelectedSuffixOption()[value]}{else}{$element->getSuffixField()->getValue()}{/if}" />
					</span>
					
					{include file='__formFieldDependencies' field=$element->getSuffixField()}
					{include file='__formFieldDataHandler' field=$element->getSuffixField()}
				{/if}
			{/if}
		</div>
		
		{if $element->getDescription() !== null}
			<small>{@$element->getDescription()}</small>
		{/if}
		
		{include file='__formFieldErrors' field=$element->getField()}
		
		{if $element->getSuffixField() !== null && $element->getSuffixField()->isAvailable()}
			{foreach from=$element->getSuffixField()->getValidationErrors() item='validationError'}
				{@$validationError->getHtml()}
			{/foreach}
		{/if}
		
		{include file='__formFieldDependencies' field=$element->getField()}
		{include file='__formFieldDataHandler' field=$element->getField()}
	</dd>
</dl>

{if $element->getSuffixField() !== null && $element->getSuffixField()->isAvailable() && !$element->getSuffixField()->isImmutable() && $element->suffixHasSelectableOptions()}
<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Container/SuffixFormField'], function(FormBuilderSuffixFormFieldContainer) {
		new FormBuilderSuffixFormFieldContainer(
			'{@$element->getDocument()->getId()|encodeJS}',
			'{@$element->getSuffixField()->getPrefixedId()|encodeJS}'
		);
	});
</script>
{/if}
