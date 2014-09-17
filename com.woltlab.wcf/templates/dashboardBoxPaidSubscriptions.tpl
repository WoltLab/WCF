<header class="boxHeadline boxSubHeadline">
	<h2>{lang}wcf.dashboard.box.com.woltlab.wcf.paidSubscriptions{/lang}</h2>
</header>

<div class="container marginTop containerPadding">
	<ul class="paidSubscriptionTeaserList">
		{foreach from=$subscriptions item=subscription}
			<li>
				<div class="containerHeadline" title="{$subscription->description|language}">
					<h3>{$subscription->title|language}</h3>
					<small>{lang}wcf.paidSubscription.formattedCost{/lang}</small> 
				</div>
				
				<ul class="buttonList marginTopTiny">
					{foreach from=$subscription->getPurchaseButtons() item=button}
						<li>{@$button}</li>
					{/foreach}
				</ul>
			</li>
		{/foreach}
	</ul>
</div>
