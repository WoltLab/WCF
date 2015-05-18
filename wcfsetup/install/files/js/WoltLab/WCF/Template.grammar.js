

define(function(require){
var o=function(k,v,o,l){for(o=o||{},l=k.length;l--;o[k[l]]=v);return o},$V0=[2,47],$V1=[5,9,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,31,32,33,35,36,37,39,40,41,42,44,46,48],$V2=[1,33],$V3=[1,37],$V4=[1,38],$V5=[1,39],$V6=[1,42],$V7=[1,40],$V8=[1,44],$V9=[11,12,14,15,17,20,21,22,23],$Va=[11,12,14,15,16,17,18,19,20,21,22,23],$Vb=[9,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,31,33,36,39,40,41,42,44,46],$Vc=[28,44,46],$Vd=[12,14];
var parser = {trace: function trace() { },
yy: {},
symbols_: {"error":2,"TEMPLATE":3,"CHUNK_STAR":4,"EOF":5,"CHUNK_STAR_repetition0":6,"CHUNK":7,"PLAIN_ANY":8,"T_LITERAL":9,"COMMAND":10,"T_ANY":11,"}":12,"{":13,"T_WS":14,"]":15,"[":16,")":17,"(":18,".":19,"=":20,"T_VARIABLE":21,"T_VARIABLE_NAME":22,"T_QUOTED_STRING":23,"{if":24,"COMMAND_PARAMETERS":25,"COMMAND_repetition0":26,"COMMAND_option0":27,"{/if}":28,"{include":29,"COMMAND_PARAMETER_LIST":30,"{implode":31,"{/implode}":32,"{foreach":33,"COMMAND_option1":34,"{/foreach}":35,"{lang}":36,"{/lang}":37,"VARIABLE":38,"{#":39,"{@":40,"{ldelim}":41,"{rdelim}":42,"ELSE":43,"{else}":44,"ELSE_IF":45,"{elseif":46,"FOREACH_ELSE":47,"{foreachelse}":48,"VARIABLE_repetition0":49,"VARIABLE_SUFFIX":50,"VARIABLE_SUFFIX_option0":51,"COMMAND_PARAMETER_VALUE":52,"COMMAND_PARAMETERS_repetition_plus0":53,"COMMAND_PARAMETER":54,"$accept":0,"$end":1},
terminals_: {2:"error",5:"EOF",9:"T_LITERAL",11:"T_ANY",12:"}",13:"{",14:"T_WS",15:"]",16:"[",17:")",18:"(",19:".",20:"=",21:"T_VARIABLE",22:"T_VARIABLE_NAME",23:"T_QUOTED_STRING",24:"{if",28:"{/if}",29:"{include",31:"{implode",32:"{/implode}",33:"{foreach",35:"{/foreach}",36:"{lang}",37:"{/lang}",39:"{#",40:"{@",41:"{ldelim}",42:"{rdelim}",44:"{else}",46:"{elseif",48:"{foreachelse}"},
productions_: [0,[3,2],[4,1],[7,1],[7,1],[7,1],[8,1],[8,1],[8,2],[8,1],[8,1],[8,1],[8,1],[8,1],[8,1],[8,1],[8,1],[8,1],[8,1],[10,7],[10,3],[10,5],[10,6],[10,3],[10,3],[10,3],[10,3],[10,1],[10,1],[43,2],[45,4],[47,2],[38,3],[50,3],[50,2],[50,3],[30,5],[30,3],[52,1],[52,1],[25,1],[54,1],[54,1],[54,1],[54,1],[54,1],[54,1],[6,0],[6,2],[26,0],[26,2],[27,0],[27,1],[34,0],[34,1],[49,0],[49,2],[51,0],[51,1],[53,1],[53,2]],
performAction: function anonymous(yytext, yyleng, yylineno, yy, yystate /* action[1] */, $$ /* vstack */, _$ /* lstack */) {
/* this == yyval */

var $0 = $$.length - 1;
switch (yystate) {
case 1:
 return $$[$0-1] + ";"; 
break;
case 2:

	var result = $$[$0].reduce(function (carry, item) {
		if (item.encode && !carry[1]) carry[0] += " + '" + item.value;
		else if (item.encode && carry[1]) carry[0] += item.value;
		else if (!item.encode && carry[1]) carry[0] += "' + " + item.value;
		else if (!item.encode && !carry[1]) carry[0] += " + " + item.value;
		
		carry[1] = item.encode;
		return carry;
	}, [ "''", false ]);
	if (result[1]) result[0] += "'";
	
	this.$ = result[0];

break;
case 3: case 4:
this.$ = { encode: true, value: $$[$0].replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/(\r\n|\n|\r)/g, '\\n') };
break;
case 5:
this.$ = { encode: false, value: $$[$0] };
break;
case 8:
this.$ = $$[$0-1] + $$[$0];
break;
case 19:

		this.$ = "(function() { if (" + $$[$0-5] + ") { return " + $$[$0-3] + "; } " + $$[$0-2].join(' ') + " " + ($$[$0-1] || '') + " return ''; })()";
	
break;
case 20:

		if (!$$[$0-1]['file']) throw new Error('Missing parameter file');
		
		this.$ = $$[$0-1]['file'] + ".fetch(v)";
	
break;
case 21:

		if (!$$[$0-3]['from']) throw new Error('Missing parameter from');
		if (!$$[$0-3]['item']) throw new Error('Missing parameter item');
		if (!$$[$0-3]['glue']) $$[$0-3]['glue'] = "', '";
		
		this.$ = "(function() { return " + $$[$0-3]['from'] + ".map(function(item) { v[" + $$[$0-3]['item'] + "] = item; return " + $$[$0-1] + "; }).join(" + $$[$0-3]['glue'] + "); })()";
	
break;
case 22:

		if (!$$[$0-4]['from']) throw new Error('Missing parameter from');
		if (!$$[$0-4]['item']) throw new Error('Missing parameter item');
		
		this.$ = "(function() {"
		+ "var looped = false, result = '';"
		+ "if (" + $$[$0-4]['from'] + " instanceof Array) {"
			+ "for (var i = 0; i < " + $$[$0-4]['from'] + ".length; i++) { looped = true;"
				+ "v[" + $$[$0-4]['key'] + "] = i;"
				+ "v[" + $$[$0-4]['item'] + "] = " + $$[$0-4]['from'] + "[i];"
				+ "result += " + $$[$0-2] + ";"
			+ "}"
		+ "} else {"
			+ "for (var key in " + $$[$0-4]['from'] + ") {"
				+ "if (!" + $$[$0-4]['from'] + ".hasOwnProperty(key)) continue;"
				+ "looped = true;"
				+ "v[" + $$[$0-4]['key'] + "] = key;"
				+ "v[" + $$[$0-4]['item'] + "] = " + $$[$0-4]['from'] + "[key];"
				+ "result += " + $$[$0-2] + ";"
			+ "}"
		+ "}"
		+ "return (looped ? result : " + ($$[$0-1] || "''") + "); })()"
	
break;
case 23:
this.$ = "WCF.Language.get(" + $$[$0-1] + ")";
break;
case 24:
this.$ = "WCF.String.escapeHTML(" + $$[$0-1] + ")";
break;
case 25:
this.$ = "WCF.String.formatNumeric(" + $$[$0-1] + ")";
break;
case 26:
this.$ = $$[$0-1];
break;
case 27:
this.$ = "'{'";
break;
case 28:
this.$ = "'}'";
break;
case 29:
this.$ = "else { return " + $$[$0] + "; }";
break;
case 30:
this.$ = "else if (" + $$[$0-2] + ") { return " + $$[$0] + "; }";
break;
case 31:
this.$ = $$[$0];
break;
case 32:
this.$ = "v['" + $$[$0-1] + "']" + $$[$0].join('');;
break;
case 33:
this.$ = $$[$0-2] + $$[$0-1] + $$[$0];
break;
case 34:
this.$ = "['" + $$[$0] + "']";
break;
case 35:
this.$ = $$[$0-2] + ($$[$0-1] || '') + $$[$0];
break;
case 36:
 this.$ = $$[$0]; this.$[$$[$0-4]] = $$[$0-2]; 
break;
case 37:
 this.$ = {}; this.$[$$[$0-2]] = $$[$0]; 
break;
case 40:
this.$ = $$[$0].join('');
break;
case 47: case 49: case 55:
this.$ = [];
break;
case 48: case 50: case 56: case 60:
$$[$0-1].push($$[$0]);
break;
case 59:
this.$ = [$$[$0]];
break;
}
},
table: [o([5,9,11,12,13,14,15,16,17,18,19,20,21,22,23,24,29,31,33,36,39,40,41,42],$V0,{3:1,4:2,6:3}),{1:[3]},{5:[1,4]},o([5,28,32,35,37,44,46,48],[2,2],{7:5,8:6,10:8,9:[1,7],11:[1,9],12:[1,10],13:[1,11],14:[1,21],15:[1,12],16:[1,13],17:[1,14],18:[1,15],19:[1,16],20:[1,17],21:[1,18],22:[1,19],23:[1,20],24:[1,22],29:[1,23],31:[1,24],33:[1,25],36:[1,26],39:[1,27],40:[1,28],41:[1,29],42:[1,30]}),{1:[2,1]},o($V1,[2,48]),o($V1,[2,3]),o($V1,[2,4]),o($V1,[2,5]),o($V1,[2,6]),o($V1,[2,7]),{14:[1,31],21:$V2,38:32},o($V1,[2,9]),o($V1,[2,10]),o($V1,[2,11]),o($V1,[2,12]),o($V1,[2,13]),o($V1,[2,14]),o($V1,[2,15]),o($V1,[2,16]),o($V1,[2,17]),o($V1,[2,18]),{11:$V3,14:$V4,20:$V5,21:$V2,22:$V6,23:$V7,25:34,38:41,53:35,54:36},{22:$V8,30:43},{22:$V8,30:45},{22:$V8,30:46},o([9,11,12,13,14,15,16,17,18,19,20,21,22,23,24,29,31,33,36,37,39,40,41,42],$V0,{6:3,4:47}),{21:$V2,38:48},{21:$V2,38:49},o($V1,[2,27]),o($V1,[2,28]),o($V1,[2,8]),{12:[1,50]},{22:[1,51]},{12:[1,52]},o([12,15,17],[2,40],{38:41,54:53,11:$V3,14:$V4,20:$V5,21:$V2,22:$V6,23:$V7}),o($V9,[2,59]),o($V9,[2,41]),o($V9,[2,42]),o($V9,[2,43]),o($V9,[2,44]),o($V9,[2,45]),o($V9,[2,46]),{12:[1,54]},{20:[1,55]},{12:[1,56]},{12:[1,57]},{37:[1,58]},{12:[1,59]},{12:[1,60]},o($V1,[2,24]),o($Va,[2,55],{49:61}),o($Vb,$V0,{6:3,4:62}),o($V9,[2,60]),o($V1,[2,20]),{21:$V2,23:[1,64],38:65,52:63},o([9,11,12,13,14,15,16,17,18,19,20,21,22,23,24,29,31,32,33,36,39,40,41,42],$V0,{6:3,4:66}),o([9,11,12,13,14,15,16,17,18,19,20,21,22,23,24,29,31,33,35,36,39,40,41,42,48],$V0,{6:3,4:67}),o($V1,[2,23]),o($V1,[2,25]),o($V1,[2,26]),o($V9,[2,32],{50:68,16:[1,69],18:[1,71],19:[1,70]}),o($Vc,[2,49],{26:72}),{12:[2,37],14:[1,73]},o($Vd,[2,38]),o($Vd,[2,39]),{32:[1,74]},{34:75,35:[2,53],47:76,48:[1,77]},o($Va,[2,56]),{11:$V3,14:$V4,20:$V5,21:$V2,22:$V6,23:$V7,25:78,38:41,53:35,54:36},{22:[1,79]},{11:$V3,14:$V4,17:[2,57],20:$V5,21:$V2,22:$V6,23:$V7,25:81,38:41,51:80,53:35,54:36},{27:82,28:[2,51],43:84,44:[1,86],45:83,46:[1,85]},{22:$V8,30:87},o($V1,[2,21]),{35:[1,88]},{35:[2,54]},o([9,11,12,13,14,15,16,17,18,19,20,21,22,23,24,29,31,33,35,36,39,40,41,42],$V0,{6:3,4:89}),{15:[1,90]},o($Va,[2,34]),{17:[1,91]},{17:[2,58]},{28:[1,92]},o($Vc,[2,50]),{28:[2,52]},{11:$V3,14:$V4,20:$V5,21:$V2,22:$V6,23:$V7,25:93,38:41,53:35,54:36},o([9,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,31,33,36,39,40,41,42],$V0,{6:3,4:94}),{12:[2,36]},o($V1,[2,22]),{35:[2,31]},o($Va,[2,33]),o($Va,[2,35]),o($V1,[2,19]),{12:[1,95]},{28:[2,29]},o($Vb,$V0,{6:3,4:96}),o($Vc,[2,30])],
defaultActions: {4:[2,1],76:[2,54],81:[2,58],84:[2,52],87:[2,36],89:[2,31],94:[2,29]},
parseError: function parseError(str, hash) {
    if (hash.recoverable) {
        this.trace(str);
    } else {
        throw new Error(str);
    }
},
parse: function parse(input) {
    var self = this, stack = [0], tstack = [], vstack = [null], lstack = [], table = this.table, yytext = '', yylineno = 0, yyleng = 0, recovering = 0, TERROR = 2, EOF = 1;
    var args = lstack.slice.call(arguments, 1);
    var lexer = Object.create(this.lexer);
    var sharedState = { yy: {} };
    for (var k in this.yy) {
        if (Object.prototype.hasOwnProperty.call(this.yy, k)) {
            sharedState.yy[k] = this.yy[k];
        }
    }
    lexer.setInput(input, sharedState.yy);
    sharedState.yy.lexer = lexer;
    sharedState.yy.parser = this;
    if (typeof lexer.yylloc == 'undefined') {
        lexer.yylloc = {};
    }
    var yyloc = lexer.yylloc;
    lstack.push(yyloc);
    var ranges = lexer.options && lexer.options.ranges;
    if (typeof sharedState.yy.parseError === 'function') {
        this.parseError = sharedState.yy.parseError;
    } else {
        this.parseError = Object.getPrototypeOf(this).parseError;
    }
    function popStack(n) {
        stack.length = stack.length - 2 * n;
        vstack.length = vstack.length - n;
        lstack.length = lstack.length - n;
    }
    _token_stack:
        function lex() {
            var token;
            token = lexer.lex() || EOF;
            if (typeof token !== 'number') {
                token = self.symbols_[token] || token;
            }
            return token;
        }
    var symbol, preErrorSymbol, state, action, a, r, yyval = {}, p, len, newState, expected;
    while (true) {
        state = stack[stack.length - 1];
        if (this.defaultActions[state]) {
            action = this.defaultActions[state];
        } else {
            if (symbol === null || typeof symbol == 'undefined') {
                symbol = lex();
            }
            action = table[state] && table[state][symbol];
        }
                    if (typeof action === 'undefined' || !action.length || !action[0]) {
                var errStr = '';
                expected = [];
                for (p in table[state]) {
                    if (this.terminals_[p] && p > TERROR) {
                        expected.push('\'' + this.terminals_[p] + '\'');
                    }
                }
                if (lexer.showPosition) {
                    errStr = 'Parse error on line ' + (yylineno + 1) + ':\n' + lexer.showPosition() + '\nExpecting ' + expected.join(', ') + ', got \'' + (this.terminals_[symbol] || symbol) + '\'';
                } else {
                    errStr = 'Parse error on line ' + (yylineno + 1) + ': Unexpected ' + (symbol == EOF ? 'end of input' : '\'' + (this.terminals_[symbol] || symbol) + '\'');
                }
                this.parseError(errStr, {
                    text: lexer.match,
                    token: this.terminals_[symbol] || symbol,
                    line: lexer.yylineno,
                    loc: yyloc,
                    expected: expected
                });
            }
        if (action[0] instanceof Array && action.length > 1) {
            throw new Error('Parse Error: multiple actions possible at state: ' + state + ', token: ' + symbol);
        }
        switch (action[0]) {
        case 1:
            stack.push(symbol);
            vstack.push(lexer.yytext);
            lstack.push(lexer.yylloc);
            stack.push(action[1]);
            symbol = null;
            if (!preErrorSymbol) {
                yyleng = lexer.yyleng;
                yytext = lexer.yytext;
                yylineno = lexer.yylineno;
                yyloc = lexer.yylloc;
                if (recovering > 0) {
                    recovering--;
                }
            } else {
                symbol = preErrorSymbol;
                preErrorSymbol = null;
            }
            break;
        case 2:
            len = this.productions_[action[1]][1];
            yyval.$ = vstack[vstack.length - len];
            yyval._$ = {
                first_line: lstack[lstack.length - (len || 1)].first_line,
                last_line: lstack[lstack.length - 1].last_line,
                first_column: lstack[lstack.length - (len || 1)].first_column,
                last_column: lstack[lstack.length - 1].last_column
            };
            if (ranges) {
                yyval._$.range = [
                    lstack[lstack.length - (len || 1)].range[0],
                    lstack[lstack.length - 1].range[1]
                ];
            }
            r = this.performAction.apply(yyval, [
                yytext,
                yyleng,
                yylineno,
                sharedState.yy,
                action[1],
                vstack,
                lstack
            ].concat(args));
            if (typeof r !== 'undefined') {
                return r;
            }
            if (len) {
                stack = stack.slice(0, -1 * len * 2);
                vstack = vstack.slice(0, -1 * len);
                lstack = lstack.slice(0, -1 * len);
            }
            stack.push(this.productions_[action[1]][0]);
            vstack.push(yyval.$);
            lstack.push(yyval._$);
            newState = table[stack[stack.length - 2]][stack[stack.length - 1]];
            stack.push(newState);
            break;
        case 3:
            return true;
        }
    }
    return true;
}};

/* generated by jison-lex 0.3.4 */
var lexer = (function(){
var lexer = ({

EOF:1,

parseError:function parseError(str, hash) {
        if (this.yy.parser) {
            this.yy.parser.parseError(str, hash);
        } else {
            throw new Error(str);
        }
    },

// resets the lexer, sets new input
setInput:function (input, yy) {
        this.yy = yy || this.yy || {};
        this._input = input;
        this._more = this._backtrack = this.done = false;
        this.yylineno = this.yyleng = 0;
        this.yytext = this.matched = this.match = '';
        this.conditionStack = ['INITIAL'];
        this.yylloc = {
            first_line: 1,
            first_column: 0,
            last_line: 1,
            last_column: 0
        };
        if (this.options.ranges) {
            this.yylloc.range = [0,0];
        }
        this.offset = 0;
        return this;
    },

// consumes and returns one char from the input
input:function () {
        var ch = this._input[0];
        this.yytext += ch;
        this.yyleng++;
        this.offset++;
        this.match += ch;
        this.matched += ch;
        var lines = ch.match(/(?:\r\n?|\n).*/g);
        if (lines) {
            this.yylineno++;
            this.yylloc.last_line++;
        } else {
            this.yylloc.last_column++;
        }
        if (this.options.ranges) {
            this.yylloc.range[1]++;
        }

        this._input = this._input.slice(1);
        return ch;
    },

// unshifts one char (or a string) into the input
unput:function (ch) {
        var len = ch.length;
        var lines = ch.split(/(?:\r\n?|\n)/g);

        this._input = ch + this._input;
        this.yytext = this.yytext.substr(0, this.yytext.length - len);
        //this.yyleng -= len;
        this.offset -= len;
        var oldLines = this.match.split(/(?:\r\n?|\n)/g);
        this.match = this.match.substr(0, this.match.length - 1);
        this.matched = this.matched.substr(0, this.matched.length - 1);

        if (lines.length - 1) {
            this.yylineno -= lines.length - 1;
        }
        var r = this.yylloc.range;

        this.yylloc = {
            first_line: this.yylloc.first_line,
            last_line: this.yylineno + 1,
            first_column: this.yylloc.first_column,
            last_column: lines ?
                (lines.length === oldLines.length ? this.yylloc.first_column : 0)
                 + oldLines[oldLines.length - lines.length].length - lines[0].length :
              this.yylloc.first_column - len
        };

        if (this.options.ranges) {
            this.yylloc.range = [r[0], r[0] + this.yyleng - len];
        }
        this.yyleng = this.yytext.length;
        return this;
    },

// When called from action, caches matched text and appends it on next action
more:function () {
        this._more = true;
        return this;
    },

// When called from action, signals the lexer that this rule fails to match the input, so the next matching rule (regex) should be tested instead.
reject:function () {
        if (this.options.backtrack_lexer) {
            this._backtrack = true;
        } else {
            return this.parseError('Lexical error on line ' + (this.yylineno + 1) + '. You can only invoke reject() in the lexer when the lexer is of the backtracking persuasion (options.backtrack_lexer = true).\n' + this.showPosition(), {
                text: "",
                token: null,
                line: this.yylineno
            });

        }
        return this;
    },

// retain first n characters of the match
less:function (n) {
        this.unput(this.match.slice(n));
    },

// displays already matched input, i.e. for error messages
pastInput:function () {
        var past = this.matched.substr(0, this.matched.length - this.match.length);
        return (past.length > 20 ? '...':'') + past.substr(-20).replace(/\n/g, "");
    },

// displays upcoming input, i.e. for error messages
upcomingInput:function () {
        var next = this.match;
        if (next.length < 20) {
            next += this._input.substr(0, 20-next.length);
        }
        return (next.substr(0,20) + (next.length > 20 ? '...' : '')).replace(/\n/g, "");
    },

// displays the character position where the lexing error occurred, i.e. for error messages
showPosition:function () {
        var pre = this.pastInput();
        var c = new Array(pre.length + 1).join("-");
        return pre + this.upcomingInput() + "\n" + c + "^";
    },

// test the lexed token: return FALSE when not a match, otherwise return token
test_match:function (match, indexed_rule) {
        var token,
            lines,
            backup;

        if (this.options.backtrack_lexer) {
            // save context
            backup = {
                yylineno: this.yylineno,
                yylloc: {
                    first_line: this.yylloc.first_line,
                    last_line: this.last_line,
                    first_column: this.yylloc.first_column,
                    last_column: this.yylloc.last_column
                },
                yytext: this.yytext,
                match: this.match,
                matches: this.matches,
                matched: this.matched,
                yyleng: this.yyleng,
                offset: this.offset,
                _more: this._more,
                _input: this._input,
                yy: this.yy,
                conditionStack: this.conditionStack.slice(0),
                done: this.done
            };
            if (this.options.ranges) {
                backup.yylloc.range = this.yylloc.range.slice(0);
            }
        }

        lines = match[0].match(/(?:\r\n?|\n).*/g);
        if (lines) {
            this.yylineno += lines.length;
        }
        this.yylloc = {
            first_line: this.yylloc.last_line,
            last_line: this.yylineno + 1,
            first_column: this.yylloc.last_column,
            last_column: lines ?
                         lines[lines.length - 1].length - lines[lines.length - 1].match(/\r?\n?/)[0].length :
                         this.yylloc.last_column + match[0].length
        };
        this.yytext += match[0];
        this.match += match[0];
        this.matches = match;
        this.yyleng = this.yytext.length;
        if (this.options.ranges) {
            this.yylloc.range = [this.offset, this.offset += this.yyleng];
        }
        this._more = false;
        this._backtrack = false;
        this._input = this._input.slice(match[0].length);
        this.matched += match[0];
        token = this.performAction.call(this, this.yy, this, indexed_rule, this.conditionStack[this.conditionStack.length - 1]);
        if (this.done && this._input) {
            this.done = false;
        }
        if (token) {
            return token;
        } else if (this._backtrack) {
            // recover context
            for (var k in backup) {
                this[k] = backup[k];
            }
            return false; // rule action called reject() implying the next rule should be tested instead.
        }
        return false;
    },

// return next match in input
next:function () {
        if (this.done) {
            return this.EOF;
        }
        if (!this._input) {
            this.done = true;
        }

        var token,
            match,
            tempMatch,
            index;
        if (!this._more) {
            this.yytext = '';
            this.match = '';
        }
        var rules = this._currentRules();
        for (var i = 0; i < rules.length; i++) {
            tempMatch = this._input.match(this.rules[rules[i]]);
            if (tempMatch && (!match || tempMatch[0].length > match[0].length)) {
                match = tempMatch;
                index = i;
                if (this.options.backtrack_lexer) {
                    token = this.test_match(tempMatch, rules[i]);
                    if (token !== false) {
                        return token;
                    } else if (this._backtrack) {
                        match = false;
                        continue; // rule action called reject() implying a rule MISmatch.
                    } else {
                        // else: this is a lexer rule which consumes input without producing a token (e.g. whitespace)
                        return false;
                    }
                } else if (!this.options.flex) {
                    break;
                }
            }
        }
        if (match) {
            token = this.test_match(match, rules[index]);
            if (token !== false) {
                return token;
            }
            // else: this is a lexer rule which consumes input without producing a token (e.g. whitespace)
            return false;
        }
        if (this._input === "") {
            return this.EOF;
        } else {
            return this.parseError('Lexical error on line ' + (this.yylineno + 1) + '. Unrecognized text.\n' + this.showPosition(), {
                text: "",
                token: null,
                line: this.yylineno
            });
        }
    },

// return next match that has a token
lex:function lex() {
        var r = this.next();
        if (r) {
            return r;
        } else {
            return this.lex();
        }
    },

// activates a new lexer condition state (pushes the new lexer condition state onto the condition stack)
begin:function begin(condition) {
        this.conditionStack.push(condition);
    },

// pop the previously active lexer condition state off the condition stack
popState:function popState() {
        var n = this.conditionStack.length - 1;
        if (n > 0) {
            return this.conditionStack.pop();
        } else {
            return this.conditionStack[0];
        }
    },

// produce the lexer rule set which is active for the currently active lexer condition state
_currentRules:function _currentRules() {
        if (this.conditionStack.length && this.conditionStack[this.conditionStack.length - 1]) {
            return this.conditions[this.conditionStack[this.conditionStack.length - 1]].rules;
        } else {
            return this.conditions["INITIAL"].rules;
        }
    },

// return the currently active lexer condition state; when an index argument is provided it produces the N-th previous condition state, if available
topState:function topState(n) {
        n = this.conditionStack.length - 1 - Math.abs(n || 0);
        if (n >= 0) {
            return this.conditionStack[n];
        } else {
            return "INITIAL";
        }
    },

// alias for begin(condition)
pushState:function pushState(condition) {
        this.begin(condition);
    },

// return the number of states currently on the stack
stateStackSize:function stateStackSize() {
        return this.conditionStack.length;
    },
options: {},
performAction: function anonymous(yy,yy_,$avoiding_name_collisions,YY_START) {
var YYSTATE=YY_START;
switch($avoiding_name_collisions) {
case 0:/* comment */
break;
case 1: yy_.yytext = yy_.yytext.substring(9, yy_.yytext.length - 10); return 9; 
break;
case 2:return 23;
break;
case 3:return 23;
break;
case 4:return 21;
break;
case 5: return 22; 
break;
case 6:return 19;
break;
case 7:return 16;
break;
case 8:return 15;
break;
case 9:return 18;
break;
case 10:return 17;
break;
case 11:return 20;
break;
case 12:return 41;
break;
case 13:return 42;
break;
case 14:return 39;
break;
case 15:return 40;
break;
case 16: this.begin('command'); return 24; 
break;
case 17: this.begin('command'); return 46; 
break;
case 18: this.begin('command'); return 46; 
break;
case 19:return 44;
break;
case 20:return 28;
break;
case 21:return 36;
break;
case 22:return 37;
break;
case 23: this.begin('command'); return 29; 
break;
case 24: this.begin('command'); return 31; 
break;
case 25:return 32;
break;
case 26: this.begin('command'); return 33; 
break;
case 27:return 48;
break;
case 28:return 35;
break;
case 29:return 13;
break;
case 30: this.popState(); return 12;
break;
case 31:return 12;
break;
case 32:return 14;
break;
case 33:return 5;
break;
case 34:return 11;
break;
}
},
rules: [/^(?:\{\*.*\*\})/,/^(?:\{literal\}.*?\{\/literal\})/,/^(?:"([^"]|\\\.)*")/,/^(?:'([^']|\\\.)*')/,/^(?:\$)/,/^(?:[_a-zA-Z][_a-zA-Z0-9]*)/,/^(?:\.)/,/^(?:\[)/,/^(?:\])/,/^(?:\()/,/^(?:\))/,/^(?:=)/,/^(?:\{ldelim\})/,/^(?:\{rdelim\})/,/^(?:\{#)/,/^(?:\{@)/,/^(?:\{if )/,/^(?:\{else if )/,/^(?:\{elseif )/,/^(?:\{else\})/,/^(?:\{\/if\})/,/^(?:\{lang\})/,/^(?:\{\/lang\})/,/^(?:\{include )/,/^(?:\{implode )/,/^(?:\{\/implode\})/,/^(?:\{foreach )/,/^(?:\{foreachelse\})/,/^(?:\{\/foreach\})/,/^(?:\{)/,/^(?:\})/,/^(?:\})/,/^(?:\s+)/,/^(?:$)/,/^(?:[^{])/],
conditions: {"command":{"rules":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34],"inclusive":true},"INITIAL":{"rules":[0,1,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,31,32,33,34],"inclusive":true}}
});
return lexer;
})();
parser.lexer = lexer;
return parser;
});