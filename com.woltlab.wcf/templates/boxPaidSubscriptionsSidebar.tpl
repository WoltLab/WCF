<ul class="sidebarBoxList">
	{foreach from=$subscriptions item=subscription}
		<li>
			<div class="sidebarBoxHeadline" title="{$subscription->description|language}">
				<h3>{$subscription->title|language}</h3>
				<small>{lang}wcf.paidSubscription.formattedCost{/lang}</small>
			</div>
			
			{if !PAID_SUBSCRIPTION_ENABLE_TOS_CONFIRMATION}
				<ul class="buttonList">
					{foreach from=$subscription->getPurchaseButtons() item=button}
						<li>{@$button}</li>
					{/foreach}
				</ul>
			{/if}
		</li>
	{/foreach}
</ul>

{if PAID_SUBSCRIPTION_ENABLE_TOS_CONFIRMATION}
	<a class="button small more" href="{link controller='PaidSubscriptionList'}{/link}">{lang}wcf.paidSubscription.button.moreInformation{/lang}</a>
{/if}