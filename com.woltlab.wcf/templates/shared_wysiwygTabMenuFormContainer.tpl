<div id="{$container->getPrefixedId()}Container"{*
	*}{if !$container->getClasses()|empty} class="{implode from=$container->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if !$container->checkDependencies()} hidden{/if}>
	<nav class="messageTabMenuNavigation jsOnly">
		<ul>
			{foreach from=$container item='child'}
				{if $child->isAvailable()}
					<li data-name="{$child->getName()}"{if !$child->checkDependencies()} hidden{/if}>
						<button type="button">
							{if $child->getIcon()}{unsafe:$child->getIcon()->toHtml()}{/if}
							<span>{unsafe:$child->getLabel()}</span>
						</button>
					</li>
				{/if}
			{/foreach}
		</ul>
	</nav>

	{include file='shared_formContainerChildren'}
</div>

{include file='shared_formContainerDependencies'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/WysiwygTabMenu'], ({ WysiwygTabMenu }) => {
		new WysiwygTabMenu('{unsafe:$container->getPrefixedId()|encodeJS}Container');
	});
</script>
