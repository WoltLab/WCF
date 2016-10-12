<ul class="containerBoxList tripleColumned">
	{foreach from=$subscriptions item=subscription}
		<li>
			<div class="containerHeadline" title="{$subscription->description|language}">
				<h3>{$subscription->title|language}</h3>
				<small>{lang}wcf.paidSubscription.formattedCost{/lang}</small>
			</div>
			
			{if !PAID_SUBSCRIPTION_ENABLE_TOS_CONFIRMATION}
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

{if PAID_SUBSCRIPTION_ENABLE_TOS_CONFIRMATION}
	<ul class="buttonList">
		<li><a class="button small" href="{link controller='PaidSubscriptionList'}{/link}">{lang}wcf.paidSubscription.button.moreInformation{/lang}</a></li>
	</ul>
{/if}