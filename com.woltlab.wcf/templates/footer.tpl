				{event name='contents'}
				
				{hascontent}
					<div class="boxesContentBottom">
						<div class="boxContainer">
							{content}
								{if !$boxesContentBottom|empty}
									{@$boxesContentBottom}
								{/if}
								
								{foreach from=$__wcf->getBoxHandler()->getBoxes('contentBottom') item=box}
									{@$box->render()}
								{/foreach}
							{/content}
						</div>
					</div>
				{/hascontent}
				
				{if MODULE_WCF_AD && $__disableAds|empty}
					{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.footer.content')}
				{/if}
			</div>
				
			{capture assign='__sidebarRightContent'}
				{event name='boxesSidebarRightTop'}
				
				{* WCF2.1 Fallback *}
				{if !$sidebar|empty}
					{if !$sidebarOrientation|isset || $sidebarOrientation == 'right'}
						{@$sidebar}
					{/if}
				{/if}
				
				{if !$sidebarRight|empty}
					{@$sidebarRight}
				{/if}
				
				{foreach from=$__wcf->getBoxHandler()->getBoxes('sidebarRight') item=box}
					{@$box->render()}
				{/foreach}
				
				{event name='boxesSidebarRightBottom'}
			{/capture}
				
			{if $__sidebarRightContent|trim}
				{if !$__sidebarRightShow|isset}{assign var='__sidebarRightShow' value='wcf.global.button.showSidebar'|language}{/if}
				{if !$__sidebarRightHide|isset}{assign var='__sidebarRightHide' value='wcf.global.button.hideSidebar'|language}{/if}
				
				<aside class="sidebar boxesSidebarRight" aria-label="{lang}wcf.page.sidebar.right{/lang}" data-show-sidebar="{$__sidebarRightShow}" data-hide-sidebar="{$__sidebarRightHide}">
					<div class="boxContainer">
						{if MODULE_WCF_AD && $__disableAds|empty && $__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.top')}
							<div class="box boxBorderless">
								<div class="boxContent">
									{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.top')}
								</div>
							</div>
						{/if}
							
						{@$__sidebarRightContent}	
						
						{if MODULE_WCF_AD && $__disableAds|empty && $__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.bottom')}
							<div class="box boxBorderless">
								<div class="boxContent">
									{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.bottom')}
								</div>
							</div>
						{/if}
					</div>
				</aside>
			{/if}
		</div>
	</section>
	
	{hascontent}
		<div class="boxesBottom">
			<div class="boxContainer">
				{content}
					{if !$boxesBottom|empty}
						{@$boxesBottom}
					{/if}
				
					{foreach from=$__wcf->getBoxHandler()->getBoxes('bottom') item=box}
						{@$box->render()}
					{/foreach}
				{/content}
			</div>
		</div>
	{/hascontent}
	
	{hascontent}
		<div class="boxesFooterBoxes">
			<div class="layoutBoundary">
				<div class="boxContainer">
					{content}
						{if !$footerBoxes|empty}
							{@$footerBoxes}
						{/if}
					
						{foreach from=$__wcf->getBoxHandler()->getBoxes('footerBoxes') item=box}
							{@$box->render()}
						{/foreach}
					{/content}
				</div>
			</div>
		</div>
	{/hascontent}
	
	{include file='pageFooter'}
</div>

{include file='pageMenuMobile'}

{event name='footer'}

<div class="pageFooterStickyNotice">
	{if MODULE_COOKIE_POLICY_PAGE && $__wcf->session->isFirstVisit() && !$__wcf->user->userID}
		<div class="info cookiePolicyNotice">
			<div class="layoutBoundary">
				<span class="cookiePolicyNoticeText">{lang}wcf.page.cookiePolicy.info{/lang}</span>
				<a href="{page}com.woltlab.wcf.CookiePolicy{/page}" class="button buttonPrimary small cookiePolicyNoticeMoreInformation">{lang}wcf.page.cookiePolicy.info.moreInformation{/lang}</a>
				<a href="#" class="button small jsOnly cookiePolicyNoticeDismiss">{lang}wcf.global.button.close{/lang}</a>
				<script data-relocate="true">
					elBySel('.cookiePolicyNoticeDismiss').addEventListener(WCF_CLICK_EVENT, function(event) {
						event.preventDefault();

						elRemove(elBySel('.cookiePolicyNotice'));
					});
				</script>
			</div>
		</div>
	{/if}
	
	{event name='pageFooterStickyNotice'}
	
	<noscript>
		<div class="info">
			<div class="layoutBoundary">
				<span class="javascriptDisabledWarningText">{lang}wcf.page.javascriptDisabled{/lang}</span>
			</div>
		</div>	
	</noscript>
</div>

<!-- JAVASCRIPT_RELOCATE_POSITION -->

{@FOOTER_CODE}

<a id="bottom"></a>

</body>
</html>
