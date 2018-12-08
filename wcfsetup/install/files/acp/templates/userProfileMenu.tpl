{include file='header' pageTitle='wcf.acp.user.profileMenu.sort'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Sortable/List'], function (UiSortableList) {
		new UiSortableList({
			containerId: 'userProfileMenuItemList',
			className: 'wcf\\data\\user\\profile\\menu\\item\\UserProfileMenuItemAction',
			isSimpleSorting: true
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.profileMenu.sort{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

<div id="userProfileMenuItemList" class="section">
	<ol class="sortableList" data-object-id="0">
		{foreach from=$userProfileMenuItemList item=menuItem}
			<li class="sortableNode" data-object-id="{@$menuItem->menuItemID}">
				<span class="sortableNodeLabel">
					<span>{$menuItem}</span>
					
					<span class="statusDisplay sortableButtonContainer">
						<span class="icon icon16 fa-arrows sortableNodeHandle"></span>
					</span>
				</span>
			</li>
		{/foreach}
	</ol>
</div>

<div class="formSubmit">
	<button class="button buttonPrimary" data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
</div>

{include file='footer'}
