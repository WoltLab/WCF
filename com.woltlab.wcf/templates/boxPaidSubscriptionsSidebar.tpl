<ul class="sidebarItemList">
	{foreach from=$subscriptions item=subscription}
		<li>
			<div class="sidebarItemTitle">
				<h3>{$subscription->getTitle()}</h3>
				<small>{lang}wcf.paidSubscription.formattedCost{/lang}</small>
			</div>
			
			{if !PAID_SUBSCRIPTION_ENABLE_TOS_CONFIRMATION && $__wcf->user->canPurchasePaidSubscriptions()}
				<ul class="buttonList">
					{foreach from=$subscription->getPurchaseButtons() item=button}
						<li>{@$button}</li>
					{/foreach}
				</ul>
			{/if}
		</li>
	{/foreach}
</ul>

{if PAID_SUBSCRIPTION_ENABLE_TOS_CONFIRMATION && $__wcf->user->canPurchasePaidSubscriptions()}
	<a class="button small more" href="{link controller='PaidSubscriptionList'}{/link}">{lang}wcf.paidSubscription.button.moreInformation{/lang}</a>
{/if}
