{hascontent}
	<ul class="tagList">
		{content}
			{foreach from=$tags item=tag}
				<li><a href="{link controller='Tagged' object=$tag}{/link}" rel="tag" style="font-size: {@$tag->getSize()}%;">{$tag->name}</a></li>
			{/foreach}
		{/content}
	</ul>
{/hascontent}