<ul class="reactionSummaryList jsOnly" data-object-type="{$objectType}" data-object-id="{$objectID}">
	{if $reactionData[$objectID]|isset && $reactionData[$objectID]->getReactions()|is_array}
		{foreach from=$reactionData[$objectID]->getReactions() key=reactionTypeID item=reaction}
			<li class="reactCountButton jsTooltip" data-reaction-type-id="{$reactionTypeID}" title="{lang}wcf.reactions.summary.listReactions{/lang}">{@$reaction[renderedReactionIcon]} <span class="reactionCount">{$reaction[reactionCount]|shortUnit}</span></li>
		{/foreach}
	{/if}
</ul>