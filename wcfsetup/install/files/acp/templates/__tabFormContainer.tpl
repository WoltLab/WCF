<div id="{@$container->getPrefixedId()}Container" class="tabMenuContent{foreach from=$container->getClasses() item='class'} {$class}{/foreach}"{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{if !$container->checkDependencies()} style="display: none;"{/if}>
	{include file='__formContainerChildren'}
</div>

{include file='__formContainerDependencies'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Tab'], function(TabContainerDependency) {
		new TabContainerDependency('{@$container->getPrefixedId()}Container');
	});
</script>
