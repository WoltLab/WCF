<ul class="sidebarItemList">
	{foreach from=$boxUsers item=boxUser}
		<li class="box24">
			{user object=$boxUser type='avatar24' ariaHidden='true' tabindex='-1'}
			
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
