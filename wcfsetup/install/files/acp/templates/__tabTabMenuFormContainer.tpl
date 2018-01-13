<div id="{@$container->getPrefixedId()}Container" class="tabMenuContainer tabMenuContent{foreach from=$container->getClasses() item='class'} {$class}{/foreach}"{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}>
	<nav class="menu">
		<ul>
			{foreach from=$container item='child'}
				<li><a href="{@$__wcf->getAnchor($child->getPrefixedId())}">{@$container->getLabel()}</a></li>
			{/foreach}
		</ul>
	</nav>
	
	{include file='__formContainerChildren'}
</div>

{include file='__formContainerDependencies'}
