<ul class="containerBoxList tripleColumned">
	{foreach from=$subscriptions item=subscription}
		<li>
			<div class="containerHeadline">
				<h3>{$subscription->getTitle()}</h3>
				<small>{lang}wcf.paidSubscription.formattedCost{/lang}</small>
			</div>
			
			{if !PAID_SUBSCRIPTION_ENABLE_TOS_CONFIRMATION && $__wcf->user->canPurchasePaidSubscriptions()}
				<div class="containerContent">
					<ul class="buttonList">
						{foreach from=$subscription->getPurchaseButtons() item=button}
							<li>{@$button}</li>
						{/foreach}
					</ul>
				</div>
			{/if}
		</li>
	{/foreach}
</ul>

{if PAID_SUBSCRIPTION_ENABLE_TOS_CONFIRMATION && $__wcf->user->canPurchasePaidSubscriptions()}
	<ul class="buttonList">
		<li><a class="button small" href="{link controller='PaidSubscriptionList'}{/link}">{lang}wcf.paidSubscription.button.moreInformation{/lang}</a></li>
	</ul>
{/if}
