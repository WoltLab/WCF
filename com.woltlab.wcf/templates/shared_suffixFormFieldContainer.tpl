<div id="{$element->getField()->getPrefixedId()}Container" class="formField{foreach from=$element->getField()->getClasses() item='class'} {$class}{/foreach}"{*
	*}{foreach from=$element->getField()->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if !$element->getField()->checkDependencies()} style="display: none;"{/if}{*
*}>
	<div class="formField__label">{if $element->getLabel() !== null}<label id="{$element->getField()->getPrefixedId()}Label" for="{$element->getField()->getPrefixedId()}">{unsafe:$element->getLabel()}</label>{/if}</div>
	<div class="formField__input">
		<div class="inputAddon">
			{unsafe:$element->getField()->getFieldHtml()}
			
			{if $element->getSuffixField() !== null && $element->getSuffixField()->isAvailable()}
				{if !$element->suffixHasSelectableOptions()}
					{if $element->getSuffixLabel() !== ''}
						<span class="inputSuffix">{unsafe:$element->getSuffixLabel()}</span>
					{/if}
				{else}
					<span class="inputSuffix dropdown" id="{$element->getSuffixField()->getPrefixedId()}_dropdown">
						<span class="dropdownToggle">{unsafe:$element->getSelectedSuffixOption()[label]} {icon name='caret-down' type='solid'}</span>
						
						<ul class="dropdownMenu">
							{foreach from=$element->getSuffixField()->getNestedOptions() item=__fieldNestedOption}
								<li{if ($element->getSuffixField()->getValue() == $__fieldNestedOption[value] && $__fieldNestedOption[isSelectable]) || !$__fieldNestedOption[isSelectable]} class="{if $element->getSuffixField()->getValue() == $__fieldNestedOption[value] && $__fieldNestedOption[isSelectable]}active{if !$__fieldNestedOption[isSelectable]} disabled{/if}{else}disabled{/if}"{/if} data-value="{$__fieldNestedOption[value]}" data-label="{$__fieldNestedOption[label]}"><span>{unsafe:'&nbsp;'|str_repeat:$__fieldNestedOption[depth] * 4}{unsafe:$__fieldNestedOption[label]}</span></li>
							{/foreach}
						</ul>
						<input type="hidden" id="{$element->getSuffixField()->getPrefixedId()}" name="{$element->getSuffixField()->getPrefixedId()}" value="{if $element->getSuffixField()->getValue() === null}{$element->getSelectedSuffixOption()[value]}{else}{$element->getSuffixField()->getValue()}{/if}" />
					</span>
					
					{include file='shared_formFieldDependencies' field=$element->getSuffixField()}
					{include file='shared_formFieldDataHandler' field=$element->getSuffixField()}
				{/if}
			{/if}
		</div>
	</div>
	
	{include file='shared_formFieldErrors' field=$element->getField()}
	
	{if $element->getSuffixField() !== null && $element->getSuffixField()->isAvailable()}
		{include file='shared_formFieldErrors' field=$element->getSuffixField()}
	{/if}
	
	{if $element->getDescription() !== null}
		<div id="{$element->getField()->getPrefixedId()}Description" class="formField__description">{unsafe:$element->getDescription()}</div>
	{/if}
	
	{include file='shared_formFieldDependencies' field=$element->getField()}
	{include file='shared_formFieldDataHandler' field=$element->getField()}
</div>

{if $element->getSuffixField() !== null && $element->getSuffixField()->isAvailable() && !$element->getSuffixField()->isImmutable() && $element->suffixHasSelectableOptions()}
<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Container/SuffixFormField'], function(FormBuilderSuffixFormFieldContainer) {
		new FormBuilderSuffixFormFieldContainer(
			'{unsafe:$element->getDocument()->getId()|encodeJS}',
			'{unsafe:$element->getSuffixField()->getPrefixedId()|encodeJS}'
		);
	});
</script>
{/if}
