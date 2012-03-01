				{if $skipBreadcrumbs|empty}{include file='breadcrumbs' sandbox=false __microdata=false}{/if}
				
			</section>
			<!-- /CONTENT -->
		</div>
	</div>
	<!-- /MAIN -->
	
	<!-- FOOTER -->
	<footer id="pageFooter" class="wcf-pageFooter">
		<div>
			<!-- footer navigation -->
			<nav id="footerNavigation" class="wcf-footerNavigation">
				{include file='footerMenu'}
				
				<ul>
					<li id="toTopLink" class="toTopLink"><a href="{$__wcf->getAnchor('top')}" title="{lang}wcf.global.scrollUp{/lang}" class="jsTooltip"><img src="{icon size='S'}toTop{/icon}" alt="" /> <span class="invisible">{lang}wcf.global.scrollUp{/lang}</span></a></li>
					{if SHOW_CLOCK}
						<li><p><img src="{icon size='S'}time1{/icon}" alt="" /> <span>{@TIME_NOW|plainTime}</span></p></li>
					{/if}
				</ul>
			</nav>
			<!-- /footer navigation -->
			
			{include file='footerMenu'}
		</div>
		
		{if ENABLE_BENCHMARK}{include file='benchmark'}{/if}
		
		{event name='copyright'}
	</footer>
	<!-- /FOOTER -->
	<a id="bottom"></a>
	