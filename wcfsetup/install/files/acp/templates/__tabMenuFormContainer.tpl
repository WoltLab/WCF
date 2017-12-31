<div id="{@$container->getPrefixedId()}" class="section tabMenuContainer{foreach from=$container->getClasses() item='class'} {$class}{/foreach}"{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}>
	<nav class="tabMenu">
		<ul>
			{foreach from=$container item='child'}
				<li><a href="{@$__wcf->getAnchor($child->getPrefixedId())}">{@$child->getLabel()}</a></li>
			{/foreach}
		</ul>
	</nav>
	
	{include file='__formContainerChildren'}
</div>
