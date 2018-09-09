{if $__wcf->session->getPermission('user.like.canViewLike')}
	<ul class="reactionSummaryList{if $isTiny|isset && $isTiny} reactionSummaryListTiny{/if} jsOnly" data-object-type="{$objectType}" data-object-id="{$objectID}">
		{if $reactionData[$objectID]|isset && $reactionData[$objectID]->getReactions()|is_array}
			{foreach from=$reactionData[$objectID]->getReactions() key=reactionTypeID item=reaction}
				<li class="reactCountButton jsTooltip" data-reaction-type-id="{$reactionTypeID}" title="{lang}wcf.reactions.summary.listReactions{/lang}"><span class="reactionCount">{$reaction[reactionCount]|shortUnit}</span> {@$reaction[renderedReactionIcon]}</li>
			{/foreach}
		{/if}
	</ul>
{/if}