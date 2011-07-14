			<div class="info deletable" id="outstandingNotificationsContainer">
				<a href="index.php?action=NotificationConfirm{if $this->session->requestMethod == 'GET'}&amp;url={$this->session->requestURI|rawurlencode}{/if}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" class="close deleteButton"><img src="{icon}closeS.png{/icon}" alt="" title="{lang}wcf.user.notification.confirmAll{/lang}" longdesc="" /></a>
				<p>{lang}wcf.user.notification.type.userMessages.title{/lang}</p>
				<ul class="itemList">
					{foreach from=$notifications item=notification}
						{assign var=languageCategory value=$notification->event->languageCategory}
						{assign var=eventName value=$notification->eventName}
						<li class="deletable"{if $notification->event->icon} style="list-style-image:url('{icon}{$notification->event->icon}S.png{/icon}');"{/if}>
							{if $notification->event->requiresConfirmation}
								{assign var=acceptURL value=$notification->event->getAcceptURL($notification)}
								{assign var=declineURL value=$notification->event->getDeclineURL($notification)}
								<div class="buttons">
									{if $acceptURL}
									<a href="{@$acceptURL}" class="deleteButton" title="{lang}{$languageCategory}.{$eventName}.accept{/lang}"><img src="{icon}checkS.png{/icon}" alt="{lang}{$languageCategory}.{$eventName}.accept{/lang}" longdesc="{if $declineURL}{lang}{$languageCategory}.{$eventName}.accept.sure{/lang}{/if}" /></a>
									{/if}
									{if $declineURL}
									<a href="{@$declineURL}" class="deleteButton" title="{lang}{$languageCategory}.{$eventName}.decline{/lang}"><img src="{icon}deleteS.png{/icon}" alt="{lang}{$languageCategory}.{$eventName}.decline{/lang}" longdesc="{lang}{$languageCategory}.{$eventName}.decline.sure{/lang}" /></a>
									{/if}
								</div>
							{/if}
							<p class="itemListTitle">{@$notification->messageCache}</p>
						</li>
					{/foreach}
				</ul>
			</div>
			<script type="text/javascript">
				//<![CDATA[
				document.observe('wcf:inlineDelete', function() {
					if ($('outstandingNotificationsContainer') && !$('outstandingNotificationsContainer').down('li')) {
						inlineDelete($('outstandingNotificationsContainer').down('.close'));
					}
				});
				//]]>
			</script>