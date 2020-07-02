{assign var='count' value=$event->getAuthors()|count}{assign var='guestTimesTriggered' value=$event->getNotification()->guestTimesTriggered}{assign var='authors' value=$event->getAuthors()|array_values}
{if $mimeType === 'text/plain'}
{capture assign='authorList'}{lang}wcf.user.notification.mail.authorList.plaintext{/lang}{/capture}
{lang}{$notificationContent[variables][languageItemPrefix]}.comment.mail.plaintext{/lang}{if $count == 1 && !$guestTimesTriggered} {* this line ends with a space *}

{@$event->getUserNotificationObject()->getMailText($mimeType)}{/if} {* this line ends with a space *}
{else}
	{capture assign='authorList'}{lang}wcf.user.notification.mail.authorList.html{/lang}{/capture}
	{lang}{$notificationContent[variables][languageItemPrefix]}.comment.mail.html{/lang}
	{assign var='user' value=$event->getAuthor()}
	{assign var='comment' value=$event->getUserNotificationObject()}
	
	{if $notificationType == 'instant'}{assign var='avatarSize' value=48}
	{else}{assign var='avatarSize' value=32}{/if}
	{capture assign='commentContent'}
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td><a href="{link controller='User' object=$user isHtmlEmail=true}{/link}" title="{$comment->username}">{@$user->getAvatar()->getImageTag($avatarSize)}</a></td>
			<td class="boxContent">
				<div class="containerHeadline">
					<h3>
						{if $comment->userID}
							<a href="{link controller='User' object=$user isHtmlEmail=true}{/link}">{$comment->username}</a>
						{else}
							{$comment->username}
						{/if}
						&#xb7;
						<small>{$comment->time|plainTime}</small>
					</h3>
				</div>
				<div>
					{@$comment->getMailText($mimeType)}
				</div>
			</td>
		</tr>
	</table>
	{/capture}
	{include file='email_paddingHelper' block=true class='box'|concat:$avatarSize content=$commentContent sandbox=true}
{/if}
