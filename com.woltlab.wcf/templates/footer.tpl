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
		<p class="info cookiePolicyNotice">
			{lang}wcf.page.cookiePolicy.info{/lang}
			<span class="icon icon16 fa-times jsTooltip jsOnly pointer cookiePolicyNoticeDismiss" title="{lang}wcf.global.button.close{/lang}"></span>
			<script data-relocate="true">
				elBySel('.cookiePolicyNoticeDismiss').addEventListener(WCF_CLICK_EVENT, function() {
					elRemove(elBySel('.cookiePolicyNotice'));
				});
			</script>
		</p>
	{/if}
	
	{event name='pageFooterStickyNotice'}
	
	<noscript>
		<p class="error javascriptDisabledWarning">{lang}wcf.page.javascriptDisabled{/lang}</p>
	</noscript>
</div>

<!-- JAVASCRIPT_RELOCATE_POSITION -->

{@FOOTER_CODE}

<a id="bottom"></a>
				
</body>
</html>
