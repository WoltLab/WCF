{if $userProfile === null}
	{* user no longer exists, use plain output rather than using a broken link *}
	@{$username}
{else}
	{* non-breaking space below to prevent wrapping of user avatar and username *}
	<a href="{link controller='User' object=$userProfile->getDecoratedObject()}{/link}">{@$userProfile->getAvatar()->getImageTag(16)}&nbsp;{$userProfile->username}</a>
{/if}