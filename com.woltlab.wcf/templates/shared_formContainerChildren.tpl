{foreach from=$container item='child'}
	{if $child->isAvailable()}
		{unsafe:$child->getHtml()}
	{/if}
{/foreach}
