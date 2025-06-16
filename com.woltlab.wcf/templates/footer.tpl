				{event name='contents'}
				
				{hascontent}
					<div class="boxesContentBottom">
						<div class="boxContainer">
							{content}
								{if !$boxesContentBottom|empty}
									{unsafe:$boxesContentBottom}
								{/if}
								
								{foreach from=$__wcf->getBoxHandler()->getBoxes('contentBottom') item=box}
									{unsafe:$box->render()}
								{/foreach}
							{/content}
						</div>
					</div>
				{/hascontent}
				
				{if MODULE_WCF_AD && $__disableAds|empty}
					{unsafe:$__wcf->getAdHandler()->getAds('com.woltlab.wcf.footer.content')}
				{/if}
			</div>
			
			{hascontent}
				<aside class="sidebar boxesSidebarRight" aria-label="{lang}wcf.page.sidebar.right{/lang}">
					<div class="boxContainer">
						{content}{unsafe:$__sidebarRightContent}{/content}
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
						{unsafe:$boxesBottom}
					{/if}
				
					{foreach from=$__wcf->getBoxHandler()->getBoxes('bottom') item=box}
						{unsafe:$box->render()}
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
							{unsafe:$footerBoxes}
						{/if}
					
						{foreach from=$__wcf->getBoxHandler()->getBoxes('footerBoxes') item=box}
							{unsafe:$box->render()}
						{/foreach}
					{/content}
				</div>
			</div>
		</div>
	{/hascontent}
	
	{include file='pageFooter'}
</div>

{event name='footer'}

<div class="pageFooterStickyNotice">
	{event name='pageFooterStickyNotice'}
	
	<noscript>
		<div class="info" role="status">
			<div class="layoutBoundary">
				<span class="javascriptDisabledWarningText">{lang}wcf.page.javascriptDisabled{/lang}</span>
			</div>
		</div>	
	</noscript>
</div>

<!-- {$__wcf->getRequestNonce('JAVASCRIPT_RELOCATE_POSITION')} -->

{unsafe:FOOTER_CODE}

<span id="bottom"></span>

</body>
</html>
