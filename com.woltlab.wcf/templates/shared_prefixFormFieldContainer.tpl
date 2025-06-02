<dl id="{$container->getField()->getPrefixedId()}Container"{*
	*}{if !$container->getField()->getClasses()|empty} class="{implode from=$container->getField()->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$container->getField()->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if !$container->getField()->checkDependencies()} style="display: none
;"{/if}{*
*}>
	<dt>{if $container->getLabel() !== null}<label for="{$container->getField()->getPrefixedId()}">{unsafe:$container->getLabel()}</label>{if $container->getField()->isRequired() && $form->marksRequiredFields()} <span class="formFieldRequired">*</span>{/if}{/if}</dt>
	<dd>
		<div class="inputAddon">
			{if $container->getPrefixField() !== null && $container->getPrefixField()->isAvailable()}
				{if !$container->prefixHasSelectableOptions()}
					{if $container->getPrefixLabel() !== ''}
						<span class="inputPrefix">{unsafe:$container->getPrefixLabel()}</span>
					{/if}
				{else}
					<span class="inputPrefix dropdown" id="{$container->getPrefixField()->getPrefixedId()}_dropdown">
						<span class="dropdownToggle">{unsafe:$container->getSelectedPrefixOption()[label]} {icon name='caret-down' type='solid'}</span>

						<ul class="dropdownMenu">
							{foreach from=$container->getPrefixField()->getNestedOptions() item=__fieldNestedOption}
								<li{if ($container->getPrefixField()->getValue() == $__fieldNestedOption[value] && $__fieldNestedOption[isSelectable]) || !$__fieldNestedOption[isSelectable]} class="{if $container->getPrefixField()->getValue() == $__fieldNestedOption[value] && $__fieldNestedOption[isSelectable]}active{if !$__fieldNestedOption[isSelectable]} disabled{/if}{else}disabled{/if}"{/if} data-value="{$__fieldNestedOption[value]}" data-label="{$__fieldNestedOption[label]}"><span>{unsafe:'&nbsp;'|str_repeat:$__fieldNestedOption[depth] * 4}{unsafe:$__fieldNestedOption[label]}</span></li>
							{/foreach}
						</ul>
						<input type="hidden" id="{$container->getPrefixField()->getPrefixedId()}" name="{$container->getPrefixField()->getPrefixedId()}" value="{if $container->getPrefixField()->getValue() === null}{$container->getSelectedPrefixOption()[value]}{else}{$container->getPrefixField()->getValue()}{/if}" />
					</span>
					{include file='shared_formFieldDependencies' field=$container->getPrefixField()}
					{include file='shared_formFieldDataHandler' field=$container->getPrefixField()}
				{/if}
			{/if}

			{unsafe:$container->getField()->getFieldHtml()}
		</div>

		{if $container->getDescription() !== null}
			<small>{unsafe:$container->getDescription()}</small>
		{/if}

		{include file='shared_formFieldErrors' field=$container->getField()}

		{if $container->getPrefixField() !== null && $container->getPrefixField()->isAvailable()}
			{foreach from=$container->getPrefixField()->getValidationErrors() item='validationError'}
				{unsafe:$validationError->getHtml()}
			{/foreach}
		{/if}

		{include file='shared_formFieldDependencies' field=$container->getField()}
		{include file='shared_formFieldDataHandler' field=$container->getField()}
	</dd>
</dl>

{if $container->getPrefixField() !== null && $container->getPrefixField()->isAvailable() && !$container->getPrefixField()->isImmutable() && $container->prefixHasSelectableOptions()}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Form/Builder/Container/SuffixFormField'], function(FormBuilderPrefixFormFieldContainer) {
			new FormBuilderPrefixFormFieldContainer(
				'{unsafe:$container->getDocument()->getId()|encodeJS}',
				'{unsafe:$container->getPrefixField()->getPrefixedId()|encodeJS}',
			);
		});
	</script>
{/if}
