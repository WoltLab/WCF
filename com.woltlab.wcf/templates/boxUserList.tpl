<ul class="sidebarItemList">
	{foreach from=$boxUsers item=boxUser}
		<li class="box24">
			<a href="{link controller='User' object=$boxUser}{/link}" aria-hidden="true">{@$boxUser->getAvatar()->getImageTag(24)}</a>
			
			<div class="sidebarItemTitle">
				<h3>{user object=$boxUser}</h3>
				{capture assign='__boxUserLanguageItem'}{lang __optional=true}wcf.user.boxList.description.{$boxSortField}{/lang}{/capture}
				{if $__boxUserLanguageItem}
					<small>{@$__boxUserLanguageItem}</small>
				{/if}
			</div>
		</li>
	{/foreach}
</ul>
