{if $userProfile === null}
	{* user no longer exists, use plain output rather than using a broken link *}
	<span class="userMention">{$username}</span>{* no newline after the tag
*}{else}
	<a href="{link controller='User' object=$userProfile->getDecoratedObject()}{/link}" class="userMention userLink" data-user-id="{@$userProfile->userID}">{$userProfile->username}</a>{* no newline after the tag
*}{/if}
