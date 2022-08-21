{if $__wcf->session->getPermission('user.like.canViewLike')}
	{assign var='__reactionSummaryJson' value='[]'}
    {if $reactionData[$objectID]|isset}
		{assign var='__reactionSummaryJson' value=$reactionData[$objectID]->getReactionsJson()}
	{/if}
	
	<reaction-summary data="{$__reactionSummaryJson}" object-type="{$objectType}" object-id="{$objectID}"></reaction-summary>

	<script>
		require(['WoltLabSuite/Core/Ui/Reaction/SummaryDetails'], function({ SummaryDetails }) {
			new SummaryDetails('{$objectType}', {$objectID});
		});
	</script>
{/if}
