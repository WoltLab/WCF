{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.menu.settings.paidSubscription{/lang} - {lang}wcf.user.menu.settings{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
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
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{include file='userMenuSidebar'}

{include file='header' sidebarOrientation='left'}

<header class="boxHeadline">
	<h1>{lang}wcf.user.menu.settings.paidSubscription{/lang}</h1>
</header>

{include file='userNotice'}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{if $subscriptions|count}
	<header class="boxHeadline boxSubHeadline">
		<h2>{lang}wcf.paidSubscription.availableSubscriptions{/lang}</h2>
	</header>
	
	{if PAID_SUBSCRIPTION_ENABLE_TOS_CONFIRMATION}
		<div class="container containerPadding marginTop">
			<label><input type="checkbox" id="tosConfirmed" name="tosConfirmed" value="1" /> {lang}wcf.paidSubscription.confirmTOS{/lang}</label>
		</div>
	{/if}
	
	<div class="container marginTop paidSubscriptionList">
		<ul class="containerList">
			{foreach from=$subscriptions item=subscription}
				<li>
					<div class="containerHeadline">
						<h3>{$subscription->title|language}</h3>
						<p>{@$subscription->description|language|newlineToBreak}</p>
						
						<p class="marginTopTiny">{lang}wcf.paidSubscription.formattedCost{/lang}</p>
						
						<ul class="buttonList marginTopTiny">
							{foreach from=$subscription->getPurchaseButtons() item=button}
								<li>{@$button}</li>
							{/foreach}
						</ul>
					</div>
				</li>
			{/foreach}
		</ul>
	</div>
{/if}
	
{if $userSubscriptions|count}
	<header class="boxHeadline boxSubHeadline">
		<h2>{lang}wcf.paidSubscription.purchasedSubscriptions{/lang}</h2>
	</header>
	
	<div class="container marginTop">
		<ul class="containerList">
			{foreach from=$userSubscriptions item=userSubscription}
				<li>
					<div class="containerHeadline">
						<h3>{$userSubscription->getSubscription()->title|language}</h3>
						<p>{@$userSubscription->getSubscription()->description|language|newlineToBreak}</p>
						
						{if $userSubscription->endDate}
							<p>{lang}wcf.paidSubscription.expires{/lang}: {@$userSubscription->endDate|time}</p>
						{/if}
					</div>
				</li>
			{/foreach}
		</ul>
	</div>
{/if}

{if !$subscriptions|count && !$userSubscriptions|count}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsBottom'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{include file='footer'}

</body>
</html>
