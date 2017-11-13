{capture assign='sidebarLeft'}
	{assign var=__userMenuActiveItems value=$__wcf->getUserMenu()->getActiveMenuItems()}
	
	<script data-relocate="true">
		$(function() {
			// mobile safari hover workaround
			if ($(window).width() <= 800) {
				$('.sidebar').addClass('mobileSidebar').hover(function() { });
			}
		});
	</script>
	
	<section class="box" data-static-box-identifier="com.woltlab.wcf.UserMenu">
		{foreach from=$__wcf->getUserMenu()->getMenuItems('') item=menuCategory}
			<h2 class="boxTitle">{lang}{$menuCategory->menuItem}{/lang}</h2>
			
			<nav class="boxContent">
				<ol class="boxMenu">
					{foreach from=$__wcf->getUserMenu()->getMenuItems($menuCategory->menuItem) item=menuItem}
						<li{if $menuItem->menuItem|in_array:$__userMenuActiveItems} class="active"{/if}>
							<a href="{$menuItem->getProcessor()->getLink()}" class="boxMenuLink"><span class="boxMenuLinkTitle">{@$menuItem}</span></a>
						</li>
					{/foreach}
				</ol>
			</nav>
		{/foreach}
	</section>
{/capture}
