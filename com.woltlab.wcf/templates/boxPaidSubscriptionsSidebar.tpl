<ol class="sidebarList">
	{foreach from=$subscriptions item=subscription}
		<li class="sidebarListItem">
			<div class="sidebarListItem__content">
				<h3 class="sidebarListItem__title">
					{$subscription->getTitle()}
				</h3>
				
				<div class="sidebarListItem__description">
					{lang}wcf.paidSubscription.formattedCost{/lang}
				</div>
			</div>

			<div class="sidebarListItem__meta">
				<div class="sidebarListItem__meta__buttons">
					{if !PAID_SUBSCRIPTION_ENABLE_TOS_CONFIRMATION && $__wcf->user->canPurchasePaidSubscriptions()}
						<ul class="buttonList">
							{foreach from=$subscription->getPurchaseButtons() item=button}
								<li>{unsafe:$button}</li>
							{/foreach}
						</ul>
					{/if}
				</div>
			</div>
		</li>
	{/foreach}
</ol>

{if PAID_SUBSCRIPTION_ENABLE_TOS_CONFIRMATION && $__wcf->user->canPurchasePaidSubscriptions()}
	<a class="button small more" href="{link controller='PaidSubscriptionList'}{/link}">{lang}wcf.paidSubscription.button.moreInformation{/lang}</a>
{/if}
