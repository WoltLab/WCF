<dl>
	<dt></dt>
	<dd>
		<button type="button" class="button jsCopyButton">{lang}wcf.acp.exceptionLog.exception.copy{/lang}</button>
		<textarea rows="5" cols="40" class="jsCopyException" hidden>{$exception[0]}</textarea>	
	</dd>
</dl>
<dl>
	<dt>{lang}wcf.acp.exceptionLog.search.exceptionID{/lang}</dt>
	<dd>{$exceptionID}</dd>
</dl>
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
