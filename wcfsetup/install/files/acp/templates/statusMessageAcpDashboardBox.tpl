{foreach from=$messages item='status'}
	<woltlab-core-notice type="{$status->type->getClassName()}">{unsafe:$status->message}</woltlab-core-notice>
{/foreach}
