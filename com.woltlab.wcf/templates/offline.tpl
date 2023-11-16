{include file='header' skipBreadcrumbs=true}

<woltlab-core-notice type="warning">
	<p><strong>{lang}wcf.page.offline{/lang}</strong></p>
	<p>{if OFFLINE_MESSAGE_ALLOW_HTML}{@OFFLINE_MESSAGE|phrase}{else}{@OFFLINE_MESSAGE|phrase|newlineToBreak}{/if}</p>
</woltlab-core-notice>

{include file='footer'}
