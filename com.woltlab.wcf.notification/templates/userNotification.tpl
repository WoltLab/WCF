{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.notification.title{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>

	{include file='headInclude' sandbox=false}
	{include file='imageViewer'}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
	<link rel="alternate" type="application/rss+xml" href="index.php?page=UserNotificationFeed&amp;format=rss2" title="{lang}wcf.user.notification.feed{/lang} (RSS2)" />
	<link rel="alternate" type="application/atom+xml" href="index.php?page=UserNotificationFeed&amp;format=atom" title="{lang}wcf.user.notification.feed{/lang} (Atom)" />
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	{include file="userCPHeader"}

	<div class="border tabMenuContent">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.user.notification.title{/lang}</h3>
			{if $notifications|count}
				<div class="contentHeader">
					{pages print=true assign=pagesLinks link="index.php?page=UserNotification&pageNo=%d"|concat:SID_ARG_2ND_NOT_ENCODED}
				</div>

				<div class="border titleBarPanel">
					<div class="containerHead">
						<h4>{lang}wcf.user.notification.stats{/lang}</h4>
					</div>
				</div>
				<div class="border borderMarginRemove">
					<table class="tableList">
						<thead>
							<tr class="tableHead">
								<th class="columnIcon">
										<div>
											<span class="emptyHead">{lang}wcf.user.notification.icon{/lang}</span>
										</div>
								</th>
								<th class="columnText">
										<div>
											<span class="emptyHead">{lang}wcf.user.notification.text{/lang}</span>
										</div>
								</th>
								<th class="columnText">
										<div>
											<span class="emptyHead">{lang}wcf.user.notification.time{/lang}</span>
										</div>
								</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$notifications item=notification}
								{assign var=languageCategory value=$notification->event->languageCategory}
								{assign var=eventName value=$notification->eventName}
								<tr class="{cycle values='container-1,container-2'}">
									<td class="columnIcon">
										<img src="{icon}{$notification->event->icon}M.png{/icon}" alt="" />
									</td>
									<td class="columnText{if !$notification->confirmed} new{/if}">
										<span>{@$notification->longOutput}</span>
										{if !$notification->confirmed && $notification->event->requiresConfirmation && $notification->event->acceptURL}
										{assign var=acceptURL value=$notification->event->getAcceptURL($notification)}
										{assign var=declineURL value=$notification->event->getDeclineURL($notification)}
										<div class="buttons" style="float: right;">
											{if $acceptURL}
												<a href="{@$acceptURL}" title="{lang}{$languageCategory}.{$eventName}.accept{/lang}"><img src="{icon}checkS.png{/icon}" alt="{lang}{$languageCategory}.{$eventName}.accept{/lang}" {if $declineURL}onclick="return confirm('{lang}{$languageCategory}.{$eventName}.accept.sure{/lang}');"{/if} /></a>
											{/if}
											{if $declineURL}
												<a href="{@$declineURL}" title="{lang}{$languageCategory}.{$eventName}.decline{/lang}"><img src="{icon}deleteS.png{/icon}" alt="{lang}{$languageCategory}.{$eventName}.decline{/lang}" onclick="return confirm('{lang}{$languageCategory}.{$eventName}.accept.sure{/lang}');" /></a>
											{/if}
										</div>
										{/if}
									</td>
									<td class="columnText">
										<span>{@$notification->time|time}</span>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				 </div>

				<div class="contentFooter">
					{@$pagesLinks}
				</div>
			{else}
				<p>{lang}wcf.user.notification.noNotifications{/lang}</p>
			{/if}
		</div>
	</div>
</div>

{include file='footer' sandbox=false}
</body>
</html>