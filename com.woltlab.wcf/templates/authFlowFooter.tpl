			</div>
		</div>
	</section>
	
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

{@FOOTER_CODE}

<span id="bottom"></span>

</body>
</html>
