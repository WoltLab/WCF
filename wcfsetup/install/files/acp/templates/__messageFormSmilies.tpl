<ul class="inlineList smileyList">
	{foreach from=$smilies item=smiley}
		<li><a title="{lang}{$smiley->smileyTitle}{/lang}" class="jsTooltip jsSmiley">{@$smiley->getHtml()}</a></li>
	{/foreach}
</ul>