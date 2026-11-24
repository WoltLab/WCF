{if $__wcf->session->getPermission('user.like.canViewLike')}
	{assign var='__reactionSummaryJson' value='[]'}
	{if $reactionData[$objectID]|isset}
		{assign var='__reactionSummaryJson' value=$reactionData[$objectID]->getReactionsJson()}
	{/if}
	
	<woltlab-core-reaction-summary
		data="{$__reactionSummaryJson}"
		object-type="{$objectType}"
		object-id="{$objectID}"
	></woltlab-core-reaction-summary>
{/if}
