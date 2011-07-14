{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.notification.settings{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">

	{capture append=userMessages}
		{if $errorField}
			<p class="error">{lang}wcf.global.form.error{/lang}</p>
		{/if}

		{if $success|isset}
			<p class="success">{lang}wcf.user.edit.success{/lang}</p>
		{/if}
	{/capture}

	{include file="userCPHeader"}

	<form method="post" action="index.php?form=UserNotificationSettings">
		<div class="border tabMenuContent">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wcf.user.notification.settings{/lang}</h3>

				<div class="border borderMarginRemove">
					<table class="tableList">
					{foreach from=$notificationObjectTypes key=typeName item=data}
						{assign var=objectType value=$data.object}
						{assign var=events value=$data.events}
						{cycle name="eventRows" values='container-1,container-2' print=false advance=false reset=true}
						<thead>
							<tr class="tableHead">
								<th class="columnObjectType">
									<div>
										<h3 class="emptyHead">{lang}wcf.user.notification.object.type.{$typeName}{/lang}</h3>
									</div>
								</th>
								{foreach from=$notificationTypes key=notificationTypeName item=notificationType}
									<th class="columnNotificationType columnIcon">
										<div>
											<span class="emptyHead"><img src="{icon}{$notificationType->getIcon()}S.png{/icon}" alt="{lang}wcf.user.notification.type.{$notificationTypeName}{/lang}" title="{lang}wcf.user.notification.type.{$notificationTypeName}{/lang}" /></span>
										</div>
									</th>
								{/foreach}
							</tr>
						</thead>
						<tbody>
							{foreach from=$events key=eventName item=event}
								<tr class="{cycle name="eventRows"}">
									<td class="columnEvent columnText">
										<p title="{$event->getDescription()}">{$event->getTitle()}</p>
										<p class="smallFont">{$event->getDescription()}</p>
									</td>
									{foreach from=$event->supportedNotificationTypes key=supportedType item=supported}
									<td class="columnNotificationType columnIcon">
										<input type="checkbox" name="activeEventNotifications[{$typeName}][{$eventName}][{$supportedType}]"{if !$supported} disabled="disabled"{/if}{if $activeEventNotifications[$typeName][$eventName][$supportedType]} checked="checked"{/if} />
									</td>
									{/foreach}
								</tr>
							{/foreach}
						</tbody>
					{/foreach}
					</table>
				</div>
			</div>
		</div>
		<div class="formSubmit">
			{@SECURITY_TOKEN_INPUT_TAG}
			{@SID_INPUT_TAG}
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		</div>
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>