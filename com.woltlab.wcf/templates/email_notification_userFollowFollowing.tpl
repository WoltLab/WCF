{if $mimeType === 'text/plain'}
{lang}wcf.user.notification.follow.mail.plaintext{/lang}
{else}
	{assign var='user' value=$event->getAuthor()}
	{capture assign='userContent'}
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td><a href="{link controller='User' object=$user}{/link}" title="{$user->username}">{@$user->getAvatar()->getImageTag(128)}</a></td>
			<td>
				{include file='email_userInformationHeadline'}
			</td>
		</tr>
	</table>
	{/capture}
	{include file='email_paddingHelper' block=true class='box128' content=$userContent sandbox=true}
	<br style="clear:both">
	{lang}wcf.user.notification.follow.mail.html{/lang}
{/if}
