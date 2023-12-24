{foreach from=$messages item='status'}
	<woltlab-core-notice type="{$status->type->getClassName()}">{@$status->message}</woltlab-core-notice>
{/foreach}
