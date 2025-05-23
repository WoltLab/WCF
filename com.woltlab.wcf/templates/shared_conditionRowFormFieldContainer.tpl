<div id="{$container->getPrefixedId()}"{*
	*}{if !$container->getClasses()|empty} class="{implode from=$container->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
	*}{if !$container->checkDependencies()} style="display: none" {/if}{*
*}>
	<button type="button" class="button condition-remove">
		{icon name="xmark"}
	</button>
	<label class="condition-label" for="{$container->getPrefixedId()}">{unsafe:$container->getLabel()}</label>
	{foreach from=$container item='field'}
		{if $field->isAvailable()}
			<div class="condition-field"{*
				*}{if !$field->getClasses()|empty} class="{implode from=$field->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
				*}{foreach from=$field->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
				*}{if !$field->checkDependencies()} style="display: none" {/if}>
			{unsafe:$field->getFieldHtml()}

			{include file='shared_formFieldErrors'}
			{include file='shared_formFieldDependencies'}
			{include file='shared_formFieldDataHandler'}
			</div>
		{/if}
	{/foreach}
	<input type="hidden" name="{$container->getContainerId()}[{$container->getConditionIndex()}]" value="{$container->getConditionType()}" />
</div>
