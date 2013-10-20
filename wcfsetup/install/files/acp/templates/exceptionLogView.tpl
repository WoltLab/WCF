{include file='header' pageTitle='wcf.acp.exceptionLog'}
<script data-relocate="true">
	//<![CDATA[
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
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.exceptionLog{/lang}</h1>
</header>

{include file='formError'}

{if !$logFiles|empty}
	<form method="get" action="{link controller='ExceptionLogView'}{/link}">
		<div class="container containerPadding marginTop">
			<fieldset><legend>{lang}wcf.acp.exceptionLog.search{/lang}</legend>
				<dl>
					<dt><label for="exceptionID">{lang}wcf.acp.exceptionLog.search.exceptionID{/lang}</label></dt>
					<dd>
						<input type="text" id="exceptionID" name="exceptionID" value="{$exceptionID}" autofocus="autofocus" class="long" />
					</dd>
				</dl>
				<dl>
					<dt><label for="logFile">{lang}wcf.acp.exceptionLog.search.logFile{/lang}</label></dt>
					<dd>
						<select id="logFile" name="logFile">
							{htmlOptions options=$logFiles selected=$logFile}
						</select>
					</dd>
				</dl>
			</fieldset>
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SID_INPUT_TAG}
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</form>
{/if}

<div class="contentNavigation">
	{pages print=true controller="ExceptionLogView" link="pageNo=%d&logFile=$logFile"}
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{if !$logFiles|empty}
	{if $logFile}
		{foreach from=$exceptions item='exception' key='exceptionKey'}
			<div id="{$exceptionKey}" class="container containerPadding marginTop">
				<fieldset>
					<legend>{$exception[message]}</legend>
					
					<dl>
						<dt>{lang}wcf.acp.exceptionLog.exception.date{/lang}</dt>
						<dd>{$exception[date]|strtotime|plainTime}</dd>
					</dl>
					
					<dl>
						<dt>{lang}wcf.acp.exceptionLog.exception.file{/lang}</dt>
						<dd>{$exception[file]} ({$exception[line]})</dd>
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
					{if $exception[information]}
						<dl>
							<dt>{lang}wcf.acp.exceptionLog.exception.information{/lang}</dt>
							<dd>{@$exception[information]}</dd>
						</dl>
					{/if}
					<dl>
						<dt>{lang}wcf.acp.exceptionLog.exception.stacktrace{/lang}</dt>
						<dd style="font-family: monospace; word-wrap: wrap-all; word-break: break-all;">
							<ul>
								<li>{@"</li><li>"|implode:$exception[stacktrace]}</li>
							</ul>
						</dd>
					</dl>
					<dl>
						<dt><label for="copyException{$exceptionKey}">{lang}wcf.acp.exceptionLog.exception.copy{/lang}</label></dt>
						<dd><textarea id="copyException{$exceptionKey}" rows="5" cols="40" class="jsCopyException" readonly="readonly">{$exception[0]}</textarea></dd>
					</dl>
				</fieldset>
			</div>
		{/foreach}
	{elseif $exceptionID}
		<p class="error">{lang}wcf.acp.exceptionLog.exceptionNotFound{/lang}</p>
	{/if}
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
