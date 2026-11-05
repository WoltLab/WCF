{if !$field->getValidationErrors()|empty}
	<div id="{$field->getPrefixedId()}Error" class="formField__errors">
		{foreach from=$field->getValidationErrors() item='validationError'}
			{unsafe:$validationError->getHtml()}
		{/foreach}
	</div>
{/if}
