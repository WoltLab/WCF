<p>{lang}wcf.acp.package.enableUpgradeOverride.issues{/lang}</p>
<ul class="nativeList">
	{foreach from=$issues item='issue'}
		<li>
			<strong>{unsafe:$issue['title']}</strong><br>
			{unsafe:$issue['description']}
		</li>
	{/foreach}
</ul>
