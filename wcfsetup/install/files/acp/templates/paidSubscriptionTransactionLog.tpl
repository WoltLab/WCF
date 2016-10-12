{capture assign='pageTitle'}{lang}wcf.acp.paidSubscription.transactionLog{/lang}: {@$log->logID}{/capture}
{include file='header'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.paidSubscription.transactionLog{/lang}: {@$log->logID}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='PaidSubscriptionTransactionLogList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.paidSubscription.transactionLog.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.paidSubscription.transactionLog{/lang}: {@$log->logID}</h2>
	
	<dl>
		<dt>{lang}wcf.acp.paidSubscription.transactionLog.logMessage{/lang}</dt>
		<dd>{$log->logMessage}</dd>
		
		{if $log->userID}
			<dt>{lang}wcf.user.username{/lang}</dt>
			<dd><a href="{link controller='UserEdit' id=$log->userID}{/link}" title="{lang}wcf.acp.user.edit{/lang}">{$log->getUser()->username}</a></dd>
		{/if}
		
		{if $log->subscriptionID}
			<dt>{lang}wcf.acp.paidSubscription.subscription{/lang}</dt>
			<dd>{$log->getSubscription()->title|language}</dd>
		{/if}
		
		<dt>{lang}wcf.acp.paidSubscription.transactionLog.paymentMethod{/lang}</dt>
		<dd>{lang}wcf.payment.{@$log->getPaymentMethodName()}{/lang}</dd>
		
		<dt>{lang}wcf.acp.paidSubscription.transactionLog.transactionID{/lang}</dt>
		<dd>{$log->transactionID}</dd>
		
		<dt>{lang}wcf.acp.paidSubscription.transactionLog.logTime{/lang}</dt>
		<dd>{@$log->logTime|time}</dd>
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
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}
	
	<nav class="contentFooterNavigation">
		<ul>
			<li><a href="{link controller='PaidSubscriptionTransactionLogList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.paidSubscription.transactionLog.list{/lang}</span></a></li>
			
			{event name='contentFooterNavigation'}
		</ul>
	</nav>
</footer>

{include file='footer'}
