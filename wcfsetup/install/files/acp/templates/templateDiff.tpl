{include file='header' pageTitle="wcf.acp.template.diff"}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.template.diff{/lang}</h1>
		<p class="contentHeaderDescription">{$template->templateName}</p>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $diff !== null}
						{if $parent->templateGroupID}
							<li><a href="{link controller='TemplateEdit' id=$parent->templateID}{/link}" class="button">{icon name='pencil'} <span>{lang}wcf.global.button.edit{/lang}</span></a></li>
						{/if}
					{/if}
					
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='shared_formError'}

<form method="post" action="{link controller='TemplateDiff'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.template.group{/lang}</h2>
		
		<dl>
			<dt><label for="parentID">{lang}wcf.acp.template.diff.compareWith{/lang}</label></dt>
			<dd>
				<select name="parentID" id="parentID">
					<option value="0"></option>
					{assign var=depth value=0}
					{foreach from=$templateGroupHierarchy item='templateGroup' key='templateGroupID'}
						<option{if $templateGroup[hasTemplate] !== false && $templateGroup[hasTemplate] != $templateID} value="{$templateGroup[hasTemplate]}"{if $parent->templateID == $templateGroup[hasTemplate]} selected{/if}{else} disabled{/if}>{@'&nbsp;'|str_repeat:$depth * 4}{if $templateGroupID}{$templateGroup[group]->getName()}{else}{lang}wcf.acp.template.group.default{/lang}{/if}</option>
						{assign var=depth value=$depth + 1}
					{/foreach}
				</select>
			</dd>
		</dl>
	</section>
	
	<div class="formSubmit">
		<input type="hidden" name="id" value="{$templateID}">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
	</div>
</form>

{if $diff !== null}
	<div id="fullscreenContainer">
		<div class="sideBySide templateDiff">
			<div class="section">
				<header class="sectionHeader">
					<h2 class="sectionTitle">
						{if $parent->templateGroupID}{$templateGroupHierarchy[$parent->templateGroupID][group]->getName()}{else}{lang}wcf.acp.template.group.default{/lang}{/if}
					</h2>
					<p class="sectionDescription">{lang}wcf.acp.template.lastModificationTime{/lang}: {@$parent->lastModificationTime|time}</p>
				</header>
				
				{assign var=removeOffset value=0}
				{assign var=lineNo value=0}
				<pre id="left" class="monospace" style="overflow: auto; height: 700px;">{*
					*}<span style="display: inline-block;">{* <-- wrapper span to prevent content from overflowing the <li>
						*}<ol class="nativeList">{*
							*}{foreach from=$diff item='line'}{*
								*}{if $line[0] == ' '}{*
									*}{assign var=removeOffset value=0}{assign var=lineNo value=$lineNo + 1}{*
									*}<li value="{$lineNo}">{$line[1]}</li>{*
								*}{elseif $line[0] == '-'}{*
									*}{assign var=removeOffset value=$removeOffset + 1}{assign var=lineNo value=$lineNo + 1}{*
									*}<li value="{$lineNo}" class="templateDiff--removed">{$line[1]}</li>{*
								*}{elseif $line[0] == '+'}{*
									*}{assign var=removeOffset value=$removeOffset - 1}{*
									*}{if $removeOffset < 0}<li style="list-style-type: none">&nbsp;</li>{/if}{*
								*}{/if}{*
							*}{/foreach}{*
						*}</ol>{*
					*}</span>{*
				*}</pre>
			</div>
			<div class="section">
				<header class="sectionHeader">
					<h2 class="sectionTitle">
						{if $template->templateGroupID}{$templateGroupHierarchy[$template->templateGroupID][group]->getName()}{else}{lang}wcf.acp.template.group.default{/lang}{/if}
					</h2>
					<p class="sectionDescription">{lang}wcf.acp.template.lastModificationTime{/lang}: {@$template->lastModificationTime|time}</p>
				</header>
				{assign var=removeOffset value=0}
				{assign var=lineNo value=0}
				<pre id="right" class="monospace" style="overflow: auto; height: 700px;">{*
					*}<span style="display: inline-block;">{* <-- wrapper span to prevent content from overflowing the <li>
						*}<ol class="nativeList">{*
							*}{foreach from=$diff item='line'}{*
								*}{if $line[0] == ' '}{*
									*}{if $removeOffset > 0}{*
										*}{@'<li style="list-style-type: none">&nbsp;</li>'|str_repeat:$removeOffset}{*
									*}{/if}{*
									*}{assign var=removeOffset value=0}{assign var=lineNo value=$lineNo + 1}{*
									*}<li value="{$lineNo}">{$line[1]}</li>{*
								*}{elseif $line[0] == '-'}{*
									*}{assign var=removeOffset value=$removeOffset + 1}{*
								*}{elseif $line[0] == '+'}{*
									*}{assign var=removeOffset value=$removeOffset - 1}{assign var=lineNo value=$lineNo + 1}{*
									*}<li value="{$lineNo}" class="templateDiff--added">{$line[1]}</li>{*
								*}{/if}{*
							*}{/foreach}{*
						*}</ol>{*
					*}</span>{*
				*}</pre>
				
				<footer class="contentFooter">
					<nav class="contentFooterNavigation">
						<ul>
							<li><a href="{link controller='TemplateEdit' id=$template->templateID}{/link}" class="button">{icon name='pencil'} <span>{lang}wcf.global.button.edit{/lang}</span></a></li>
							
							{event name='contentFooterNavigation'}
						</ul>
					</nav>
				</footer>
			</div>
		</div>
	</div>
	<script data-relocate="true">
	$(function() {
		// sync scrolling
		var sync = $('#left, #right');
		function syncPosition() {
			var other = sync.not(this);
			other.off('scroll');
			other.prop('scrollLeft', $(this).prop('scrollLeft'));
			other.prop('scrollTop', $(this).prop('scrollTop'));
			setTimeout(function () { other.on('scroll', syncPosition); }, 150);
		}
		
		sync.on('scroll', syncPosition);
	});
	</script>
{/if}

{include file='footer'}
