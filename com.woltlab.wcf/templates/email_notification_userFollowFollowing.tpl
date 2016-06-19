{if $mimeType === 'text/plain'}
{lang}wcf.user.notification.follow.mail.plaintext{/lang}
{else}
	{lang}wcf.user.notification.follow.mail.html{/lang}
	{assign var='user' value=$event->getAuthor()}
	
	{if $notificationType == 'instant'}{assign var='avatarSize' value=128}
	{else}{assign var='avatarSize' value=64}{/if}
	{capture assign='userContent'}
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td><a href="{link controller='User' object=$user isEmail=true}{/link}" title="{$user->username}">{@$user->getAvatar()->getImageTag($avatarSize)}</a></td>
			<td class="boxContent">
				{include file='email_userInformationHeadline'}
			</td>
		</tr>
	</table>
	{/capture}
	{include file='email_paddingHelper' block=true class='box'|concat:$avatarSize content=$userContent sandbox=true}
{/if}
