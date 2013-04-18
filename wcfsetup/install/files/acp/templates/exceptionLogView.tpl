{include file='header' pageTitle='wcf.acp.exceptionLog'}
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		{if $exceptionID}window.location.hash = '{$exceptionID|encodeJS}';{/if}
		WCF.Collapsible.Simple.init();
		
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
	<hgroup>
		<h1>{lang}wcf.acp.exceptionLog{/lang}</h1>
	</hgroup>
</header>

<form action="{link controller='ExceptionLogView'}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset><legend>{lang}wcf.acp.exceptionLog.search{/lang}</legend>
			<dl>
				<dt><label for="exceptionID">{lang}wcf.acp.exceptionLog.search.exceptionID{/lang}</label></dt>
				<dd>
					<input type="search" id="exceptionID" name="exceptionID" value="{$exceptionID}" autofocus="autofocus" class="medium" />
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
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SID_INPUT_TAG}
		</div>
	</div>
</form>

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

{if $logFile}
	{foreach from=$exceptions item='exception' key='exceptionKey'}
		<div class="tabularBox tabularBoxTitle marginTop" id="{$exceptionKey}">
			<hgroup>
				<h1><a class="jsCollapsible jsTooltip" data-is-open="{if $exceptionKey == $exceptionID}1{else}0{/if}" data-collapsible-container="exception_{$exceptionKey}" title="{lang}wcf.global.button.collapsible{/lang}" class="jsTooltip"><span class="icon icon16 icon-chevron-{if $exceptionKey == $exceptionID}down{else}right{/if}"></span></a> {$exception[message]}</h1>
				<h2>{$exception[date]|strtotime|plainTime}</h2>
			</hgroup>
			
			<div id="exception_{$exceptionKey}" class="container containerPadding" {if $exceptionKey != $exceptionID} style="display: none;"{/if}>
				<dl>
					<dt>{lang}wcf.acp.exceptionLog.exception.file{/lang}</dt>
					<dd>{$exception[file]} ({$exception[line]})</dd>
				</dl>
				<dl>
					<dt>{lang}wcf.acp.exceptionLog.exception.phpVersion{/lang}</dt>
					<dd>{$exception[phpVersion]}</dd>
				</dl>
				<dl>
					<dt>{lang}wcf.acp.exceptionLog.exception.wcfVersion{/lang}</dt>
					<dd>{$exception[wcfVersion]}</dd>
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
					<dt>{lang}wcf.acp.exceptionLog.exception.stacktrace{/lang}</dt>
					<dd style="font-family: monospace; word-wrap: wrap-all; word-break: break-all;">
						<ul>
							<li>{@"</li><li>"|implode:$exception[stacktrace]}</li>
						</ul>
					</dd>
				</dl>
				<dl>
					<dt>{lang}wcf.acp.exceptionLog.exception.copy{/lang}</dt>
					<dd><textarea rows="5" cols="40" class="jsCopyException" readonly="readonly">{$exception[0]}</textarea></dd>
				</dl>
			</div>
		</div>
	{/foreach}
{elseif $exceptionID}
	<p class="error">{lang}wcf.acp.exceptionLog.exceptionNotFound{/lang}</p>
{/if}
{include file='footer'}
