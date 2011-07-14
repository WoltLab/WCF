{if $__wcf->getPageMenu()->getMenuItems('footer')|count > 0}
	<div id="footerMenu" class="footerMenu">
		<div class="footerMenuInner">
			<ul>
				{foreach from=$__wcf->getPageMenu()->getMenuItems('footer') item=menuItem}
					<li>{$menuItem|print_r}</li>
				{/foreach}
			</ul>
		</div>
	</div>
{/if}