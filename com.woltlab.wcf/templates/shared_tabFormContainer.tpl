<div id="{$container->getPrefixedId()}Container"{*
	*}{if !$container->getClasses()|empty} class="{implode from=$container->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if !$container->checkDependencies()} style="display: none;"{/if}{*
*}>
	{include file='__formContainerChildren'}
</div>

{include file='__formContainerDependencies'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Tab'], function(TabContainerDependency) {
		new TabContainerDependency('{$container->getPrefixedId()|encodeJS}Container');
	});
</script>
