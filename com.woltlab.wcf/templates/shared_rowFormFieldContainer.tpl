<dl id="{$container->getPrefixedId()}Container"{*
	*}{if !$container->getClasses()|empty} class="{implode from=$container->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if !$container->checkDependencies()} style="display: none;"{/if}{*
*}>
	<dt>{if $container->getLabel() !== null}<label for="{$container->getPrefixedId()}">{@$container->getLabel()}</label>{/if}</dt>
	<dd>
		<div class="row rowColGap formGrid">
			{foreach from=$container item='field'}
				{if $field->isAvailable()}
					<div id="{$field->getPrefixedId()}Container" {if !$field->getClasses()|empty} class="{implode from=$field->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{foreach from=$field->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{if !$field->checkDependencies()} style="display: none;"{/if}>
						{@$field->getFieldHtml()}
						
						{include file='__formFieldErrors'}
						{include file='__formFieldDependencies'}
						{include file='__formFieldDataHandler'}
					</div>
				{/if}
			{/foreach}
		</div>
		{if $container->getDescription() !== null}
			<small>{@$container->getDescription()}</small>
		{/if}
	</dd>
</dl>

{include file='shared_formContainerDependencies'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Default'], function(DefaultContainerDependency) {
		new DefaultContainerDependency('{@$container->getPrefixedId()|encodeJS}Container');
	});
</script>
