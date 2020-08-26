WCF.Language.addObject({
	'wcf.message.quote.insertAllQuotes': '{jslang}wcf.message.quote.insertAllQuotes{/jslang}',
	'wcf.message.quote.insertSelectedQuotes': '{jslang}wcf.message.quote.insertSelectedQuotes{/jslang}',
	'wcf.message.quote.manageQuotes': '{jslang}wcf.message.quote.manageQuotes{/jslang}',
	'wcf.message.quote.quoteSelected': '{jslang}wcf.message.quote.quoteSelected{/jslang}',
	'wcf.message.quote.quoteAndReply': '{jslang}wcf.message.quote.quoteAndReply{/jslang}',
	'wcf.message.quote.removeAllQuotes': '{jslang}wcf.message.quote.removeAllQuotes{/jslang}',
	'wcf.message.quote.removeSelectedQuotes': '{jslang}wcf.message.quote.removeSelectedQuotes{/jslang}',
	'wcf.message.quote.showQuotes': '{jslang __literal=true}wcf.message.quote.showQuotes{/jslang}'
});

{if !$wysiwygSelector|isset}{assign var=wysiwygSelector value=''}{/if}
{if !$supportPaste|isset}{assign var=supportPaste value=false}{/if}
var $quoteManager = new WCF.Message.Quote.Manager({@$__quoteCount}, '{$wysiwygSelector|encodeJS}', {if $supportPaste}true{else}false{/if}, [ {implode from=$__quoteRemove item=quoteID}'{$quoteID}'{/implode} ]);
