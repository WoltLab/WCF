

define(function(require){
var o=function(k,v,o,l){for(o=o||{},l=k.length;l--;o[k[l]]=v);return o},$V0=[2,37],$V1=[5,9,11,12,13,18,19,21,22,23,25,26,27,28,30,31,32,33,35,37,39],$V2=[1,24],$V3=[1,25],$V4=[1,31],$V5=[1,29],$V6=[1,30],$V7=[1,26],$V8=[1,27],$V9=[1,33],$Va=[11,12,15,40,41,45,47,49,50,52],$Vb=[9,11,12,13,18,19,21,23,26,28,30,31,32,33,35,37],$Vc=[11,12,15,40,41,44,45,46,47,49,50,52],$Vd=[18,35,37],$Ve=[12,15];
var parser = {trace: function trace() { },
yy: {},
symbols_: {"error":2,"TEMPLATE":3,"CHUNK_STAR":4,"EOF":5,"CHUNK_STAR_repetition0":6,"CHUNK":7,"PLAIN_ANY":8,"T_LITERAL":9,"COMMAND":10,"T_ANY":11,"T_WS":12,"{if":13,"COMMAND_PARAMETERS":14,"}":15,"COMMAND_repetition0":16,"COMMAND_option0":17,"{/if}":18,"{include":19,"COMMAND_PARAMETER_LIST":20,"{implode":21,"{/implode}":22,"{foreach":23,"COMMAND_option1":24,"{/foreach}":25,"{lang}":26,"{/lang}":27,"{":28,"VARIABLE":29,"{#":30,"{@":31,"{ldelim}":32,"{rdelim}":33,"ELSE":34,"{else}":35,"ELSE_IF":36,"{elseif":37,"FOREACH_ELSE":38,"{foreachelse}":39,"T_VARIABLE":40,"T_VARIABLE_NAME":41,"VARIABLE_repetition0":42,"VARIABLE_SUFFIX":43,"[":44,"]":45,".":46,"(":47,"VARIABLE_SUFFIX_option0":48,")":49,"=":50,"COMMAND_PARAMETER_VALUE":51,"T_QUOTED_STRING":52,"COMMAND_PARAMETERS_repetition_plus0":53,"COMMAND_PARAMETER":54,"$accept":0,"$end":1},
terminals_: {2:"error",5:"EOF",9:"T_LITERAL",11:"T_ANY",12:"T_WS",13:"{if",15:"}",18:"{/if}",19:"{include",21:"{implode",22:"{/implode}",23:"{foreach",25:"{/foreach}",26:"{lang}",27:"{/lang}",28:"{",30:"{#",31:"{@",32:"{ldelim}",33:"{rdelim}",35:"{else}",37:"{elseif",39:"{foreachelse}",40:"T_VARIABLE",41:"T_VARIABLE_NAME",44:"[",45:"]",46:".",47:"(",49:")",50:"=",52:"T_QUOTED_STRING"},
productions_: [0,[3,2],[4,1],[7,1],[7,1],[7,1],[8,1],[8,1],[10,7],[10,3],[10,5],[10,6],[10,3],[10,3],[10,3],[10,3],[10,1],[10,1],[34,2],[36,4],[38,2],[29,3],[43,3],[43,2],[43,3],[20,5],[20,3],[51,1],[51,1],[14,1],[54,1],[54,1],[54,1],[54,1],[54,1],[54,1],[54,3],[6,0],[6,2],[16,0],[16,2],[17,0],[17,1],[24,0],[24,1],[42,0],[42,2],[48,0],[48,1],[53,1],[53,2]],
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

		this.$ = "(function() { if (" + $$[$0-5] + ") { return " + $$[$0-3] + "; } " + $$[$0-2].join(' ') + " " + ($$[$0-1] || '') + " return ''; })()";
	
break;
case 9:

		if (!$$[$0-1]['file']) throw new Error('Missing parameter file');
		
		this.$ = $$[$0-1]['file'] + ".fetch(v)";
	
break;
case 10:

		if (!$$[$0-3]['from']) throw new Error('Missing parameter from');
		if (!$$[$0-3]['item']) throw new Error('Missing parameter item');
		if (!$$[$0-3]['glue']) $$[$0-3]['glue'] = "', '";
		
		this.$ = "(function() { return " + $$[$0-3]['from'] + ".map(function(item) { v[" + $$[$0-3]['item'] + "] = item; return " + $$[$0-1] + "; }).join(" + $$[$0-3]['glue'] + "); })()";
	
break;
case 11:

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
case 12:
this.$ = "Language.get(" + $$[$0-1] + ")";
break;
case 13:
this.$ = "StringUtil.escapeHTML(" + $$[$0-1] + ")";
break;
case 14:
this.$ = "StringUtil.formatNumeric(" + $$[$0-1] + ")";
break;
case 15:
this.$ = $$[$0-1];
break;
case 16:
this.$ = "'{'";
break;
case 17:
this.$ = "'}'";
break;
case 18:
this.$ = "else { return " + $$[$0] + "; }";
break;
case 19:
this.$ = "else if (" + $$[$0-2] + ") { return " + $$[$0] + "; }";
break;
case 20:
this.$ = $$[$0];
break;
case 21:
this.$ = "v['" + $$[$0-1] + "']" + $$[$0].join('');;
break;
case 22:
this.$ = $$[$0-2] + $$[$0-1] + $$[$0];
break;
case 23:
this.$ = "['" + $$[$0] + "']";
break;
case 24: case 36:
this.$ = $$[$0-2] + ($$[$0-1] || '') + $$[$0];
break;
case 25:
 this.$ = $$[$0]; this.$[$$[$0-4]] = $$[$0-2]; 
break;
case 26:
 this.$ = {}; this.$[$$[$0-2]] = $$[$0]; 
break;
case 29:
this.$ = $$[$0].join('');
break;
case 37: case 39: case 45:
this.$ = [];
break;
case 38: case 40: case 46: case 50:
$$[$0-1].push($$[$0]);
break;
case 49:
this.$ = [$$[$0]];
break;
}
},
table: [o([5,9,11,12,13,19,21,23,26,28,30,31,32,33],$V0,{3:1,4:2,6:3}),{1:[3]},{5:[1,4]},o([5,18,22,25,27,35,37,39],[2,2],{7:5,8:6,10:8,9:[1,7],11:[1,9],12:[1,10],13:[1,11],19:[1,12],21:[1,13],23:[1,14],26:[1,15],28:[1,16],30:[1,17],31:[1,18],32:[1,19],33:[1,20]}),{1:[2,1]},o($V1,[2,38]),o($V1,[2,3]),o($V1,[2,4]),o($V1,[2,5]),o($V1,[2,6]),o($V1,[2,7]),{11:$V2,12:$V3,14:21,29:28,40:$V4,41:$V5,47:$V6,50:$V7,52:$V8,53:22,54:23},{20:32,41:$V9},{20:34,41:$V9},{20:35,41:$V9},o([9,11,12,13,19,21,23,26,27,28,30,31,32,33],$V0,{6:3,4:36}),{29:37,40:$V4},{29:38,40:$V4},{29:39,40:$V4},o($V1,[2,16]),o($V1,[2,17]),{15:[1,40]},o([15,45,49],[2,29],{29:28,54:41,11:$V2,12:$V3,40:$V4,41:$V5,47:$V6,50:$V7,52:$V8}),o($Va,[2,49]),o($Va,[2,30]),o($Va,[2,31]),o($Va,[2,32]),o($Va,[2,33]),o($Va,[2,34]),o($Va,[2,35]),{11:$V2,12:$V3,14:42,29:28,40:$V4,41:$V5,47:$V6,50:$V7,52:$V8,53:22,54:23},{41:[1,43]},{15:[1,44]},{50:[1,45]},{15:[1,46]},{15:[1,47]},{27:[1,48]},{15:[1,49]},{15:[1,50]},{15:[1,51]},o($Vb,$V0,{6:3,4:52}),o($Va,[2,50]),{49:[1,53]},o($Vc,[2,45],{42:54}),o($V1,[2,9]),{29:57,40:$V4,51:55,52:[1,56]},o([9,11,12,13,19,21,22,23,26,28,30,31,32,33],$V0,{6:3,4:58}),o([9,11,12,13,19,21,23,25,26,28,30,31,32,33,39],$V0,{6:3,4:59}),o($V1,[2,12]),o($V1,[2,13]),o($V1,[2,14]),o($V1,[2,15]),o($Vd,[2,39],{16:60}),o($Va,[2,36]),o([11,12,15,40,41,45,49,50,52],[2,21],{43:61,44:[1,62],46:[1,63],47:[1,64]}),{12:[1,65],15:[2,26]},o($Ve,[2,27]),o($Ve,[2,28]),{22:[1,66]},{24:67,25:[2,43],38:68,39:[1,69]},{17:70,18:[2,41],34:72,35:[1,74],36:71,37:[1,73]},o($Vc,[2,46]),{11:$V2,12:$V3,14:75,29:28,40:$V4,41:$V5,47:$V6,50:$V7,52:$V8,53:22,54:23},{41:[1,76]},{11:$V2,12:$V3,14:78,29:28,40:$V4,41:$V5,47:$V6,48:77,49:[2,47],50:$V7,52:$V8,53:22,54:23},{20:79,41:$V9},o($V1,[2,10]),{25:[1,80]},{25:[2,44]},o([9,11,12,13,19,21,23,25,26,28,30,31,32,33],$V0,{6:3,4:81}),{18:[1,82]},o($Vd,[2,40]),{18:[2,42]},{11:$V2,12:$V3,14:83,29:28,40:$V4,41:$V5,47:$V6,50:$V7,52:$V8,53:22,54:23},o([9,11,12,13,18,19,21,23,26,28,30,31,32,33],$V0,{6:3,4:84}),{45:[1,85]},o($Vc,[2,23]),{49:[1,86]},{49:[2,48]},{15:[2,25]},o($V1,[2,11]),{25:[2,20]},o($V1,[2,8]),{15:[1,87]},{18:[2,18]},o($Vc,[2,22]),o($Vc,[2,24]),o($Vb,$V0,{6:3,4:88}),o($Vd,[2,19])],
defaultActions: {4:[2,1],68:[2,44],72:[2,42],78:[2,48],79:[2,25],81:[2,20],84:[2,18]},
parseError: function parseError(str, hash) {
    if (hash.recoverable) {
        this.trace(str);
    } else {
        function _parseError (msg, hash) {
            this.message = msg;
            this.hash = hash;
        }
        _parseError.prototype = Error;

        throw new _parseError(str, hash);
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
        var lex = function () {
            var token;
            token = lexer.lex() || EOF;
            if (typeof token !== 'number') {
                token = self.symbols_[token] || token;
            }
            return token;
        };
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
case 2:return 52;
break;
case 3:return 52;
break;
case 4:return 40;
break;
case 5: return 41; 
break;
case 6:return 46;
break;
case 7:return 44;
break;
case 8:return 45;
break;
case 9:return 47;
break;
case 10:return 49;
break;
case 11:return 50;
break;
case 12:return 32;
break;
case 13:return 33;
break;
case 14: this.begin('command'); return 30; 
break;
case 15: this.begin('command'); return 31; 
break;
case 16: this.begin('command'); return 13; 
break;
case 17: this.begin('command'); return 37; 
break;
case 18: this.begin('command'); return 37; 
break;
case 19:return 35;
break;
case 20:return 18;
break;
case 21:return 26;
break;
case 22:return 27;
break;
case 23: this.begin('command'); return 19; 
break;
case 24: this.begin('command'); return 21; 
break;
case 25:return 22;
break;
case 26: this.begin('command'); return 23; 
break;
case 27:return 39;
break;
case 28:return 25;
break;
case 29: this.begin('command'); return 28; 
break;
case 30: this.popState(); return 15;
break;
case 31:return 12;
break;
case 32:return 5;
break;
case 33:return 11;
break;
}
},
rules: [/^(?:\{\*[\s\S]*?\*\})/,/^(?:\{literal\}[\s\S]*?\{\/literal\})/,/^(?:"([^"]|\\\.)*")/,/^(?:'([^']|\\\.)*')/,/^(?:\$)/,/^(?:[_a-zA-Z][_a-zA-Z0-9]*)/,/^(?:\.)/,/^(?:\[)/,/^(?:\])/,/^(?:\()/,/^(?:\))/,/^(?:=)/,/^(?:\{ldelim\})/,/^(?:\{rdelim\})/,/^(?:\{#)/,/^(?:\{@)/,/^(?:\{if )/,/^(?:\{else if )/,/^(?:\{elseif )/,/^(?:\{else\})/,/^(?:\{\/if\})/,/^(?:\{lang\})/,/^(?:\{\/lang\})/,/^(?:\{include )/,/^(?:\{implode )/,/^(?:\{\/implode\})/,/^(?:\{foreach )/,/^(?:\{foreachelse\})/,/^(?:\{\/foreach\})/,/^(?:\{(?!\s))/,/^(?:\})/,/^(?:\s+)/,/^(?:$)/,/^(?:[^{])/],
conditions: {"command":{"rules":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33],"inclusive":true},"INITIAL":{"rules":[0,1,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,31,32,33],"inclusive":true}}
});
return lexer;
})();
parser.lexer = lexer;
return parser;
});