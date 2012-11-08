			</section>
			<!-- CONTENT -->
		</div>
	</div>
	<!-- /MAIN -->
	
	<!-- FOOTER -->
	<footer id="pageFooter" class="layoutFluid footer">
		<nav id="footerNavigation" class="navigation navigationFooter clearfix">
			<ul class="navigationIcons">
				<li id="toTopLink" class="toTopLink"><a href="{$__wcf->getAnchor('top')}" title="{lang}wcf.global.scrollUp{/lang}" class="jsTooltip"><img src="{@$__wcf->getPath()}icon/circleArrowUpColored.svg" alt="" class="icon16" /> <span class="invisible">{lang}wcf.global.scrollUp{/lang}</span></a></li>
				{event name='navigationIcons'}
			</ul>
			
			<ul class="navigationItems">
				{if PACKAGE_ID && SHOW_CLOCK}
					<li title="{lang}wcf.date.timezone.{@'/'|str_replace:'.':$__wcf->getUser()->getTimeZone()->getName()|strtolower}{/lang}"><p><img src="{@$__wcf->getPath()}icon/clockColored.svg" alt="" class="icon16" /> <span>{@TIME_NOW|plainTime}</span></p></li>
				{/if}
				{event name='navigationItems'}
			</ul>
		</nav>
		
		<div class="footerContent">
			{if PACKAGE_ID && ENABLE_BENCHMARK}{include file='benchmark'}{/if}
			
			<address class="copyright marginTop"><a href="http://www.woltlab.com" title="Go to the WoltLab website">Copyright &copy; 2001-2012 WoltLab&reg; GmbH</a>{event name='copyright'}</address>
		</div>
	</footer>
	<!-- /FOOTER -->
	<a id="bottom"></a>
</body>
</html>
