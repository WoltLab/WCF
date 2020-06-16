{foreach from=$rewriteRules key=$webserver item=$rules}
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.rewrite.{$webserver}{/lang}</h2>
		
		{foreach from=$rules key=$path item=content}
			<dl>
				<dt>{lang}wcf.acp.rewrite.filename{/lang}</dt>
				<dd>
					<kbd>{$path}</kbd>
				</dd>
				
				<dt>{lang}wcf.acp.rewrite.fileContent{/lang}</dt>
				<dd>
					<textarea rows="10" readonly>{$content|trim}</textarea>
				</dd>
			</dl>
		{/foreach}
	</section>
{/foreach}
