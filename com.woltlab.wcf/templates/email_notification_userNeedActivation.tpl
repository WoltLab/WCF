{assign var='count' value=$event->getAuthors()|count}{assign var='guestTimesTriggered' value=$event->getNotification()->guestTimesTriggered}{assign var='authors' value=$event->getAuthors()|array_values}
{if $mimeType === 'text/plain'}
{if $count > 1}
{capture assign='authorList'}{lang}wcf.user.notification.mail.authorList.plaintext{/lang}{/capture}
{lang}wcf.user.notification.needActivation.mail.plaintext.stacked{/lang}
{else}
{lang}wcf.user.notification.needActivation.mail.plaintext{/lang}
{/if}
{else}
	{if $count > 1}
		{capture assign='authorList'}{lang}wcf.user.notification.mail.authorList.html{/lang}{/capture}
		{lang}wcf.user.notification.needActivation.mail.html.stacked{/lang}
	{else}
		{lang}wcf.user.notification.needActivation.mail.html{/lang}
	{/if}
	{assign var='user' value=$event->getAuthor()}

	{if $notificationType == 'instant'}{assign var='avatarSize' value=48}
	{else}{assign var='avatarSize' value=32}{/if}
	{capture assign='userContent'}
		<table cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td>
					<a href="{link controller='User' object=$user isHtmlEmail=true}{/link}"
					   title="{$user->username}">
						{unsafe:$user->getAvatar()->getSafeImageTag($avatarSize)}
					</a>
				</td>
				<td class="boxContent">
					{include file='email_userInformationHeadline'}
				</td>
			</tr>
		</table>
	{/capture}
	{include file='email_paddingHelper' block=true class='box'|concat:$avatarSize content=$userContent sandbox=true}
{/if}
