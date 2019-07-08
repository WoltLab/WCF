{if $__wcf->session->getPermission('user.like.canViewLike')}
	{assign var='_reactionSummaryListReactions' value=null}
        {if $reactionData[$objectID]|isset}
		{assign var='_reactionSummaryListReactions' value=$reactionData[$objectID]->getReactions()}
	{/if}
	<a href="#" class="reactionSummaryList{if $isTiny|isset && $isTiny} reactionSummaryListTiny{/if} jsOnly jsTooltip" data-object-type="{$objectType}" data-object-id="{$objectID}" title="{lang}wcf.reactions.summary.listReactions{/lang}"{if $_reactionSummaryListReactions|empty} style="display: none;"{/if}>
		{if !$_reactionSummaryListReactions|empty}
			{foreach from=$_reactionSummaryListReactions key=reactionTypeID item=reaction}
				<span class="reactCountButton" data-reaction-type-id="{@$reactionTypeID}">
					{@$reaction[renderedReactionIcon]}
					<span class="reactionCount">{$reaction[reactionCount]|shortUnit}</span>
				</span>
			{/foreach}
		{/if}
	</a>
{/if}
