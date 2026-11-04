<section id="{$container->getPrefixedId()}Container"{*
	*}{if !$container->getClasses()|empty} class="{implode from=$container->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if $container->getLabel() !== null} aria-labelledby="{$container->getPrefixedId()}Title"{/if}{*
	*}{if !$container->checkDependencies()} style="display: none;"{/if}{*
*}>
	{if $container->getLabel() !== null}
		<header class="formSection__header">
			<h2 id="{$container->getPrefixedId()}Title" class="formSection__title">{unsafe:$container->getLabel()}{if $container->markAsRequired()} <span class="formSection__required">*</span>{/if}</h2>
			{if $container->getDescription() !== null}
				<p class="formSection__description">{unsafe:$container->getDescription()}</p>
			{/if}
		</header>
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
						{unsafe:$child->getHtml()}
					{/if}
				{/foreach}
			</tbody>
		</table>
	</div>
</section>

{include file='shared_formContainerDependencies'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Default'], function(DefaultContainerDependency) {
		new DefaultContainerDependency('{unsafe:$container->getPrefixedId()|encodeJS}Container');
	});
</script>
