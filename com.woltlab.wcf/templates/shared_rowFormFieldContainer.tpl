<div id="{$container->getPrefixedId()}Container" class="formField{foreach from=$container->getClasses() item='class'} {$class}{/foreach}"{*
	*}{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*} role="group"{if $container->getLabel() !== null} aria-labelledby="{$container->getPrefixedId()}Label"{/if}{if $container->getDescription() !== null} aria-describedby="{$container->getPrefixedId()}Description"{/if}{*
	*}{if !$container->checkDependencies()} style="display: none;"{/if}{*
*}>
	<div class="formField__label">{if $container->getLabel() !== null}<span id="{$container->getPrefixedId()}Label">{unsafe:$container->getLabel()}</span>{/if}</div>
	<div class="formField__input">
		<div class="row rowColGap formGrid">
			{foreach from=$container item='field'}
				{if $field->isAvailable()}
					<div id="{$field->getPrefixedId()}Container" class="formField__gridItem{foreach from=$field->getClasses() item='class'} {$class}{/foreach}"{foreach from=$field->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{if !$field->checkDependencies()} style="display: none;"{/if}>
						{if $field->getLabel() !== null && $field->getLabelTarget() !== null}
							<label for="{$field->getLabelTarget()}" class="visuallyHidden">{unsafe:$field->getLabel()}</label>
						{/if}
						{unsafe:$field->getFieldHtml()}

						{include file='shared_formFieldErrors'}
						{include file='shared_formFieldDependencies'}
						{include file='shared_formFieldDataHandler'}
					</div>
				{/if}
			{/foreach}
		</div>
	</div>
	{if $container->getDescription() !== null}
		<div id="{$container->getPrefixedId()}Description" class="formField__description">{unsafe:$container->getDescription()}</div>
	{/if}
</div>

{include file='shared_formContainerDependencies'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Default'], function(DefaultContainerDependency) {
		new DefaultContainerDependency('{unsafe:$container->getPrefixedId()|encodeJS}Container');
	});
</script>
