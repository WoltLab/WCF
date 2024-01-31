<div id="{$container->getPrefixedId()}Container"{*
	*}{if !$container->getClasses()|empty} class="{implode from=$container->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if !$container->checkDependencies()} style="display: none;"{/if}{*
*}>
	<nav class="{if !$__tabMenuCSSClassName|empty}{@$__tabMenuCSSClassName}{else}tabMenu{/if}">
		<ul>
			{foreach from=$container item='child'}
				{if $child->isAvailable()}
					<li{if !$child->checkDependencies()} style="display: none;"{/if}><a{if $container->usesAnchors()} href="#{$child->getPrefixedId()|rawurlencode}Container"{/if}>{@$child->getLabel()}</a></li>
				{/if}
			{/foreach}
		</ul>
	</nav>

	{include file='shared_formContainerChildren'}
</div>

{include file='shared_formContainerDependencies'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/TabMenu'], function(TabMenuContainerDependency) {
		new TabMenuContainerDependency('{@$container->getPrefixedId()|encodeJS}Container');
	});
</script>
