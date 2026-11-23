{capture assign='pageTitle'}{lang}wcf.acp.paidSubscription.transactionLog{/lang}: {$log->logID}{/capture}
{include file='header'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.paidSubscription.transactionLog{/lang}: {$log->logID}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.paidSubscription.transactionLog{/lang}: {$log->logID}</h2>
	
	<dl>
		<dt>{lang}wcf.acp.paidSubscription.transactionLog.logMessage{/lang}</dt>
		<dd>{$log->logMessage}</dd>
		
		{if $log->userID}
			<dt>{lang}wcf.user.username{/lang}</dt>
			<dd><a href="{link controller='UserEdit' id=$log->userID}{/link}" title="{lang}wcf.acp.user.edit{/lang}">{$log->getUser()->username}</a></dd>
		{/if}
		
		{if $log->subscriptionID}
			<dt>{lang}wcf.acp.paidSubscription.subscription{/lang}</dt>
			<dd>{$log->getSubscription()->getTitle()}</dd>
		{/if}
		
		<dt>{lang}wcf.acp.paidSubscription.transactionLog.paymentMethod{/lang}</dt>
		<dd>{lang}wcf.payment.{$log->getPaymentMethodName()}{/lang}</dd>
		
		<dt>{lang}wcf.acp.paidSubscription.transactionLog.transactionID{/lang}</dt>
		<dd>{$log->transactionID}</dd>
		
		<dt>{lang}wcf.acp.paidSubscription.transactionLog.logTime{/lang}</dt>
		<dd>{time time=$log->logTime}</dd>
	</dl>
</section>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.paidSubscription.transactionLog.transactionDetails{/lang}</h2>

	<dl>
		{foreach from=$log->getTransactionDetails() key=key item=value}
			<dt>{$key}</dt>
			<dd>{$value}</dd>
		{/foreach}
	</dl>
</section>

{event name='sections'}

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
