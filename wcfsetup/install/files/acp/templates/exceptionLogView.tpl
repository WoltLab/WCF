{include file='header' pageTitle='wcf.acp.exceptionLog'}
<script data-relocate="true">
	$(function() {
		{if $exceptionID}window.location.hash = '{$exceptionID|encodeJS}';{/if}
		
		$('#exceptionID').on('keyup keydown keypress', function () {
			if ($.trim($(this).val()) == '') {
				$('#logFile').enable().parents('dl').removeClass('disabled');
			}
			else {
				$('#logFile').disable().parents('dl').addClass('disabled');
			}
		}).trigger('keypress');
		
		$('.jsCopyException').click(function () {
			$(this).select();
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.exceptionLog{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='shared_formError'}

{if !$logFiles|empty}
	<form method="post" action="{link controller='ExceptionLogView'}{/link}">
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.acp.exceptionLog.search{/lang}</h2>
			
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-4">
					<dt></dt>
					<dd>
						<input type="text" id="exceptionID" name="exceptionID" value="{$exceptionID}" placeholder="{lang}wcf.acp.exceptionLog.search.exceptionID{/lang}" autofocus class="long">
					</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-4">
					<dt></dt>
					<dd>
						<select id="logFile" name="logFile">
							<option value="">{lang}wcf.acp.exceptionLog.search.logFile{/lang}</option>
							{htmlOptions options=$logFiles selected=$logFile}
						</select>
					</dd>
				</dl>
			</div>
		</section>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		</div>
	</form>
{/if}

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="ExceptionLogView" link="pageNo=%d&logFile=$logFile"}{/content}
	</div>
{/hascontent}

{if !$logFiles|empty}
	{if $logFile}
		{foreach from=$exceptions item='exception' key='exceptionKey'}
			<details id="{$exceptionKey}" class="section exceptionContainer"{if $exception[collapsed]|empty} open{/if}>
				<summary class="sectionTitle">
					{$exception[message]}
				</summary>
				
				<div class="exceptionDetails">
					<dl>
						<dt>{lang}wcf.acp.exceptionLog.exception.date{/lang}</dt>
						<dd>{$exception[date]|plainTime}</dd>
					</dl>
					
					<dl>
						<dt>{lang}wcf.acp.exceptionLog.exception.requestURI{/lang}</dt>
						<dd>{$exception[requestURI]}</dd>
					</dl>
					<dl>
						<dt>{lang}wcf.acp.exceptionLog.exception.referrer{/lang}</dt>
						<dd>{$exception[referrer]}</dd>
					</dl>
					<dl>
						<dt>{lang}wcf.acp.exceptionLog.exception.userAgent{/lang}</dt>
						<dd>{$exception[userAgent]}</dd>
					</dl>
					<dl>
						<dt>{lang}wcf.acp.exceptionLog.exception.memory{/lang}</dt>
						<dd>{$exception[peakMemory]|filesizeBinary} / {if $exception[maxMemory] == -1}&infin;{else}{$exception[maxMemory]|filesizeBinary}{/if}</dd>
					</dl>
					{foreach from=$exception[chain] item=chain}
					<dl>
						<dt>{lang}wcf.acp.exceptionLog.exception.message{/lang}</dt>
						<dd>{$chain[message]}</dd>
					</dl>
					<dl>
						<dt>{lang}wcf.acp.exceptionLog.exception.class{/lang}</dt>
						<dd>{$chain[class]}</dd>
					</dl>
					<dl>
						<dt>{lang}wcf.acp.exceptionLog.exception.file{/lang}</dt>
						<dd>{$chain[file]} ({$chain[line]})</dd>
					</dl>
					{if !$chain[information]|empty}
						{foreach from=$chain[information] item=extraInformation}
							<dl>
								<dt>{$extraInformation[0]}</dt>
								<dd style="white-space: pre-wrap;">{$extraInformation[1]}</dd>
							</dl>
						{/foreach}
					{/if}
					<dl>
						<dt>{lang}wcf.acp.exceptionLog.exception.stacktrace{/lang}</dt>
						<dd>
							<ol start="0" class="nativeList">
								{foreach from=$chain[stack] item=stack}
								<li>{$stack[file]} ({$stack[line]}): {$stack[class]}{$stack[type]}{$stack[function]}(&hellip;)</li>
								{/foreach}
							</ol>
						</dd>
					</dl>
					{/foreach}
					<dl>
						<dt><label for="copyException{$exceptionKey}">{lang}wcf.acp.exceptionLog.exception.copy{/lang}</label></dt>
						<dd><textarea id="copyException{$exceptionKey}" rows="5" cols="40" class="jsCopyException" readonly>{$exception[0]}</textarea></dd>
					</dl>
				</div>
			</details>
		{/foreach}

		<footer class="contentFooter">
			{hascontent}
				<div class="paginationBottom">
					{content}{@$pagesLinks}{/content}
				</div>
			{/hascontent}
		</footer>
	{elseif $exceptionID}
		<woltlab-core-notice type="error">{lang}wcf.acp.exceptionLog.exceptionNotFound{/lang}</woltlab-core-notice>
	{/if}
{else}
	<woltlab-core-notice type="info">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}
