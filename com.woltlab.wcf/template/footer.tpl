			{if $skipBreadcrumbs|empty}{include file='breadcrumbs' sandbox=false __microdata=false}{/if}
			
		</section>
		<!-- /CONTENT -->
	</div>
</div>
<!-- /MAIN -->

<!-- FOOTER -->
<footer id="pageFooter" class="layoutFluid footer">
	<!-- footer navigation -->
	<nav id="footerNavigation" class="navigation navigationFooter clearfix">
		{include file='footerMenu'}
		
		<ul class="navigationIcons">
			<li id="toTopLink" class="toTopLink"><a href="{$__wcf->getAnchor('top')}" title="{lang}wcf.global.scrollUp{/lang}" class="jsTooltip"><img src="{icon size='S'}toTop{/icon}" alt="" class="icon16" /> <span class="invisible">{lang}wcf.global.scrollUp{/lang}</span></a></li>
			{if SHOW_CLOCK}
				<li class="separator"><p><img src="{icon size='S'}time{/icon}" alt="" class="icon16" /> <span>{@TIME_NOW|plainTime}</span></p></li>
			{/if}
		</ul>
	</nav>
	<!-- /footer navigation -->
	
	<div class="footerContent">
		{if ENABLE_BENCHMARK}{include file='benchmark'}{/if}
	
		{event name='copyright'}
	</div>
</footer>
<!-- /FOOTER -->
<a id="bottom"></a>
