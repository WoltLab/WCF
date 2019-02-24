<div id="{@$container->getPrefixedId()}Container" {*
	*}{if !$container->getClasses()|empty}class="{implode from=$container->getClasses() item='class' glue=' '}{$class}{/implode}" {/if}{*
	*}{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
*}>
	<nav class="menu">
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
