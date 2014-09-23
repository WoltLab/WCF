{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.menu.settings.paidSubscription{/lang} - {lang}wcf.user.menu.settings{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
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
	
	<div class="container marginTop">
		<ul class="containerList">
			{foreach from=$subscriptions item=subscription}
				<li>
					<div class="containerHeadline">
						<h3>{$subscription->title|language}</h3>
						<p>{$subscription->description|language}</p>
						
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
						<p>{$userSubscription->getSubscription()->description|language}</p>
						
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
