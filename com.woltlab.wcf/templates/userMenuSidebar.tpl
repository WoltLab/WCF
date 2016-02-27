{capture assign='sidebarLeft'}
	{assign var=__userMenuActiveItems value=$__wcf->getUserMenu()->getActiveMenuItems()}
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			// mobile safari hover workaround
			if ($(window).width() <= 800) {
				$('.sidebar').addClass('mobileSidebar').hover(function() { });
			}
		});
		//]]>
	</script> 
	
	{foreach from=$__wcf->getUserMenu()->getMenuItems('') item=menuCategory}
		<section>
			<h1>{lang}{$menuCategory->menuItem}{/lang}</h1>
			
			<nav>
				<ul class="sidebarNavigation">
					{foreach from=$__wcf->getUserMenu()->getMenuItems($menuCategory->menuItem) item=menuItem}
						<li{if $menuItem->menuItem|in_array:$__userMenuActiveItems} class="active"{/if}><a href="{$menuItem->getProcessor()->getLink()}">{@$menuItem}</a></li>
					{/foreach}
				</ul>
			</nav>
		</section>
	{/foreach}
{/capture}
