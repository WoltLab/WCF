{assign var='count' value=$event->getAuthors()|count}{assign var='guestTimesTriggered' value=$event->getNotification()->guestTimesTriggered}{assign var='authors' value=$event->getAuthors()|array_values}
{if $mimeType === 'text/plain'}
{capture assign='authorList'}{lang}wcf.user.notification.mail.authorList.plaintext{/lang}{/capture}
{if $moderationQueue->isSystemReport()}
{lang}wcf.moderation.report.notification.mail.plaintext.system{/lang}

{lang}wcf.moderation.report.system.message{/lang} {* this line ends with a space *}
{else}
{lang}wcf.moderation.report.notification.mail.plaintext{/lang}

{unsafe:$moderationQueue->getMailText($mimeType)} {* this line ends with a space *}
{/if}
{else}
	{capture assign='authorList'}{lang}wcf.user.notification.mail.authorList.html{/lang}{/capture}
	{if $moderationQueue->isSystemReport()}
		{lang}wcf.moderation.report.notification.mail.html.system{/lang}

		{capture assign='userContent'}
			<div class="containerHeadline">
				<h3>
					{lang}wcf.moderation.report.system{/lang}
					&#xb7;
					<small>{$moderationQueue->time|plainTime}</small>
				</h3>
			</div>
			<div>
				{lang}wcf.moderation.report.system.message{/lang}
			</div>
		{/capture}
		{include file='email_paddingHelper' block=true class='box' content=$userContent sandbox=true}
	{else}
		{lang}wcf.moderation.report.notification.mail.html{/lang}
		{assign var='user' value=$event->getAuthor()}

		{if $notificationType == 'instant'}{assign var='avatarSize' value=48}
		{else}{assign var='avatarSize' value=32}{/if}
		{capture assign='userContent'}
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td><a href="{link controller='User' object=$user isHtmlEmail=true}{/link}"
						   title="{$user->username}">{unsafe:$user->getAvatar()->getSafeImageTag($avatarSize)}</a></td>
					<td class="boxContent">
						<div class="containerHeadline">
							<h3>
								{if $moderationQueue->userID}
									<a href="{link controller='User' object=$user isHtmlEmail=true}{/link}">{$moderationQueue->username}</a>
								{else}
									{$moderationQueue->username}
								{/if}
								&#xb7;
								<small>{$moderationQueue->time|plainTime}</small>
							</h3>
						</div>
						<div>
							{unsafe:$moderationQueue->getMailText($mimeType)}
						</div>
					</td>
				</tr>
			</table>
		{/capture}
		{include file='email_paddingHelper' block=true class='box'|concat:$avatarSize content=$userContent sandbox=true}
	{/if}
{/if}
