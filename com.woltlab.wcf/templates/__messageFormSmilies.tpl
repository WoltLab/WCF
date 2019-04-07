<ul class="inlineList smileyList">
	{foreach from=$smilies item=smiley}
		<li><a class="jsSmiley">{@$smiley->getHtml('jsTooltip')}</a></li>
	{/foreach}
</ul>