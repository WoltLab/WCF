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
		<section class="box">
			<h2 class="boxTitle">{lang}{$menuCategory->menuItem}{/lang}</h2>
			
			<div class="boxContent">
				<nav>
					<ol class="boxMenu">
						{foreach from=$__wcf->getUserMenu()->getMenuItems($menuCategory->menuItem) item=menuItem}
							<li{if $menuItem->menuItem|in_array:$__userMenuActiveItems} class="active"{/if}>
								<a href="{$menuItem->getProcessor()->getLink()}" class="boxMenuLink"><span class="boxMenuLinkTitle">{@$menuItem}</span></a>
							</li>
						{/foreach}
					</ol>
				</nav>
			</div>	
		</section>
	{/foreach}
{/capture}
