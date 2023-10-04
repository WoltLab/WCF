{foreach from=$messages item='status'}
	<div class="{$status->type->getClassName()}">{@$status->message}</div>
{/foreach}
