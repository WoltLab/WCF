{foreach from=$rewriteRules key=$webserver item=$rules}
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.rewrite.{$webserver}{/lang}</h2>
		
		{foreach from=$rules key=$path item=content}
			<p>
				<span class="inlineCode">{$path}</span>
			</p>
			<pre>{$content}</pre>
		{/foreach}
	</section>
{/foreach}

