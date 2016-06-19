{if $mimeType === 'text/plain'}
{lang}wcf.user.notification.commentResponse.mail.plaintext{/lang}
{$event->getUserNotificationObject()->message} {* this line ends with a space *}
{else}
	{lang}wcf.user.notification.commentResponse.mail.html{/lang}
	{assign var='user' value=$event->getAuthor()}
	{assign var='comment' value=$event->getUserNotificationObject()}
	
	{if $notificationType == 'instant'}{assign var='avatarSize' value=128}
	{else}{assign var='avatarSize' value=64}{/if}
	{capture assign='commentContent'}
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td><a href="{link controller='User' object=$user isEmail=true}{/link}" title="{$comment->username}">{@$user->getAvatar()->getImageTag($avatarSize)}</a></td>
			<td class="boxContent">
				<div class="containerHeadline">
					<h3>
						{if $comment->userID}
							<a href="{link controller='User' object=$user isEmail=true}{/link}">{$comment->username}</a>
						{else}
							{$comment->username}
						{/if}
						&#xb7;
						<small>{$comment->time|plainTime}</small>
					</h3>
				</div>
				<div>
					{$comment->message}
				</div>
			</td>
		</tr>
	</table>
	{/capture}
	{include file='email_paddingHelper' block=true class='box'|concat:$avatarSize content=$commentContent sandbox=true}
{/if}
