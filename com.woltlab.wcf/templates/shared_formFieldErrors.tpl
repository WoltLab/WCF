{foreach from=$field->getValidationErrors() item='validationError'}
	{unsafe:$validationError->getHtml()}
{/foreach}
