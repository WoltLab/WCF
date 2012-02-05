			</section>
			<!-- CONTENT -->
		</div>
	</div>
	<!-- /MAIN -->
	
	<!-- FOOTER -->
	<footer id="pageFooter" class="pageFooter">
		<div>
			<nav id="footerNavigation" class="footerNavigation">
				<ul>
					<li id="toTopLink" class="toTopLink"><a href="{@$__wcf->getAnchor('top')}" title="{lang}wcf.global.scrollUp{/lang}" class="balloonTooltip"><img src="{@RELATIVE_WCF_DIR}icon/toTop.svg" alt="" /> <span class="invisible">{lang}wcf.global.scrollUp{/lang}</span></a></li>
				</ul>
			</nav>
		</div>
		
		{if PACKAGE_ID && ENABLE_BENCHMARK}{include file='benchmark' sandbox=false}{/if}
		
		<address class="wcf-copyright"><a href="http://www.woltlab.com" title="Go to the WoltLab website">Copyright &copy; 2001-2012 WoltLab&reg; GmbH</a></address>
	</footer>
	<!-- /FOOTER -->
	<a id="bottom"></a>
</body>
</html>
