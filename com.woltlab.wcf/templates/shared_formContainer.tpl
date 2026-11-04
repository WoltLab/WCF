<section id="{$container->getPrefixedId()}Container"{*
	*}{if !$container->getClasses()|empty} class="{implode from=$container->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if $container->getLabel() !== null} aria-labelledby="{$container->getPrefixedId()}Title"{/if}{*
	*}{if !$container->checkDependencies()} style="display: none;"{/if}{*
*}>
	{if $container->getLabel() !== null}
		<header class="formSection__header">
			<h2 id="{$container->getPrefixedId()}Title" class="formSection__title">{unsafe:$container->getLabel()}{if $container->markAsRequired()} <span class="formSection__required">*</span>{/if}</h2>
			{if $container->getDescription() !== null}
				<p class="formSection__description">{unsafe:$container->getDescription()}</p>
			{/if}
		</header>
	{/if}

	{include file='shared_formContainerChildren'}
</section>

{include file='shared_formContainerDependencies'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Default'], function(DefaultContainerDependency) {
		new DefaultContainerDependency('{unsafe:$container->getPrefixedId()|encodeJS}Container');
	});
</script>
