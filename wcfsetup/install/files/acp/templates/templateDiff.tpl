{include file='header' pageTitle="wcf.acp.template.diff"}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.template.diff{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $diff}
						<li><a id="requestFullscreen" class="button" style="display: none;"><span class="icon icon16 fa-arrows-alt"></span> <span>{lang}wcf.global.button.fullscreen{/lang}</span></a></li>
						
						{if $parent->templateGroupID}
							<li><a href="{link controller='TemplateEdit' id=$parent->templateID}{/link}" class="button"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.global.button.edit{/lang}</span></a></li>
						{/if}
					{/if}
					
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='formError'}

<form method="post" action="{link controller='TemplateDiff'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.template.group{/lang}</h2>
		
		<dl>
			<dt><label for="parentID">{lang}wcf.acp.template.diff.compareWith{/lang}</label></dt>
			<dd>
				<select name="parentID" id="{lang}wcf.acp.template.group.default{/lang}ID">
					<option value="0"></option>
					{assign var=depth value=0}
					{foreach from=$templateGroupHierarchy item='templateGroup' key='templateGroupID'}
						<option{if $templateGroup[hasTemplate] !== false && $templateGroup[hasTemplate] != $templateID} value="{$templateGroup[hasTemplate]}"{if $parent->templateID == $templateGroup[hasTemplate]} selected="selected"{/if}{else} disabled="disabled"{/if}>{@'&nbsp;'|str_repeat:$depth * 4}{if $templateGroupID}{$templateGroup[group]->templateGroupName}{else}{lang}wcf.acp.template.group.default{/lang}{/if}</option>
						{assign var=depth value=$depth + 1}
					{/foreach}
				</select>
			</dd>
		</dl>
	</section>
	
	<div class="formSubmit">
		<input type="hidden" name="id" value="{$templateID}" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
	</div>
</form>

{if $diff}
	<div id="fullscreenContainer">
		<div class="sideBySide">
			<div class="section">
				<header class="sectionHeader">
					<h2 class="sectionTitle">
						{if $parent->templateGroupID}{$templateGroupHierarchy[$parent->templateGroupID][group]->templateGroupName}{else}{lang}wcf.acp.template.group.default{/lang}{/if}
					</h2>
					<p class="sectionDescription">{lang}wcf.acp.template.lastModificationTime{/lang}: {@$parent->lastModificationTime|time}</p>
				</header>
				
				{assign var=removeOffset value=0}
				{assign var=lineNo value=0}
				<pre id="left" class="monospace" style="overflow: auto; height: 700px;">{*
					*}<ol class="nativeList">{*
						*}{foreach from=$diff->getRawDiff() item='line'}{*
							*}{if $line[0] == ' '}{*
								*}{assign var=removeOffset value=0}{assign var=lineNo value=$lineNo + 1}{*
								*}<li value="{@$lineNo}" style="margin: 0">{$line[1]}</li>{*
							*}{elseif $line[0] == '-'}{*
								*}{assign var=removeOffset value=$removeOffset + 1}{assign var=lineNo value=$lineNo + 1}{*
								*}<li value="{@$lineNo}" style="color: red;margin: 0">{$line[1]}</li>{*
							*}{elseif $line[0] == '+'}{*
								*}{assign var=removeOffset value=$removeOffset - 1}{*
								*}{if $removeOffset < 0}<li style="list-style-type: none;margin: 0">&nbsp;</li>{/if}{*
							*}{/if}{*
						*}{/foreach}{*
					*}</ol>{*
				*}</pre>
			</div>
			<div class="section">
				<header class="sectionHeader">
					<h2 class="sectionTitle">
						{if $template->templateGroupID}{$templateGroupHierarchy[$template->templateGroupID][group]->templateGroupName}{else}{lang}wcf.acp.template.group.default{/lang}{/if}
					</h2>
					<p class="sectionDescription">{lang}wcf.acp.template.lastModificationTime{/lang}: {@$template->lastModificationTime|time}</p>
				</header>
				{assign var=removeOffset value=0}
				{assign var=lineNo value=0}
				<pre id="right" class="monospace" style="overflow: auto; height: 700px;">{*
					*}<ol class="nativeList">{*
						*}{foreach from=$diff->getRawDiff() item='line'}{*
							*}{if $line[0] == ' '}{*
								*}{if $removeOffset > 0}{*
									*}{@'<li style="list-style-type: none;margin: 0">&nbsp;</li>'|str_repeat:$removeOffset}{*
								*}{/if}{*
								*}{assign var=removeOffset value=0}{assign var=lineNo value=$lineNo + 1}{*
								*}<li value="{@$lineNo}" style="margin: 0">{$line[1]}</li>{*
							*}{elseif $line[0] == '-'}{*
								*}{assign var=removeOffset value=$removeOffset + 1}{*
							*}{elseif $line[0] == '+'}{*
								*}{assign var=removeOffset value=$removeOffset - 1}{assign var=lineNo value=$lineNo + 1}{*
								*}<li value="{@$lineNo}" style="color: green; margin: 0">{$line[1]}</li>{*
							*}{/if}{*
						*}{/foreach}{*
					*}</ol>{*
				*}</pre>
				
				<footer class="contentFooter">
					<nav class="contentFooterNavigation">
						<ul>
							<li><a href="{link controller='TemplateEdit' id=$template->templateID}{/link}" class="button"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.global.button.edit{/lang}</span></a></li>
							
							{event name='contentFooterNavigation'}
						</ul>
					</nav>
				</footer>
			</div>
		</div>
	</div>
	<script data-relocate="true">
	$(function() {
		if (WCF.System.Fullscreen.isSupported()) {
			$('#requestFullscreen').show();
		}
		
		// force that both containers have got the same width
		var max = Math.max($('#left > ol').prop('scrollWidth'), $('#right > ol').prop('scrollWidth'));
		$('#left > ol').width(max);
		$('#right > ol').width(max);
		
		// sync scrolling
		var sync = $('#left, #right');
		function syncPosition(event) {
			var other = sync.not(this);
			other.off('scroll');
			other.prop('scrollLeft', $(this).prop('scrollLeft'));
			other.prop('scrollTop', $(this).prop('scrollTop'));
			setTimeout(function () { other.on('scroll', syncPosition); }, 150);
		}
		
		sync.on('scroll', syncPosition);
		
		$('#requestFullscreen').on('click', function() {
			var element = $('#fullscreenContainer')[0];
			WCF.System.Fullscreen.toggleFullscreen(element);
		});
	});
	</script>
{/if}

{include file='footer'}
