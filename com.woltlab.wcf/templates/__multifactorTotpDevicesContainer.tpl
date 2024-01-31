<section id="{$container->getPrefixedId()}Container"{*
	*}{if !$container->getClasses()|empty} class="{implode from=$container->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if !$container->checkDependencies()} style="display: none;"{/if}{*
*}>
	{if $container->getLabel() !== null}
		{if $container->getDescription() !== null}
			<header class="sectionHeader">
				<h2 class="sectionTitle">{@$container->getLabel()}{if $container->markAsRequired()} <span class="formFieldRequired">*</span>{/if}</h2>
				<p class="sectionDescription">{@$container->getDescription()}</p>
			</header>
		{else}
			<h2 class="sectionTitle">{@$container->getLabel()}{if $container->markAsRequired()} <span class="formFieldRequired">*</span>{/if}</h2>
		{/if}
	{/if}
	
	<div class="tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnText">{lang}wcf.user.security.multifactor.totp.deviceName{/lang}</th>
					<th class="columnText">{lang}wcf.user.security.multifactor.totp.createTime{/lang}</th>
					<th class="columnText">{lang}wcf.user.security.multifactor.totp.useTime{/lang}</th>
					<th></th>
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$container item='child'}
					{if $child->isAvailable()}
						{@$child->getHtml()}
					{/if}
				{/foreach}
			</tbody>
		</table>
	</div>
</section>

{include file='shared_formContainerDependencies'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Default'], function(DefaultContainerDependency) {
		new DefaultContainerDependency('{@$container->getPrefixedId()|encodeJS}Container');
	});
</script>
