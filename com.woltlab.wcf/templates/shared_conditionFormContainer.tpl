<section id="{$container->getPrefixedId()}Container"{*
	*}{if !$container->getClasses()|empty} class="{implode from=$container->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if !$container->checkDependencies()} style="display: none;"{/if}{*
*}>
	{if $container->getLabel() !== null}
		{if $container->getDescription() !== null}
			<header class="sectionHeader">
				<h2 class="sectionTitle">{unsafe:$container->getLabel()}{if $container->markAsRequired()} <span class="formFieldRequired">*</span>{/if}</h2>
				<p class="sectionDescription">{unsafe:$container->getDescription()}</p>
			</header>
		{else}
			<h2 class="sectionTitle">{unsafe:$container->getLabel()}{if $container->markAsRequired()} <span class="formFieldRequired">*</span>{/if}</h2>
		{/if}
	{/if}

	<div class="conditions" id="{$container->getPrefixedId()}Conditions">
		{include file='shared_formContainerChildren'}
	</div>

	<button type="button" class="button" id="{$container->getPrefixedId()}AddCondition">
        {lang}wcf.condition.add{/lang}
	</button>
</section>

{include file='shared_formContainerDependencies'}

<script data-relocate="true">
  require([
	'WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Default',
	'WoltLabSuite/Core/Form/Builder/Container/ConditionFormField'
  ], (DefaultContainerDependency, { ConditionFormField }) => {
	new DefaultContainerDependency('{unsafe:$container->getPrefixedId()|encodeJS}Container');
	{* TODO set dynamic index *}
	new ConditionFormField('{unsafe:$container->getPrefixedId()|encodeJS}', '{link controller="ConditionAdd" isACP=false provider=$container->getConditionProviderClass()}{/link}', 0);
  });
</script>
