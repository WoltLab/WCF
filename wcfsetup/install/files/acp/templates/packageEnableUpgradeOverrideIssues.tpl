<p>{lang}wcf.acp.package.enableUpgradeOverride.issues{/lang}</p>
<ul class="nativeList">
	{foreach from=$issues item='issue'}
		<li>
			<strong>{@$issue['title']}</strong><br>
			{@$issue['description']}
		</li>
	{/foreach}
</ul>
