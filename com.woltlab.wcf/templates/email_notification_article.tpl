{if $mimeType === 'text/plain'}
{lang}{@$languageVariablePrefix}.mail.plaintext{/lang}

{@$articleContent->getMailText($mimeType)} {* this line ends with a space *}
{else}
	{lang}{@$languageVariablePrefix}.mail.html{/lang}
	{assign var='user' value=$event->getAuthor()}
	{assign var='article' value=$event->getUserNotificationObject()}
	
	{if $notificationType == 'instant'}{assign var='avatarSize' value=48}
	{else}{assign var='avatarSize' value=32}{/if}
	{capture assign='articleContent'}
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td><a href="{link controller='User' object=$user isHtmlEmail=true}{/link}" title="{$article->username}">{@$user->getAvatar()->getImageTag($avatarSize)}</a></td>
			<td class="boxContent">
				<div class="containerHeadline">
					<h3>
						{if $article->userID}
							<a href="{link controller='User' object=$user isHtmlEmail=true}{/link}">{$article->username}</a>
						{else}
							{$article->username}
						{/if}
						&#xb7;
						<a href="{$articleContent->getLink()}"><small>{$article->time|plainTime}</small></a>
					</h3>
				</div>
				<div>
					{@$articleContent->getMailText($mimeType)}
				</div>
			</td>
		</tr>
	</table>
	{/capture}
	{include file='email_paddingHelper' block=true class='box'|concat:$avatarSize content=$articleContent sandbox=true}
{/if}
