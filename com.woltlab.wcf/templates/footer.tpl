				{event name='contents'}
				
				{hascontent}
					<div class="boxesContentBottom">
						<div class="boxContainer">
							{content}
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
			
			{hascontent}
				<aside class="sidebar boxesSidebarRight">
					<div class="boxContainer">
						{content}
							{if MODULE_WCF_AD && $__disableAds|empty && $__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.top')}
								<div class="box boxBorderless">
									<div class="boxContent">
										{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.top')}
									</div>
								</div>
							{/if}
							
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
						
							{if MODULE_WCF_AD && $__disableAds|empty && $__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.bottom')}
								<div class="box boxBorderless">
									<div class="boxContent">
										{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.bottom')}
									</div>
								</div>
							{/if}	
						{/content}
					</div>
				</aside>
			{/hascontent}
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
						{if !$footerBoxes|empty}{@$footerBoxes}{/if}
					
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
					elBySel('.cookiePolicyNoticeDismiss').addEventListener(WCF_CLICK_EVENT, function() {
						elRemove(elBySel('.cookiePolicyNotice'));
					});
				</script>
			</div>
		</div>
	{/if}
	
	{event name='pageFooterStickyNotice'}
	
	<noscript>
		<div class="layoutBoundary">
			<span class="javascriptDisabledWarningText">{lang}wcf.page.javascriptDisabled{/lang}</span>
		</div>
	</noscript>
</div>

<!-- JAVASCRIPT_RELOCATE_POSITION -->

{@FOOTER_CODE}

<a id="bottom"></a>

</body>
</html>
