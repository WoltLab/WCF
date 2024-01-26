{if $userProfile === null}
	{* user no longer exists, use plain output rather than using a broken link *}
	{$username}{* no newline after the tag
*}{else}
	<a href="{link controller='User' object=$userProfile->getDecoratedObject()}{/link}" class="userMention userLink" data-object-id="{@$userProfile->userID}">{@$userProfile->getFormattedUsername()}</a>{* no newline after the tag
*}{/if}
