{if $mimeType === 'text/plain'}
{lang}wcf.moderation.activation.notification.mail.plaintext{/lang}
{if $affectedObject}

{unsafe:$affectedObject->getMailText($mimeType)} {* this line ends with a space *}
{/if}
{else}
	{lang}wcf.moderation.activation.notification.mail.html{/lang}
	{if $affectedObject}
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
							{unsafe:$affectedObject->getMailText($mimeType)}
						</div>
					</td>
				</tr>
			</table>
		{/capture}
		{include file='email_paddingHelper' block=true class='box'|concat:$avatarSize content=$userContent sandbox=true}
	{/if}
{/if}
