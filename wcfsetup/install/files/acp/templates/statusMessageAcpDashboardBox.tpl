{foreach from=$messages item='status'}
	<div class="{$status->type}">{@$status->message}</div>
{/foreach}
