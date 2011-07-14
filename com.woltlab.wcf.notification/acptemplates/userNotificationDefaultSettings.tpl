{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/infoL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.user.notification.settings{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset && $success}
	<p class="success">{lang}wcf.user.notification.settings.saved{/lang}</p>
{/if}

<form method="post" action="index.php?form=UserNotificationDefaultSettings">
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
											<span class="emptyHead"><img src="{@RELATIVE_WCF_DIR}icon/{$notificationType->getIcon()}S.png" alt="{lang}wcf.user.notification.type.{$notificationTypeName}{/lang}" title="{lang}wcf.user.notification.type.{$notificationTypeName}{/lang}" /></span>
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
										<input value="1" type="checkbox" name="activeEventNotifications[{$typeName}][{$eventName}][{$supportedType}][enabled]"{if !$supported} disabled="disabled"{/if}{if $supported && $activeEventNotifications[$typeName][$eventName][$supportedType]['enabled']} checked="checked"{/if} />
										<input value="1" type="checkbox" name="activeEventNotifications[{$typeName}][{$eventName}][{$supportedType}][canBeDisabled]"{if !$supported} disabled="disabled"{/if}{if $supported && $activeEventNotifications[$typeName][$eventName][$supportedType]['canBeDisabled']} checked="checked"{/if} />
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
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}