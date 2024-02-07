{if $__wcf->session->getPermission('user.like.canViewLike')}
	{assign var='__reactionSummaryJson' value='[]'}
	{if $reactionData[$objectID]|isset}
		{assign var='__reactionSummaryJson' value=$reactionData[$objectID]->getReactionsJson()}
	{/if}
	
	<woltlab-core-reaction-summary
		data="{$__reactionSummaryJson}"
		object-type="{$objectType}"
		object-id="{$objectID}"
		selected-reaction="{if $reactionData[$objectID]|isset && $reactionData[$objectID]->reactionTypeID}{$reactionData[$objectID]->reactionTypeID}{else}0{/if}"
	></woltlab-core-reaction-summary>
{/if}
