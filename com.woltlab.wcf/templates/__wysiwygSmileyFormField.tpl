<ul class="inlineList smileyList">
	{foreach from=$field->getSmilies() item=smiley}
		<li><a title="{lang}{$smiley->smileyTitle}{/lang}" class="jsTooltip jsSmiley">{@$smiley->getHtml()}</a></li>
	{/foreach}
</ul>
