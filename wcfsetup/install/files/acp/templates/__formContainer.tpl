<section id="{@$container->getPrefixedId()}" class="section{foreach from=$container->getClasses() item='class'} {$class}{/foreach}"{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}>
	{if $container->getLabel() !== null}
		{if $container->getDescription() !== null}
			<header class="sectionHeader">
				<h2 class="sectionTitle">{@$container->getLabel()}</h2>
				<p class="sectionDescription">{@$container->getDescription()}</p>
			</header>
		{else}
			<h2 class="sectionTitle">{@$container->getLabel()}</h2>
		{/if}
	{/if}
	
	{include file='__formContainerChildren'}
</section>
