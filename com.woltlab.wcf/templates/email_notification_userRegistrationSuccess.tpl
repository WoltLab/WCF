{if $mimeType === 'text/plain'}
	{lang}wcf.user.notification.registrationSuccess.mail.plaintext{/lang}
{else}
	{lang}wcf.user.notification.registrationSuccess.mail.html{/lang}
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
