<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.moderation.suspectedSpam{/lang}</h2>
	</header>

	{if $showNotice}
		<woltlab-core-notice type="warning">{lang}wcf.moderation.suspectedSpam.notice{/lang}</woltlab-core-notice>
	{/if}

	{foreach from=$detections item=detection}
		<dl>
			<dt>{$detection[packageName]}</dt>
			{if $detection[details] !== null}
				<dd>{unsafe:$detection[details]}</dd>
			{/if}
		</dl>
	{/foreach}
</section>
