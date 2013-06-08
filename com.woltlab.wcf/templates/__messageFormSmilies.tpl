<ul class="smileyList">
	{foreach from=$smilies item=smiley}
		<li><a title="{lang}{$smiley->smileyTitle}{/lang}" class="jsTooltip jsSmiley" data-smiley-code="{$smiley->smileyCode}"><img src="{$smiley->getURL()}" alt="{$smiley->smileyCode}" class="icon24" /></a></li>
	{/foreach}
</ul>