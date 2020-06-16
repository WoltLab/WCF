{capture assign='headContent'}
	{if PAID_SUBSCRIPTION_ENABLE_TOS_CONFIRMATION}
		<script data-relocate="true">
			$(function() {
				$('#tosConfirmed').change(function () {
					if ($('#tosConfirmed').is(':checked')) {
						$('.paidSubscriptionList button').enable();
					}
					else {
						$('.paidSubscriptionList button').disable();
					}
				});
				$('#tosConfirmed').change();
			});
		</script>
		
		<noscript>
			<style type="text/css">
				.paidSubscriptionList button {
					display: none;
				}
			</style>
		</noscript>
	{/if}
{/capture}

{include file='userMenuSidebar'}

{include file='header'}

{if $subscriptions|count}
	<section class="section sectionContainerList paidSubscriptionList">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.paidSubscription.availableSubscriptions{/lang}</h2>
			{if PAID_SUBSCRIPTION_ENABLE_TOS_CONFIRMATION}
				<div class="sectionDescription"><label><input type="checkbox" id="tosConfirmed" name="tosConfirmed" value="1"> {lang}wcf.paidSubscription.confirmTOS{/lang}</label></div>
			{/if}
		</header>
	
		<ul class="containerList">
			{foreach from=$subscriptions item=subscription}
				<li>
					<div class="containerHeadline">
						<h3>{$subscription->getTitle()} <span class="badge label">{lang}wcf.paidSubscription.formattedCost{/lang}</span></h3>
						<div class="htmlContent">{@$subscription->getFormattedDescription()}</div>
					</div>
					
					<div class="containerContent">
						<ul class="buttonList">
							{foreach from=$subscription->getPurchaseButtons() item=button}
								<li>{@$button}</li>
							{/foreach}
						</ul>
					</div>
				</li>
			{/foreach}
		</ul>
	</section>
{/if}
	
{if $userSubscriptions|count}
	<section class="section sectionContainerList">
		<h2 class="sectionTitle">{lang}wcf.paidSubscription.purchasedSubscriptions{/lang}</h2>
	
		<ul class="containerList">
			{foreach from=$userSubscriptions item=userSubscription}
				<li>
					<div class="containerHeadline">
						<h3>{$userSubscription->getSubscription()->getTitle()}</h3>
						<div class="htmlContent">{@$userSubscription->getSubscription()->getFormattedDescription()}</div>
					</div>
					
					{if $userSubscription->endDate}
						<div class="containerContent">
							<dl class="plain inlineDataList">
								<dt>{lang}wcf.paidSubscription.expires{/lang}</dt>
								<dd>{@$userSubscription->endDate|time}</dd>
							</dl>
						</div>
					{/if}
				</li>
			{/foreach}
		</ul>
	</section>
{/if}

{if !$subscriptions|count && !$userSubscriptions|count}
	<p class="info" role="status">{lang}wcf.global.noItems{/lang}</p>
{/if}

<footer class="contentFooter">
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
