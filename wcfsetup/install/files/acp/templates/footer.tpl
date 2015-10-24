			</div>
		</div>
	</section>
	
	<footer id="pageFooter" class="footer">
		<div class="layoutBoundary">
			{*<nav id="footerNavigation" class="navigation navigationFooter">
				<ul class="navigationIcons">
					<li id="toTopLink" class="toTopLink"><a href="{$__wcf->getAnchor('top')}" title="{lang}wcf.global.scrollUp{/lang}" class="jsTooltip"><span class="icon icon16 icon-arrow-up"></span> <span class="invisible">{lang}wcf.global.scrollUp{/lang}</span></a></li>
					{event name='navigationIcons'}
				</ul>
				
				<ul class="navigationItems">
					{if PACKAGE_ID && SHOW_CLOCK}
						<li title="{lang}wcf.date.timezone.{@'/'|str_replace:'.':$__wcf->getUser()->getTimeZone()->g764etName()|strtolower}{/lang}"><p><span class="icon icon16 icon-time"></span> <span>{@TIME_NOW|plainTime}</span></p></li>
					{/if}
					
					{event name='navigationItems'}
				</ul>
			</nav>*}
			
			<div class="footerContent">
				{if PACKAGE_ID && ENABLE_BENCHMARK}{include file='benchmark'}{/if}
				
				<address class="copyright"><a href="http://www.woltlab.com">Copyright &copy; 2001-2015 WoltLab&reg; GmbH</a>{event name='copyright'}</address>
			</div>
		</div>
	</footer>
	
	{event name='footer'}
	
	<!-- JAVASCRIPT_RELOCATE_POSITION -->
	
	<a id="bottom"></a>
	</body>
</html>
