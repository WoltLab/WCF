{foreach from=$container item='child'}
	{if $child->isAvailable()}
		{@$child->getHtml()}
	{/if}
{/foreach}