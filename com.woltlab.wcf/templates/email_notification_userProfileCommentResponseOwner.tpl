{if $mimeType === 'text/plain'}
{lang}wcf.user.notification.commentResponseOwner.mail.plaintext{/lang}
{$event->getUserNotificationObject()->message}
{else}
	{lang}wcf.user.notification.commentResponseOwner.mail.html{/lang}
	{assign var='user' value=$event->getAuthor()}
	{assign var='comment' value=$event->getUserNotificationObject()}
	{capture assign='commentContent'}
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td><a href="{link controller='User' object=$user isEmail=true}{/link}" title="{$comment->username}">{@$user->getAvatar()->getImageTag(128)}</a></td>
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
	{include file='email_paddingHelper' block=true class='box128' content=$commentContent sandbox=true}
{/if}
