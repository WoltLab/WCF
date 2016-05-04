<ul class="sidebarBoxList">
	{foreach from=$boxUsers item=boxUser}
		<li class="box24">
			<a href="{link controller='User' object=$boxUser}{/link}">{@$boxUser->getAvatar()->getImageTag(24)}</a>
			
			<div class="sidebarBoxHeadline">
				<h3><a href="{link controller='User' object=$boxUser}{/link}" class="userLink" data-user-id="{@$boxUser->userID}">{$boxUser->username}</a></h3>
				{capture assign='__boxUserLanguageItem'}{lang __optional=true}wcf.user.boxList.description.{$boxSortField}{/lang}{/capture}
				{if $__boxUserLanguageItem}
					<small>{@$__boxUserLanguageItem}</small>
				{* TODO: else? *}
				{/if}
			</div>
		</li>
	{/foreach}
</ul>
