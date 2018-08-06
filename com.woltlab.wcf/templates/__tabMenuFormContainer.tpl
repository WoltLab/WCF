<div id="{@$container->getPrefixedId()}Container" class="section tabMenuContainer{foreach from=$container->getClasses() item='class'} {$class}{/foreach}"{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{if !$container->checkDependencies()} style="display: none;"{/if}{if !$container->checkDependencies()} style="display: none;"{/if}>
	<nav class="tabMenu">
		<ul>
			{foreach from=$container item='child'}
				{if $child->isAvailable()}
					{assign var='__tabMenuFormContainerChildId' value=$child->getPrefixedId()|concat:'Container'}
					<li{if !$child->checkDependencies()} style="display: none;"{/if}><a href="{@$__wcf->getAnchor($__tabMenuFormContainerChildId)}">{@$child->getLabel()}</a></li>
				{/if}
			{/foreach}
		</ul>
	</nav>
	
	{include file='__formContainerChildren'}
</div>

{include file='__formContainerDependencies'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/TabMenu'], function(TabMenuContainerDependency) {
		new TabMenuContainerDependency('{@$container->getPrefixedId()}Container');
	});
</script>
