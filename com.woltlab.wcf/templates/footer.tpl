				{event name='contents'}
				
				{hascontent}
					<div class="boxesContentBottom">
						{content}
							{foreach from=$__wcf->getBoxHandler()->getBoxes('contentBottom') item=box}
								{@$box}
							{/foreach}
						{/content}
					</div>
				{/hascontent}
				
				{if MODULE_WCF_AD && $__disableAds|empty}
					{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.footer.content')}
				{/if}
			</div>
				
			{hascontent}
				<aside class="sidebar boxesSidebarRight">
					{content}
						{event name='boxesSidebarRightTop'}
												
						{* WCF2.1 Fallback *}
						{if !$sidebar|empty}
							{if !$sidebarOrientation|isset || $sidebarOrientation == 'right'}
								{@$sidebar}
							{/if}
						{/if}
						
						{if !$sidebarRight|empty}
							{@$sidebarLeft}
						{/if}
						
						{foreach from=$__wcf->getBoxHandler()->getBoxes('sidebarRight') item=box}
							{@$box}
						{/foreach}
					
						{event name='boxesSidebarRightBottom'}
					{/content}
				</aside>
			{/hascontent}
		</div>
	</section>
				
	{hascontent}
		<div class="boxesBottom">
			<div class="layoutBoundary">
				{content}
					{foreach from=$__wcf->getBoxHandler()->getBoxes('bottom') item=box}
						{@$box}
					{/foreach}
				{/content}
			</div>	
		</div>
	{/hascontent}
				
	<div class="boxesFooterBoxes">			
		<div class="layoutBoundary">
			{hascontent}
				<ul>
					{content}
						{if !$footerBoxes|empty}{@$footerBoxes}{/if}
					
						{foreach from=$__wcf->getBoxHandler()->getBoxes('footerBoxes') item=box}
							{@$box}
						{/foreach}
					{/content}
				</ul>
			{/hascontent}
		</div>
	</div>
	
	<footer id="pageFooter" class="footer">
		<div class="layoutBoundary">
			{hascontent}
				<div class="boxesFooter">
					{content}
						{foreach from=$__wcf->getBoxHandler()->getBoxes('footer') item=box}
							{@$box}
						{/foreach}
					{/content}
				</div>
			{/hascontent}
			
			<div class="footerContent">
				{event name='footerContents'}
				
				{if ENABLE_BENCHMARK}{include file='benchmark'}{/if}
				
				{event name='copyright'}
			</div>
			
			{if MODULE_WCF_AD && $__disableAds|empty}
				{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.footer.bottom')}
			{/if}
		</div>
	</footer>
</div>

{event name='footer'}

<!-- JAVASCRIPT_RELOCATE_POSITION -->

{@FOOTER_CODE}

<a id="bottom"></a>
