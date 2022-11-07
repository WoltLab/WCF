"use strict";
(() => {
  var __create = Object.create;
  var __defProp = Object.defineProperty;
  var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
  var __getOwnPropNames = Object.getOwnPropertyNames;
  var __getProtoOf = Object.getPrototypeOf;
  var __hasOwnProp = Object.prototype.hasOwnProperty;
  var __require = /* @__PURE__ */ ((x) => typeof require !== "undefined" ? require : typeof Proxy !== "undefined" ? new Proxy(x, {
    get: (a, b) => (typeof require !== "undefined" ? require : a)[b]
  }) : x)(function(x) {
    if (typeof require !== "undefined")
      return require.apply(this, arguments);
    throw new Error('Dynamic require of "' + x + '" is not supported');
  });
  var __commonJS = (cb, mod) => function __require2() {
    return mod || (0, cb[__getOwnPropNames(cb)[0]])((mod = { exports: {} }).exports, mod), mod.exports;
  };
  var __export = (target, all) => {
    for (var name in all)
      __defProp(target, name, { get: all[name], enumerable: true });
  };
  var __copyProps = (to, from, except, desc) => {
    if (from && typeof from === "object" || typeof from === "function") {
      for (let key of __getOwnPropNames(from))
        if (!__hasOwnProp.call(to, key) && key !== except)
          __defProp(to, key, { get: () => from[key], enumerable: !(desc = __getOwnPropDesc(from, key)) || desc.enumerable });
    }
    return to;
  };
  var __toESM = (mod, isNodeMode, target) => (target = mod != null ? __create(__getProtoOf(mod)) : {}, __copyProps(
    isNodeMode || !mod || !mod.__esModule ? __defProp(target, "default", { value: mod, enumerable: true }) : target,
    mod
  ));

  // ts/WoltLabSuite/WebComponent/Template.grammar.js
  var require_Template_grammar = __commonJS({
    "ts/WoltLabSuite/WebComponent/Template.grammar.js"(exports, module) {
      "use strict";
      var Template2 = function() {
        var o = function(k, v, o2, l) {
          for (o2 = o2 || {}, l = k.length; l--; o2[k[l]] = v)
            ;
          return o2;
        }, $V0 = [2, 44], $V1 = [5, 9, 11, 12, 13, 18, 19, 21, 22, 23, 25, 26, 28, 29, 30, 32, 33, 34, 35, 37, 39, 41], $V2 = [1, 25], $V3 = [1, 27], $V4 = [1, 33], $V5 = [1, 31], $V6 = [1, 32], $V7 = [1, 28], $V8 = [1, 29], $V9 = [1, 26], $Va = [1, 35], $Vb = [1, 41], $Vc = [1, 40], $Vd = [11, 12, 15, 42, 43, 47, 49, 51, 52, 54, 55], $Ve = [9, 11, 12, 13, 18, 19, 21, 23, 26, 28, 30, 32, 33, 34, 35, 37, 39], $Vf = [11, 12, 15, 42, 43, 46, 47, 48, 49, 51, 52, 54, 55], $Vg = [1, 64], $Vh = [1, 65], $Vi = [18, 37, 39], $Vj = [12, 15];
        var parser2 = {
          trace: function trace() {
          },
          yy: {},
          symbols_: { "error": 2, "TEMPLATE": 3, "CHUNK_STAR": 4, "EOF": 5, "CHUNK_STAR_repetition0": 6, "CHUNK": 7, "PLAIN_ANY": 8, "T_LITERAL": 9, "COMMAND": 10, "T_ANY": 11, "T_WS": 12, "{if": 13, "COMMAND_PARAMETERS": 14, "}": 15, "COMMAND_repetition0": 16, "COMMAND_option0": 17, "{/if}": 18, "{include": 19, "COMMAND_PARAMETER_LIST": 20, "{implode": 21, "{/implode}": 22, "{foreach": 23, "COMMAND_option1": 24, "{/foreach}": 25, "{plural": 26, "PLURAL_PARAMETER_LIST": 27, "{lang}": 28, "{/lang}": 29, "{": 30, "VARIABLE": 31, "{#": 32, "{@": 33, "{ldelim}": 34, "{rdelim}": 35, "ELSE": 36, "{else}": 37, "ELSE_IF": 38, "{elseif": 39, "FOREACH_ELSE": 40, "{foreachelse}": 41, "T_VARIABLE": 42, "T_VARIABLE_NAME": 43, "VARIABLE_repetition0": 44, "VARIABLE_SUFFIX": 45, "[": 46, "]": 47, ".": 48, "(": 49, "VARIABLE_SUFFIX_option0": 50, ")": 51, "=": 52, "COMMAND_PARAMETER_VALUE": 53, "T_QUOTED_STRING": 54, "T_DIGITS": 55, "COMMAND_PARAMETERS_repetition_plus0": 56, "COMMAND_PARAMETER": 57, "T_PLURAL_PARAMETER_NAME": 58, "$accept": 0, "$end": 1 },
          terminals_: { 2: "error", 5: "EOF", 9: "T_LITERAL", 11: "T_ANY", 12: "T_WS", 13: "{if", 15: "}", 18: "{/if}", 19: "{include", 21: "{implode", 22: "{/implode}", 23: "{foreach", 25: "{/foreach}", 26: "{plural", 28: "{lang}", 29: "{/lang}", 30: "{", 32: "{#", 33: "{@", 34: "{ldelim}", 35: "{rdelim}", 37: "{else}", 39: "{elseif", 41: "{foreachelse}", 42: "T_VARIABLE", 43: "T_VARIABLE_NAME", 46: "[", 47: "]", 48: ".", 49: "(", 51: ")", 52: "=", 54: "T_QUOTED_STRING", 55: "T_DIGITS" },
          productions_: [0, [3, 2], [4, 1], [7, 1], [7, 1], [7, 1], [8, 1], [8, 1], [10, 7], [10, 3], [10, 5], [10, 6], [10, 3], [10, 3], [10, 3], [10, 3], [10, 3], [10, 1], [10, 1], [36, 2], [38, 4], [40, 2], [31, 3], [45, 3], [45, 2], [45, 3], [20, 5], [20, 3], [53, 1], [53, 1], [53, 1], [14, 1], [57, 1], [57, 1], [57, 1], [57, 1], [57, 1], [57, 1], [57, 1], [57, 3], [27, 5], [27, 3], [58, 1], [58, 1], [6, 0], [6, 2], [16, 0], [16, 2], [17, 0], [17, 1], [24, 0], [24, 1], [44, 0], [44, 2], [50, 0], [50, 1], [56, 1], [56, 2]],
          performAction: function anonymous(yytext, yyleng, yylineno, yy, yystate, $$, _$) {
            var $0 = $$.length - 1;
            switch (yystate) {
              case 1:
                return $$[$0 - 1] + ";";
                break;
              case 2:
                var result = $$[$0].reduce(function(carry, item) {
                  if (item.encode && !carry[1])
                    carry[0] += " + '" + item.value;
                  else if (item.encode && carry[1])
                    carry[0] += item.value;
                  else if (!item.encode && carry[1])
                    carry[0] += "' + " + item.value;
                  else if (!item.encode && !carry[1])
                    carry[0] += " + " + item.value;
                  carry[1] = item.encode;
                  return carry;
                }, ["''", false]);
                if (result[1])
                  result[0] += "'";
                this.$ = result[0];
                break;
              case 3:
              case 4:
                this.$ = { encode: true, value: $$[$0].replace(/\\/g, "\\\\").replace(/'/g, "\\'").replace(/(\r\n|\n|\r)/g, "\\n") };
                break;
              case 5:
                this.$ = { encode: false, value: $$[$0] };
                break;
              case 8:
                this.$ = "(function() { if (" + $$[$0 - 5] + ") { return " + $$[$0 - 3] + "; } " + $$[$0 - 2].join(" ") + " " + ($$[$0 - 1] || "") + " return ''; })()";
                break;
              case 9:
                if (!$$[$0 - 1]["file"])
                  throw new Error("Missing parameter file");
                this.$ = $$[$0 - 1]["file"] + ".fetch(v)";
                break;
              case 10:
                if (!$$[$0 - 3]["from"])
                  throw new Error("Missing parameter from");
                if (!$$[$0 - 3]["item"])
                  throw new Error("Missing parameter item");
                if (!$$[$0 - 3]["glue"])
                  $$[$0 - 3]["glue"] = "', '";
                this.$ = "(function() { return " + $$[$0 - 3]["from"] + ".map(function(item) { v[" + $$[$0 - 3]["item"] + "] = item; return " + $$[$0 - 1] + "; }).join(" + $$[$0 - 3]["glue"] + "); })()";
                break;
              case 11:
                if (!$$[$0 - 4]["from"])
                  throw new Error("Missing parameter from");
                if (!$$[$0 - 4]["item"])
                  throw new Error("Missing parameter item");
                this.$ = "(function() {var looped = false, result = '';if (" + $$[$0 - 4]["from"] + " instanceof Array) {for (var i = 0; i < " + $$[$0 - 4]["from"] + ".length; i++) { looped = true;v[" + $$[$0 - 4]["key"] + "] = i;v[" + $$[$0 - 4]["item"] + "] = " + $$[$0 - 4]["from"] + "[i];result += " + $$[$0 - 2] + ";}} else {for (var key in " + $$[$0 - 4]["from"] + ") {if (!" + $$[$0 - 4]["from"] + ".hasOwnProperty(key)) continue;looped = true;v[" + $$[$0 - 4]["key"] + "] = key;v[" + $$[$0 - 4]["item"] + "] = " + $$[$0 - 4]["from"] + "[key];result += " + $$[$0 - 2] + ";}}return (looped ? result : " + ($$[$0 - 1] || "''") + "); })()";
                break;
              case 12:
                this.$ = "h.selectPlural({";
                var needsComma = false;
                for (var key in $$[$0 - 1]) {
                  if (objOwns($$[$0 - 1], key)) {
                    this.$ += (needsComma ? "," : "") + key + ": " + $$[$0 - 1][key];
                    needsComma = true;
                  }
                }
                this.$ += "})";
                break;
              case 13:
                this.$ = "Language.get(" + $$[$0 - 1] + ", v)";
                break;
              case 14:
                this.$ = "h.escapeHTML(" + $$[$0 - 1] + ")";
                break;
              case 15:
                this.$ = "h.formatNumeric(" + $$[$0 - 1] + ")";
                break;
              case 16:
                this.$ = $$[$0 - 1];
                break;
              case 17:
                this.$ = "'{'";
                break;
              case 18:
                this.$ = "'}'";
                break;
              case 19:
                this.$ = "else { return " + $$[$0] + "; }";
                break;
              case 20:
                this.$ = "else if (" + $$[$0 - 2] + ") { return " + $$[$0] + "; }";
                break;
              case 21:
                this.$ = $$[$0];
                break;
              case 22:
                this.$ = "v['" + $$[$0 - 1] + "']" + $$[$0].join("");
                ;
                break;
              case 23:
                this.$ = $$[$0 - 2] + $$[$0 - 1] + $$[$0];
                break;
              case 24:
                this.$ = "['" + $$[$0] + "']";
                break;
              case 25:
              case 39:
                this.$ = $$[$0 - 2] + ($$[$0 - 1] || "") + $$[$0];
                break;
              case 26:
              case 40:
                this.$ = $$[$0];
                this.$[$$[$0 - 4]] = $$[$0 - 2];
                break;
              case 27:
              case 41:
                this.$ = {};
                this.$[$$[$0 - 2]] = $$[$0];
                break;
              case 31:
                this.$ = $$[$0].join("");
                break;
              case 44:
              case 46:
              case 52:
                this.$ = [];
                break;
              case 45:
              case 47:
              case 53:
              case 57:
                $$[$0 - 1].push($$[$0]);
                break;
              case 56:
                this.$ = [$$[$0]];
                break;
            }
          },
          table: [o([5, 9, 11, 12, 13, 19, 21, 23, 26, 28, 30, 32, 33, 34, 35], $V0, { 3: 1, 4: 2, 6: 3 }), { 1: [3] }, { 5: [1, 4] }, o([5, 18, 22, 25, 29, 37, 39, 41], [2, 2], { 7: 5, 8: 6, 10: 8, 9: [1, 7], 11: [1, 9], 12: [1, 10], 13: [1, 11], 19: [1, 12], 21: [1, 13], 23: [1, 14], 26: [1, 15], 28: [1, 16], 30: [1, 17], 32: [1, 18], 33: [1, 19], 34: [1, 20], 35: [1, 21] }), { 1: [2, 1] }, o($V1, [2, 45]), o($V1, [2, 3]), o($V1, [2, 4]), o($V1, [2, 5]), o($V1, [2, 6]), o($V1, [2, 7]), { 11: $V2, 12: $V3, 14: 22, 31: 30, 42: $V4, 43: $V5, 49: $V6, 52: $V7, 54: $V8, 55: $V9, 56: 23, 57: 24 }, { 20: 34, 43: $Va }, { 20: 36, 43: $Va }, { 20: 37, 43: $Va }, { 27: 38, 43: $Vb, 55: $Vc, 58: 39 }, o([9, 11, 12, 13, 19, 21, 23, 26, 28, 29, 30, 32, 33, 34, 35], $V0, { 6: 3, 4: 42 }), { 31: 43, 42: $V4 }, { 31: 44, 42: $V4 }, { 31: 45, 42: $V4 }, o($V1, [2, 17]), o($V1, [2, 18]), { 15: [1, 46] }, o([15, 47, 51], [2, 31], { 31: 30, 57: 47, 11: $V2, 12: $V3, 42: $V4, 43: $V5, 49: $V6, 52: $V7, 54: $V8, 55: $V9 }), o($Vd, [2, 56]), o($Vd, [2, 32]), o($Vd, [2, 33]), o($Vd, [2, 34]), o($Vd, [2, 35]), o($Vd, [2, 36]), o($Vd, [2, 37]), o($Vd, [2, 38]), { 11: $V2, 12: $V3, 14: 48, 31: 30, 42: $V4, 43: $V5, 49: $V6, 52: $V7, 54: $V8, 55: $V9, 56: 23, 57: 24 }, { 43: [1, 49] }, { 15: [1, 50] }, { 52: [1, 51] }, { 15: [1, 52] }, { 15: [1, 53] }, { 15: [1, 54] }, { 52: [1, 55] }, { 52: [2, 42] }, { 52: [2, 43] }, { 29: [1, 56] }, { 15: [1, 57] }, { 15: [1, 58] }, { 15: [1, 59] }, o($Ve, $V0, { 6: 3, 4: 60 }), o($Vd, [2, 57]), { 51: [1, 61] }, o($Vf, [2, 52], { 44: 62 }), o($V1, [2, 9]), { 31: 66, 42: $V4, 53: 63, 54: $Vg, 55: $Vh }, o([9, 11, 12, 13, 19, 21, 22, 23, 26, 28, 30, 32, 33, 34, 35], $V0, { 6: 3, 4: 67 }), o([9, 11, 12, 13, 19, 21, 23, 25, 26, 28, 30, 32, 33, 34, 35, 41], $V0, { 6: 3, 4: 68 }), o($V1, [2, 12]), { 31: 66, 42: $V4, 53: 69, 54: $Vg, 55: $Vh }, o($V1, [2, 13]), o($V1, [2, 14]), o($V1, [2, 15]), o($V1, [2, 16]), o($Vi, [2, 46], { 16: 70 }), o($Vd, [2, 39]), o([11, 12, 15, 42, 43, 47, 51, 52, 54, 55], [2, 22], { 45: 71, 46: [1, 72], 48: [1, 73], 49: [1, 74] }), { 12: [1, 75], 15: [2, 27] }, o($Vj, [2, 28]), o($Vj, [2, 29]), o($Vj, [2, 30]), { 22: [1, 76] }, { 24: 77, 25: [2, 50], 40: 78, 41: [1, 79] }, { 12: [1, 80], 15: [2, 41] }, { 17: 81, 18: [2, 48], 36: 83, 37: [1, 85], 38: 82, 39: [1, 84] }, o($Vf, [2, 53]), { 11: $V2, 12: $V3, 14: 86, 31: 30, 42: $V4, 43: $V5, 49: $V6, 52: $V7, 54: $V8, 55: $V9, 56: 23, 57: 24 }, { 43: [1, 87] }, { 11: $V2, 12: $V3, 14: 89, 31: 30, 42: $V4, 43: $V5, 49: $V6, 50: 88, 51: [2, 54], 52: $V7, 54: $V8, 55: $V9, 56: 23, 57: 24 }, { 20: 90, 43: $Va }, o($V1, [2, 10]), { 25: [1, 91] }, { 25: [2, 51] }, o([9, 11, 12, 13, 19, 21, 23, 25, 26, 28, 30, 32, 33, 34, 35], $V0, { 6: 3, 4: 92 }), { 27: 93, 43: $Vb, 55: $Vc, 58: 39 }, { 18: [1, 94] }, o($Vi, [2, 47]), { 18: [2, 49] }, { 11: $V2, 12: $V3, 14: 95, 31: 30, 42: $V4, 43: $V5, 49: $V6, 52: $V7, 54: $V8, 55: $V9, 56: 23, 57: 24 }, o([9, 11, 12, 13, 18, 19, 21, 23, 26, 28, 30, 32, 33, 34, 35], $V0, { 6: 3, 4: 96 }), { 47: [1, 97] }, o($Vf, [2, 24]), { 51: [1, 98] }, { 51: [2, 55] }, { 15: [2, 26] }, o($V1, [2, 11]), { 25: [2, 21] }, { 15: [2, 40] }, o($V1, [2, 8]), { 15: [1, 99] }, { 18: [2, 19] }, o($Vf, [2, 23]), o($Vf, [2, 25]), o($Ve, $V0, { 6: 3, 4: 100 }), o($Vi, [2, 20])],
          defaultActions: { 4: [2, 1], 40: [2, 42], 41: [2, 43], 78: [2, 51], 83: [2, 49], 89: [2, 55], 90: [2, 26], 92: [2, 21], 93: [2, 40], 96: [2, 19] },
          parseError: function parseError(str, hash) {
            if (hash.recoverable) {
              this.trace(str);
            } else {
              var error = new Error(str);
              error.hash = hash;
              throw error;
            }
          },
          parse: function parse2(input) {
            var self = this, stack = [0], tstack = [], vstack = [null], lstack = [], table = this.table, yytext = "", yylineno = 0, yyleng = 0, recovering = 0, TERROR = 2, EOF = 1;
            var args = lstack.slice.call(arguments, 1);
            var lexer2 = Object.create(this.lexer);
            var sharedState = { yy: {} };
            for (var k in this.yy) {
              if (Object.prototype.hasOwnProperty.call(this.yy, k)) {
                sharedState.yy[k] = this.yy[k];
              }
            }
            lexer2.setInput(input, sharedState.yy);
            sharedState.yy.lexer = lexer2;
            sharedState.yy.parser = this;
            if (typeof lexer2.yylloc == "undefined") {
              lexer2.yylloc = {};
            }
            var yyloc = lexer2.yylloc;
            lstack.push(yyloc);
            var ranges = lexer2.options && lexer2.options.ranges;
            if (typeof sharedState.yy.parseError === "function") {
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
              var lex = function() {
                var token;
                token = lexer2.lex() || EOF;
                if (typeof token !== "number") {
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
                if (symbol === null || typeof symbol == "undefined") {
                  symbol = lex();
                }
                action = table[state] && table[state][symbol];
              }
              if (typeof action === "undefined" || !action.length || !action[0]) {
                var errStr = "";
                expected = [];
                for (p in table[state]) {
                  if (this.terminals_[p] && p > TERROR) {
                    expected.push("'" + this.terminals_[p] + "'");
                  }
                }
                if (lexer2.showPosition) {
                  errStr = "Parse error on line " + (yylineno + 1) + ":\n" + lexer2.showPosition() + "\nExpecting " + expected.join(", ") + ", got '" + (this.terminals_[symbol] || symbol) + "'";
                } else {
                  errStr = "Parse error on line " + (yylineno + 1) + ": Unexpected " + (symbol == EOF ? "end of input" : "'" + (this.terminals_[symbol] || symbol) + "'");
                }
                this.parseError(errStr, {
                  text: lexer2.match,
                  token: this.terminals_[symbol] || symbol,
                  line: lexer2.yylineno,
                  loc: yyloc,
                  expected
                });
              }
              if (action[0] instanceof Array && action.length > 1) {
                throw new Error("Parse Error: multiple actions possible at state: " + state + ", token: " + symbol);
              }
              switch (action[0]) {
                case 1:
                  stack.push(symbol);
                  vstack.push(lexer2.yytext);
                  lstack.push(lexer2.yylloc);
                  stack.push(action[1]);
                  symbol = null;
                  if (!preErrorSymbol) {
                    yyleng = lexer2.yyleng;
                    yytext = lexer2.yytext;
                    yylineno = lexer2.yylineno;
                    yyloc = lexer2.yylloc;
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
                  if (typeof r !== "undefined") {
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
          }
        };
        var lexer = function() {
          var lexer2 = {
            EOF: 1,
            parseError: function parseError(str, hash) {
              if (this.yy.parser) {
                this.yy.parser.parseError(str, hash);
              } else {
                throw new Error(str);
              }
            },
            setInput: function(input, yy) {
              this.yy = yy || this.yy || {};
              this._input = input;
              this._more = this._backtrack = this.done = false;
              this.yylineno = this.yyleng = 0;
              this.yytext = this.matched = this.match = "";
              this.conditionStack = ["INITIAL"];
              this.yylloc = {
                first_line: 1,
                first_column: 0,
                last_line: 1,
                last_column: 0
              };
              if (this.options.ranges) {
                this.yylloc.range = [0, 0];
              }
              this.offset = 0;
              return this;
            },
            input: function() {
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
            unput: function(ch) {
              var len = ch.length;
              var lines = ch.split(/(?:\r\n?|\n)/g);
              this._input = ch + this._input;
              this.yytext = this.yytext.substr(0, this.yytext.length - len);
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
                last_column: lines ? (lines.length === oldLines.length ? this.yylloc.first_column : 0) + oldLines[oldLines.length - lines.length].length - lines[0].length : this.yylloc.first_column - len
              };
              if (this.options.ranges) {
                this.yylloc.range = [r[0], r[0] + this.yyleng - len];
              }
              this.yyleng = this.yytext.length;
              return this;
            },
            more: function() {
              this._more = true;
              return this;
            },
            reject: function() {
              if (this.options.backtrack_lexer) {
                this._backtrack = true;
              } else {
                return this.parseError("Lexical error on line " + (this.yylineno + 1) + ". You can only invoke reject() in the lexer when the lexer is of the backtracking persuasion (options.backtrack_lexer = true).\n" + this.showPosition(), {
                  text: "",
                  token: null,
                  line: this.yylineno
                });
              }
              return this;
            },
            less: function(n) {
              this.unput(this.match.slice(n));
            },
            pastInput: function() {
              var past = this.matched.substr(0, this.matched.length - this.match.length);
              return (past.length > 20 ? "..." : "") + past.substr(-20).replace(/\n/g, "");
            },
            upcomingInput: function() {
              var next = this.match;
              if (next.length < 20) {
                next += this._input.substr(0, 20 - next.length);
              }
              return (next.substr(0, 20) + (next.length > 20 ? "..." : "")).replace(/\n/g, "");
            },
            showPosition: function() {
              var pre = this.pastInput();
              var c = new Array(pre.length + 1).join("-");
              return pre + this.upcomingInput() + "\n" + c + "^";
            },
            test_match: function(match, indexed_rule) {
              var token, lines, backup;
              if (this.options.backtrack_lexer) {
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
                last_column: lines ? lines[lines.length - 1].length - lines[lines.length - 1].match(/\r?\n?/)[0].length : this.yylloc.last_column + match[0].length
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
                for (var k in backup) {
                  this[k] = backup[k];
                }
                return false;
              }
              return false;
            },
            next: function() {
              if (this.done) {
                return this.EOF;
              }
              if (!this._input) {
                this.done = true;
              }
              var token, match, tempMatch, index;
              if (!this._more) {
                this.yytext = "";
                this.match = "";
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
                      continue;
                    } else {
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
                return false;
              }
              if (this._input === "") {
                return this.EOF;
              } else {
                return this.parseError("Lexical error on line " + (this.yylineno + 1) + ". Unrecognized text.\n" + this.showPosition(), {
                  text: "",
                  token: null,
                  line: this.yylineno
                });
              }
            },
            lex: function lex() {
              var r = this.next();
              if (r) {
                return r;
              } else {
                return this.lex();
              }
            },
            begin: function begin(condition) {
              this.conditionStack.push(condition);
            },
            popState: function popState() {
              var n = this.conditionStack.length - 1;
              if (n > 0) {
                return this.conditionStack.pop();
              } else {
                return this.conditionStack[0];
              }
            },
            _currentRules: function _currentRules() {
              if (this.conditionStack.length && this.conditionStack[this.conditionStack.length - 1]) {
                return this.conditions[this.conditionStack[this.conditionStack.length - 1]].rules;
              } else {
                return this.conditions["INITIAL"].rules;
              }
            },
            topState: function topState(n) {
              n = this.conditionStack.length - 1 - Math.abs(n || 0);
              if (n >= 0) {
                return this.conditionStack[n];
              } else {
                return "INITIAL";
              }
            },
            pushState: function pushState(condition) {
              this.begin(condition);
            },
            stateStackSize: function stateStackSize() {
              return this.conditionStack.length;
            },
            options: {},
            performAction: function anonymous(yy, yy_, $avoiding_name_collisions, YY_START) {
              var YYSTATE = YY_START;
              switch ($avoiding_name_collisions) {
                case 0:
                  break;
                case 1:
                  yy_.yytext = yy_.yytext.substring(9, yy_.yytext.length - 10);
                  return 9;
                  break;
                case 2:
                  return 54;
                  break;
                case 3:
                  return 54;
                  break;
                case 4:
                  return 42;
                  break;
                case 5:
                  return 55;
                  break;
                case 6:
                  return 43;
                  break;
                case 7:
                  return 48;
                  break;
                case 8:
                  return 46;
                  break;
                case 9:
                  return 47;
                  break;
                case 10:
                  return 49;
                  break;
                case 11:
                  return 51;
                  break;
                case 12:
                  return 52;
                  break;
                case 13:
                  return 34;
                  break;
                case 14:
                  return 35;
                  break;
                case 15:
                  this.begin("command");
                  return 32;
                  break;
                case 16:
                  this.begin("command");
                  return 33;
                  break;
                case 17:
                  this.begin("command");
                  return 13;
                  break;
                case 18:
                  this.begin("command");
                  return 39;
                  break;
                case 19:
                  this.begin("command");
                  return 39;
                  break;
                case 20:
                  return 37;
                  break;
                case 21:
                  return 18;
                  break;
                case 22:
                  return 28;
                  break;
                case 23:
                  return 29;
                  break;
                case 24:
                  this.begin("command");
                  return 19;
                  break;
                case 25:
                  this.begin("command");
                  return 21;
                  break;
                case 26:
                  this.begin("command");
                  return 26;
                  break;
                case 27:
                  return 22;
                  break;
                case 28:
                  this.begin("command");
                  return 23;
                  break;
                case 29:
                  return 41;
                  break;
                case 30:
                  return 25;
                  break;
                case 31:
                  this.begin("command");
                  return 30;
                  break;
                case 32:
                  this.popState();
                  return 15;
                  break;
                case 33:
                  return 12;
                  break;
                case 34:
                  return 5;
                  break;
                case 35:
                  return 11;
                  break;
              }
            },
            rules: [/^(?:\{\*[\s\S]*?\*\})/, /^(?:\{literal\}[\s\S]*?\{\/literal\})/, /^(?:"([^"]|\\\.)*")/, /^(?:'([^']|\\\.)*')/, /^(?:\$)/, /^(?:[0-9]+)/, /^(?:[_a-zA-Z][_a-zA-Z0-9]*)/, /^(?:\.)/, /^(?:\[)/, /^(?:\])/, /^(?:\()/, /^(?:\))/, /^(?:=)/, /^(?:\{ldelim\})/, /^(?:\{rdelim\})/, /^(?:\{#)/, /^(?:\{@)/, /^(?:\{if )/, /^(?:\{else if )/, /^(?:\{elseif )/, /^(?:\{else\})/, /^(?:\{\/if\})/, /^(?:\{lang\})/, /^(?:\{\/lang\})/, /^(?:\{include )/, /^(?:\{implode )/, /^(?:\{plural )/, /^(?:\{\/implode\})/, /^(?:\{foreach )/, /^(?:\{foreachelse\})/, /^(?:\{\/foreach\})/, /^(?:\{(?!\s))/, /^(?:\})/, /^(?:\s+)/, /^(?:$)/, /^(?:[^{])/],
            conditions: { "command": { "rules": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35], "inclusive": true }, "INITIAL": { "rules": [0, 1, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 33, 34, 35], "inclusive": true } }
          };
          return lexer2;
        }();
        parser2.lexer = lexer;
        function Parser() {
          this.yy = {};
        }
        Parser.prototype = parser2;
        parser2.Parser = Parser;
        return new Parser();
      }();
      if (typeof __require !== "undefined" && typeof exports !== "undefined") {
        exports.parser = Template2;
        exports.Parser = Template2.Parser;
        exports.parse = function() {
          return Template2.parse.apply(Template2, arguments);
        };
        exports.main = true;
        if (typeof module !== "undefined" && __require.main === module) {
          exports.main(process.argv.slice(1));
        }
      }
    }
  });

  // ts/WoltLabSuite/WebComponent/LanguageStore.ts
  var LanguageStore_exports = {};
  __export(LanguageStore_exports, {
    add: () => add,
    get: () => get
  });
  var languageItems = /* @__PURE__ */ new Map();
  function get(key, parameters = {}) {
    const value = languageItems.get(key);
    if (value === void 0) {
      return key;
    }
    return value(parameters);
  }
  function add(key, value) {
    languageItems.set(key, value);
  }

  // ts/WoltLabSuite/WebComponent/Template.ts
  var parser = __toESM(require_Template_grammar());
  function escapeHTML(string) {
    return String(string).replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
  }
  function formatNumeric(string) {
    return Number(string).toLocaleString(document.documentElement.lang, {
      maximumFractionDigits: 2
    }).replace("-", "\u2212");
  }
  var pluralRules = new Intl.PluralRules(document.documentElement.lang);
  function selectPlural(parameters) {
    if (!Object.hasOwn(parameters, "value")) {
      throw new Error("Missing parameter value");
    }
    if (!parameters.other) {
      throw new Error("Missing parameter other");
    }
    let value = parameters.value;
    if (Array.isArray(value)) {
      value = value.length;
    }
    const numericAttribute = Object.keys(parameters).find((key) => {
      return key.toString() === parseInt(key).toString() && key.toString() === value.toString();
    });
    if (numericAttribute) {
      return numericAttribute;
    }
    let category = pluralRules.select(value);
    if (parameters[category] === void 0) {
      category = "other";
    }
    const string = parameters[category];
    if (string.includes("#")) {
      return string.replace("#", formatNumeric(value));
    }
    return string;
  }
  function compile(template) {
    const compiled = `var tmp = {};
    for (var key in v) tmp[key] = v[key];
    v = tmp;
    v.__wcf = window.WCF; v.__window = window;
    return ${parser.parse(template)}
    `;
    return new Function("Language", "h", "v", compiled);
  }
  var Template = class {
    compiled;
    constructor(template) {
      try {
        this.compiled = compile(template);
      } catch (e) {
        console.debug(e.message);
        throw e;
      }
    }
    fetch(v) {
      return this.compiled(LanguageStore_exports, { selectPlural, escapeHTML, formatNumeric }, v);
    }
  };

  // ts/WoltLabSuite/WebComponent/fa-metadata.js
  (() => {
    const aliases = /* @__PURE__ */ new Map(
      [["contact-book", "address-book"], ["contact-card", "address-card"], ["vcard", "address-card"], ["angle-double-down", "angles-down"], ["angle-double-left", "angles-left"], ["angle-double-right", "angles-right"], ["angle-double-up", "angles-up"], ["apple-alt", "apple-whole"], ["sort-numeric-asc", "arrow-down-1-9"], ["sort-numeric-down", "arrow-down-1-9"], ["sort-numeric-desc", "arrow-down-9-1"], ["sort-numeric-down-alt", "arrow-down-9-1"], ["sort-alpha-asc", "arrow-down-a-z"], ["sort-alpha-down", "arrow-down-a-z"], ["long-arrow-down", "arrow-down-long"], ["sort-amount-desc", "arrow-down-short-wide"], ["sort-amount-down-alt", "arrow-down-short-wide"], ["sort-amount-asc", "arrow-down-wide-short"], ["sort-amount-down", "arrow-down-wide-short"], ["sort-alpha-desc", "arrow-down-z-a"], ["sort-alpha-down-alt", "arrow-down-z-a"], ["long-arrow-left", "arrow-left-long"], ["mouse-pointer", "arrow-pointer"], ["exchange", "arrow-right-arrow-left"], ["sign-out", "arrow-right-from-bracket"], ["long-arrow-right", "arrow-right-long"], ["sign-in", "arrow-right-to-bracket"], ["arrow-left-rotate", "arrow-rotate-left"], ["arrow-rotate-back", "arrow-rotate-left"], ["arrow-rotate-backward", "arrow-rotate-left"], ["undo", "arrow-rotate-left"], ["arrow-right-rotate", "arrow-rotate-right"], ["arrow-rotate-forward", "arrow-rotate-right"], ["redo", "arrow-rotate-right"], ["level-down", "arrow-turn-down"], ["level-up", "arrow-turn-up"], ["sort-numeric-up", "arrow-up-1-9"], ["sort-numeric-up-alt", "arrow-up-9-1"], ["sort-alpha-up", "arrow-up-a-z"], ["long-arrow-up", "arrow-up-long"], ["external-link", "arrow-up-right-from-square"], ["sort-amount-up-alt", "arrow-up-short-wide"], ["sort-amount-up", "arrow-up-wide-short"], ["sort-alpha-up-alt", "arrow-up-z-a"], ["arrows-h", "arrows-left-right"], ["refresh", "arrows-rotate"], ["sync", "arrows-rotate"], ["arrows-v", "arrows-up-down"], ["arrows", "arrows-up-down-left-right"], ["carriage-baby", "baby-carriage"], ["fast-backward", "backward-fast"], ["step-backward", "backward-step"], ["shopping-bag", "bag-shopping"], ["haykal", "bahai"], ["cancel", "ban"], ["smoking-ban", "ban-smoking"], ["band-aid", "bandage"], ["navicon", "bars"], ["tasks-alt", "bars-progress"], ["reorder", "bars-staggered"], ["stream", "bars-staggered"], ["baseball-ball", "baseball"], ["shopping-basket", "basket-shopping"], ["basketball-ball", "basketball"], ["bathtub", "bath"], ["battery-0", "battery-empty"], ["battery", "battery-full"], ["battery-5", "battery-full"], ["battery-3", "battery-half"], ["battery-2", "battery-quarter"], ["battery-4", "battery-three-quarters"], ["procedures", "bed-pulse"], ["beer", "beer-mug-empty"], ["concierge-bell", "bell-concierge"], ["zap", "bolt"], ["atlas", "book-atlas"], ["bible", "book-bible"], ["journal-whills", "book-journal-whills"], ["book-reader", "book-open-reader"], ["quran", "book-quran"], ["book-dead", "book-skull"], ["tanakh", "book-tanakh"], ["border-style", "border-top-left"], ["archive", "box-archive"], ["boxes", "boxes-stacked"], ["boxes-alt", "boxes-stacked"], ["quidditch", "broom-ball"], ["quidditch-broom-ball", "broom-ball"], ["bank", "building-columns"], ["institution", "building-columns"], ["museum", "building-columns"], ["university", "building-columns"], ["hamburger", "burger"], ["bus-alt", "bus-simple"], ["briefcase-clock", "business-time"], ["tram", "cable-car"], ["birthday-cake", "cake-candles"], ["cake", "cake-candles"], ["calendar-alt", "calendar-days"], ["calendar-times", "calendar-xmark"], ["camera-alt", "camera"], ["automobile", "car"], ["battery-car", "car-battery"], ["car-crash", "car-burst"], ["car-alt", "car-rear"], ["dolly-flatbed", "cart-flatbed"], ["luggage-cart", "cart-flatbed-suitcase"], ["shopping-cart", "cart-shopping"], ["blackboard", "chalkboard"], ["chalkboard-teacher", "chalkboard-user"], ["glass-cheers", "champagne-glasses"], ["area-chart", "chart-area"], ["bar-chart", "chart-bar"], ["line-chart", "chart-line"], ["pie-chart", "chart-pie"], ["vote-yea", "check-to-slot"], ["arrow-circle-down", "circle-arrow-down"], ["arrow-circle-left", "circle-arrow-left"], ["arrow-circle-right", "circle-arrow-right"], ["arrow-circle-up", "circle-arrow-up"], ["check-circle", "circle-check"], ["chevron-circle-down", "circle-chevron-down"], ["chevron-circle-left", "circle-chevron-left"], ["chevron-circle-right", "circle-chevron-right"], ["chevron-circle-up", "circle-chevron-up"], ["donate", "circle-dollar-to-slot"], ["dot-circle", "circle-dot"], ["arrow-alt-circle-down", "circle-down"], ["exclamation-circle", "circle-exclamation"], ["hospital-symbol", "circle-h"], ["adjust", "circle-half-stroke"], ["info-circle", "circle-info"], ["arrow-alt-circle-left", "circle-left"], ["minus-circle", "circle-minus"], ["pause-circle", "circle-pause"], ["play-circle", "circle-play"], ["plus-circle", "circle-plus"], ["question-circle", "circle-question"], ["radiation-alt", "circle-radiation"], ["arrow-alt-circle-right", "circle-right"], ["stop-circle", "circle-stop"], ["arrow-alt-circle-up", "circle-up"], ["user-circle", "circle-user"], ["times-circle", "circle-xmark"], ["xmark-circle", "circle-xmark"], ["clock-four", "clock"], ["history", "clock-rotate-left"], ["cloud-download", "cloud-arrow-down"], ["cloud-download-alt", "cloud-arrow-down"], ["cloud-upload", "cloud-arrow-up"], ["cloud-upload-alt", "cloud-arrow-up"], ["thunderstorm", "cloud-bolt"], ["commenting", "comment-dots"], ["sms", "comment-sms"], ["drafting-compass", "compass-drafting"], ["mouse", "computer-mouse"], ["credit-card-alt", "credit-card"], ["crop-alt", "crop-simple"], ["backspace", "delete-left"], ["desktop-alt", "desktop"], ["project-diagram", "diagram-project"], ["directions", "diamond-turn-right"], ["dollar", "dollar-sign"], ["usd", "dollar-sign"], ["dolly-box", "dolly"], ["compress-alt", "down-left-and-up-right-to-center"], ["long-arrow-alt-down", "down-long"], ["tint", "droplet"], ["tint-slash", "droplet-slash"], ["deaf", "ear-deaf"], ["deafness", "ear-deaf"], ["hard-of-hearing", "ear-deaf"], ["assistive-listening-systems", "ear-listen"], ["globe-africa", "earth-africa"], ["earth", "earth-americas"], ["earth-america", "earth-americas"], ["globe-americas", "earth-americas"], ["globe-asia", "earth-asia"], ["globe-europe", "earth-europe"], ["globe-oceania", "earth-oceania"], ["ellipsis-h", "ellipsis"], ["ellipsis-v", "ellipsis-vertical"], ["mail-bulk", "envelopes-bulk"], ["eur", "euro-sign"], ["euro", "euro-sign"], ["eye-dropper-empty", "eye-dropper"], ["eyedropper", "eye-dropper"], ["low-vision", "eye-low-vision"], ["angry", "face-angry"], ["dizzy", "face-dizzy"], ["flushed", "face-flushed"], ["frown", "face-frown"], ["frown-open", "face-frown-open"], ["grimace", "face-grimace"], ["grin", "face-grin"], ["grin-beam", "face-grin-beam"], ["grin-beam-sweat", "face-grin-beam-sweat"], ["grin-hearts", "face-grin-hearts"], ["grin-squint", "face-grin-squint"], ["grin-squint-tears", "face-grin-squint-tears"], ["grin-stars", "face-grin-stars"], ["grin-tears", "face-grin-tears"], ["grin-tongue", "face-grin-tongue"], ["grin-tongue-squint", "face-grin-tongue-squint"], ["grin-tongue-wink", "face-grin-tongue-wink"], ["grin-alt", "face-grin-wide"], ["grin-wink", "face-grin-wink"], ["kiss", "face-kiss"], ["kiss-beam", "face-kiss-beam"], ["kiss-wink-heart", "face-kiss-wink-heart"], ["laugh", "face-laugh"], ["laugh-beam", "face-laugh-beam"], ["laugh-squint", "face-laugh-squint"], ["laugh-wink", "face-laugh-wink"], ["meh", "face-meh"], ["meh-blank", "face-meh-blank"], ["meh-rolling-eyes", "face-rolling-eyes"], ["sad-cry", "face-sad-cry"], ["sad-tear", "face-sad-tear"], ["smile", "face-smile"], ["smile-beam", "face-smile-beam"], ["smile-wink", "face-smile-wink"], ["surprise", "face-surprise"], ["tired", "face-tired"], ["feather-alt", "feather-pointed"], ["file-download", "file-arrow-down"], ["file-upload", "file-arrow-up"], ["arrow-right-from-file", "file-export"], ["arrow-right-to-file", "file-import"], ["file-alt", "file-lines"], ["file-text", "file-lines"], ["file-edit", "file-pen"], ["file-medical-alt", "file-waveform"], ["file-archive", "file-zipper"], ["funnel-dollar", "filter-circle-dollar"], ["fire-alt", "fire-flame-curved"], ["burn", "fire-flame-simple"], ["save", "floppy-disk"], ["folder-blank", "folder"], ["football-ball", "football"], ["fast-forward", "forward-fast"], ["step-forward", "forward-step"], ["futbol-ball", "futbol"], ["soccer-ball", "futbol"], ["dashboard", "gauge"], ["gauge-med", "gauge"], ["tachometer-alt-average", "gauge"], ["tachometer-alt", "gauge-high"], ["tachometer-alt-fast", "gauge-high"], ["gauge-simple-med", "gauge-simple"], ["tachometer-average", "gauge-simple"], ["tachometer", "gauge-simple-high"], ["tachometer-fast", "gauge-simple-high"], ["legal", "gavel"], ["cog", "gear"], ["cogs", "gears"], ["golf-ball", "golf-ball-tee"], ["mortar-board", "graduation-cap"], ["grip-horizontal", "grip"], ["hand-paper", "hand"], ["hand-rock", "hand-back-fist"], ["allergies", "hand-dots"], ["fist-raised", "hand-fist"], ["hand-holding-usd", "hand-holding-dollar"], ["hand-holding-water", "hand-holding-droplet"], ["sign-language", "hands"], ["signing", "hands"], ["american-sign-language-interpreting", "hands-asl-interpreting"], ["asl-interpreting", "hands-asl-interpreting"], ["hands-american-sign-language-interpreting", "hands-asl-interpreting"], ["hands-wash", "hands-bubbles"], ["praying-hands", "hands-praying"], ["hands-helping", "handshake-angle"], ["handshake-alt", "handshake-simple"], ["handshake-alt-slash", "handshake-simple-slash"], ["hdd", "hard-drive"], ["header", "heading"], ["headphones-alt", "headphones-simple"], ["heart-broken", "heart-crack"], ["heartbeat", "heart-pulse"], ["hard-hat", "helmet-safety"], ["hat-hard", "helmet-safety"], ["hospital-alt", "hospital"], ["hospital-wide", "hospital"], ["hot-tub", "hot-tub-person"], ["hourglass-empty", "hourglass"], ["hourglass-3", "hourglass-end"], ["hourglass-2", "hourglass-half"], ["hourglass-1", "hourglass-start"], ["home", "house"], ["home-alt", "house"], ["home-lg-alt", "house"], ["home-lg", "house-chimney"], ["house-damage", "house-chimney-crack"], ["clinic-medical", "house-chimney-medical"], ["laptop-house", "house-laptop"], ["home-user", "house-user"], ["hryvnia", "hryvnia-sign"], ["heart-music-camera-bolt", "icons"], ["drivers-license", "id-card"], ["id-card-alt", "id-card-clip"], ["portrait", "image-portrait"], ["indian-rupee", "indian-rupee-sign"], ["inr", "indian-rupee-sign"], ["fighter-jet", "jet-fighter"], ["first-aid", "kit-medical"], ["landmark-alt", "landmark-dome"], ["long-arrow-alt-left", "left-long"], ["arrows-alt-h", "left-right"], ["chain", "link"], ["chain-broken", "link-slash"], ["chain-slash", "link-slash"], ["unlink", "link-slash"], ["list-squares", "list"], ["tasks", "list-check"], ["list-1-2", "list-ol"], ["list-numeric", "list-ol"], ["list-dots", "list-ul"], ["location", "location-crosshairs"], ["map-marker-alt", "location-dot"], ["map-marker", "location-pin"], ["search", "magnifying-glass"], ["search-dollar", "magnifying-glass-dollar"], ["search-location", "magnifying-glass-location"], ["search-minus", "magnifying-glass-minus"], ["search-plus", "magnifying-glass-plus"], ["map-marked", "map-location"], ["map-marked-alt", "map-location-dot"], ["mars-stroke-h", "mars-stroke-right"], ["mars-stroke-v", "mars-stroke-up"], ["glass-martini-alt", "martini-glass"], ["cocktail", "martini-glass-citrus"], ["glass-martini", "martini-glass-empty"], ["theater-masks", "masks-theater"], ["expand-arrows-alt", "maximize"], ["comment-alt", "message"], ["microphone-alt", "microphone-lines"], ["microphone-alt-slash", "microphone-lines-slash"], ["compress-arrows-alt", "minimize"], ["subtract", "minus"], ["mobile-android", "mobile"], ["mobile-phone", "mobile"], ["mobile-android-alt", "mobile-screen"], ["mobile-alt", "mobile-screen-button"], ["money-bill-alt", "money-bill-1"], ["money-bill-wave-alt", "money-bill-1-wave"], ["money-check-alt", "money-check-dollar"], ["coffee", "mug-saucer"], ["sticky-note", "note-sticky"], ["dedent", "outdent"], ["paint-brush", "paintbrush"], ["file-clipboard", "paste"], ["pen-alt", "pen-clip"], ["pencil-ruler", "pen-ruler"], ["edit", "pen-to-square"], ["pencil-alt", "pencil"], ["people-arrows-left-right", "people-arrows"], ["people-carry", "people-carry-box"], ["percentage", "percent"], ["male", "person"], ["biking", "person-biking"], ["digging", "person-digging"], ["diagnoses", "person-dots-from-line"], ["female", "person-dress"], ["hiking", "person-hiking"], ["pray", "person-praying"], ["running", "person-running"], ["skating", "person-skating"], ["skiing", "person-skiing"], ["skiing-nordic", "person-skiing-nordic"], ["snowboarding", "person-snowboarding"], ["swimmer", "person-swimming"], ["walking", "person-walking"], ["blind", "person-walking-with-cane"], ["phone-alt", "phone-flip"], ["volume-control-phone", "phone-volume"], ["photo-video", "photo-film"], ["add", "plus"], ["poo-bolt", "poo-storm"], ["prescription-bottle-alt", "prescription-bottle-medical"], ["quote-left-alt", "quote-left"], ["quote-right-alt", "quote-right"], ["ad", "rectangle-ad"], ["list-alt", "rectangle-list"], ["rectangle-times", "rectangle-xmark"], ["times-rectangle", "rectangle-xmark"], ["window-close", "rectangle-xmark"], ["mail-reply", "reply"], ["mail-reply-all", "reply-all"], ["sign-out-alt", "right-from-bracket"], ["exchange-alt", "right-left"], ["long-arrow-alt-right", "right-long"], ["sign-in-alt", "right-to-bracket"], ["sync-alt", "rotate"], ["rotate-back", "rotate-left"], ["rotate-backward", "rotate-left"], ["undo-alt", "rotate-left"], ["redo-alt", "rotate-right"], ["rotate-forward", "rotate-right"], ["feed", "rss"], ["rouble", "ruble-sign"], ["rub", "ruble-sign"], ["ruble", "ruble-sign"], ["rupee", "rupee-sign"], ["balance-scale", "scale-balanced"], ["balance-scale-left", "scale-unbalanced"], ["balance-scale-right", "scale-unbalanced-flip"], ["cut", "scissors"], ["tools", "screwdriver-wrench"], ["torah", "scroll-torah"], ["sprout", "seedling"], ["triangle-circle-square", "shapes"], ["arrow-turn-right", "share"], ["mail-forward", "share"], ["share-square", "share-from-square"], ["share-alt", "share-nodes"], ["ils", "shekel-sign"], ["shekel", "shekel-sign"], ["sheqel", "shekel-sign"], ["sheqel-sign", "shekel-sign"], ["shield-blank", "shield"], ["shield-alt", "shield-halved"], ["t-shirt", "shirt"], ["tshirt", "shirt"], ["store-alt", "shop"], ["store-alt-slash", "shop-slash"], ["random", "shuffle"], ["space-shuttle", "shuttle-space"], ["sign", "sign-hanging"], ["signal-5", "signal"], ["signal-perfect", "signal"], ["map-signs", "signs-post"], ["sliders-h", "sliders"], ["unsorted", "sort"], ["sort-desc", "sort-down"], ["sort-asc", "sort-up"], ["pastafarianism", "spaghetti-monster-flying"], ["utensil-spoon", "spoon"], ["air-freshener", "spray-can-sparkles"], ["external-link-square", "square-arrow-up-right"], ["caret-square-down", "square-caret-down"], ["caret-square-left", "square-caret-left"], ["caret-square-right", "square-caret-right"], ["caret-square-up", "square-caret-up"], ["check-square", "square-check"], ["envelope-square", "square-envelope"], ["h-square", "square-h"], ["minus-square", "square-minus"], ["parking", "square-parking"], ["pen-square", "square-pen"], ["pencil-square", "square-pen"], ["phone-square", "square-phone"], ["phone-square-alt", "square-phone-flip"], ["plus-square", "square-plus"], ["poll-h", "square-poll-horizontal"], ["poll", "square-poll-vertical"], ["square-root-alt", "square-root-variable"], ["rss-square", "square-rss"], ["share-alt-square", "square-share-nodes"], ["external-link-square-alt", "square-up-right"], ["times-square", "square-xmark"], ["xmark-square", "square-xmark"], ["rod-asclepius", "staff-snake"], ["rod-snake", "staff-snake"], ["staff-aesculapius", "staff-snake"], ["star-half-alt", "star-half-stroke"], ["gbp", "sterling-sign"], ["pound-sign", "sterling-sign"], ["medkit", "suitcase-medical"], ["th", "table-cells"], ["th-large", "table-cells-large"], ["columns", "table-columns"], ["th-list", "table-list"], ["ping-pong-paddle-ball", "table-tennis-paddle-ball"], ["table-tennis", "table-tennis-paddle-ball"], ["tablet-android", "tablet"], ["tablet-alt", "tablet-screen-button"], ["digital-tachograph", "tachograph-digital"], ["cab", "taxi"], ["temperature-down", "temperature-arrow-down"], ["temperature-up", "temperature-arrow-up"], ["temperature-0", "temperature-empty"], ["thermometer-0", "temperature-empty"], ["thermometer-empty", "temperature-empty"], ["temperature-4", "temperature-full"], ["thermometer-4", "temperature-full"], ["thermometer-full", "temperature-full"], ["temperature-2", "temperature-half"], ["thermometer-2", "temperature-half"], ["thermometer-half", "temperature-half"], ["temperature-1", "temperature-quarter"], ["thermometer-1", "temperature-quarter"], ["thermometer-quarter", "temperature-quarter"], ["temperature-3", "temperature-three-quarters"], ["thermometer-3", "temperature-three-quarters"], ["thermometer-three-quarters", "temperature-three-quarters"], ["tenge", "tenge-sign"], ["remove-format", "text-slash"], ["thumb-tack", "thumbtack"], ["ticket-alt", "ticket-simple"], ["broadcast-tower", "tower-broadcast"], ["subway", "train-subway"], ["transgender-alt", "transgender"], ["trash-restore", "trash-arrow-up"], ["trash-alt", "trash-can"], ["trash-restore-alt", "trash-can-arrow-up"], ["exclamation-triangle", "triangle-exclamation"], ["warning", "triangle-exclamation"], ["shipping-fast", "truck-fast"], ["ambulance", "truck-medical"], ["truck-loading", "truck-ramp-box"], ["teletype", "tty"], ["try", "turkish-lira-sign"], ["turkish-lira", "turkish-lira-sign"], ["level-down-alt", "turn-down"], ["level-up-alt", "turn-up"], ["television", "tv"], ["tv-alt", "tv"], ["unlock-alt", "unlock-keyhole"], ["arrows-alt-v", "up-down"], ["arrows-alt", "up-down-left-right"], ["long-arrow-alt-up", "up-long"], ["expand-alt", "up-right-and-down-left-from-center"], ["external-link-alt", "up-right-from-square"], ["user-md", "user-doctor"], ["user-cog", "user-gear"], ["user-friends", "user-group"], ["user-alt", "user-large"], ["user-alt-slash", "user-large-slash"], ["user-edit", "user-pen"], ["user-times", "user-xmark"], ["users-cog", "users-gear"], ["cutlery", "utensils"], ["shuttle-van", "van-shuttle"], ["video-camera", "video"], ["volleyball-ball", "volleyball"], ["volume-up", "volume-high"], ["volume-down", "volume-low"], ["volume-mute", "volume-xmark"], ["volume-times", "volume-xmark"], ["magic", "wand-magic"], ["magic-wand-sparkles", "wand-magic-sparkles"], ["ladder-water", "water-ladder"], ["swimming-pool", "water-ladder"], ["weight", "weight-scale"], ["wheat-alt", "wheat-awn"], ["wheelchair-alt", "wheelchair-move"], ["glass-whiskey", "whiskey-glass"], ["wifi-3", "wifi"], ["wifi-strong", "wifi"], ["wine-glass-alt", "wine-glass-empty"], ["krw", "won-sign"], ["won", "won-sign"], ["close", "xmark"], ["multiply", "xmark"], ["remove", "xmark"], ["times", "xmark"], ["cny", "yen-sign"], ["jpy", "yen-sign"], ["rmb", "yen-sign"], ["yen", "yen-sign"]]
    );
    const metadata = /* @__PURE__ */ new Map(
      [["0", ["0", false]], ["1", ["1", false]], ["2", ["2", false]], ["3", ["3", false]], ["4", ["4", false]], ["5", ["5", false]], ["6", ["6", false]], ["7", ["7", false]], ["8", ["8", false]], ["9", ["9", false]], ["a", ["A", false]], ["address-book", ["\uF2B9", true]], ["address-card", ["\uF2BB", true]], ["align-center", ["\uF037", false]], ["align-justify", ["\uF039", false]], ["align-left", ["\uF036", false]], ["align-right", ["\uF038", false]], ["anchor", ["\uF13D", false]], ["anchor-circle-check", ["\uE4AA", false]], ["anchor-circle-exclamation", ["\uE4AB", false]], ["anchor-circle-xmark", ["\uE4AC", false]], ["anchor-lock", ["\uE4AD", false]], ["angle-down", ["\uF107", false]], ["angle-left", ["\uF104", false]], ["angle-right", ["\uF105", false]], ["angle-up", ["\uF106", false]], ["angles-down", ["\uF103", false]], ["angles-left", ["\uF100", false]], ["angles-right", ["\uF101", false]], ["angles-up", ["\uF102", false]], ["ankh", ["\uF644", false]], ["apple-whole", ["\uF5D1", false]], ["archway", ["\uF557", false]], ["arrow-down", ["\uF063", false]], ["arrow-down-1-9", ["\uF162", false]], ["arrow-down-9-1", ["\uF886", false]], ["arrow-down-a-z", ["\uF15D", false]], ["arrow-down-long", ["\uF175", false]], ["arrow-down-short-wide", ["\uF884", false]], ["arrow-down-up-across-line", ["\uE4AF", false]], ["arrow-down-up-lock", ["\uE4B0", false]], ["arrow-down-wide-short", ["\uF160", false]], ["arrow-down-z-a", ["\uF881", false]], ["arrow-left", ["\uF060", false]], ["arrow-left-long", ["\uF177", false]], ["arrow-pointer", ["\uF245", false]], ["arrow-right", ["\uF061", false]], ["arrow-right-arrow-left", ["\uF0EC", false]], ["arrow-right-from-bracket", ["\uF08B", false]], ["arrow-right-long", ["\uF178", false]], ["arrow-right-to-bracket", ["\uF090", false]], ["arrow-right-to-city", ["\uE4B3", false]], ["arrow-rotate-left", ["\uF0E2", false]], ["arrow-rotate-right", ["\uF01E", false]], ["arrow-trend-down", ["\uE097", false]], ["arrow-trend-up", ["\uE098", false]], ["arrow-turn-down", ["\uF149", false]], ["arrow-turn-up", ["\uF148", false]], ["arrow-up", ["\uF062", false]], ["arrow-up-1-9", ["\uF163", false]], ["arrow-up-9-1", ["\uF887", false]], ["arrow-up-a-z", ["\uF15E", false]], ["arrow-up-from-bracket", ["\uE09A", false]], ["arrow-up-from-ground-water", ["\uE4B5", false]], ["arrow-up-from-water-pump", ["\uE4B6", false]], ["arrow-up-long", ["\uF176", false]], ["arrow-up-right-dots", ["\uE4B7", false]], ["arrow-up-right-from-square", ["\uF08E", false]], ["arrow-up-short-wide", ["\uF885", false]], ["arrow-up-wide-short", ["\uF161", false]], ["arrow-up-z-a", ["\uF882", false]], ["arrows-down-to-line", ["\uE4B8", false]], ["arrows-down-to-people", ["\uE4B9", false]], ["arrows-left-right", ["\uF07E", false]], ["arrows-left-right-to-line", ["\uE4BA", false]], ["arrows-rotate", ["\uF021", false]], ["arrows-spin", ["\uE4BB", false]], ["arrows-split-up-and-left", ["\uE4BC", false]], ["arrows-to-circle", ["\uE4BD", false]], ["arrows-to-dot", ["\uE4BE", false]], ["arrows-to-eye", ["\uE4BF", false]], ["arrows-turn-right", ["\uE4C0", false]], ["arrows-turn-to-dots", ["\uE4C1", false]], ["arrows-up-down", ["\uF07D", false]], ["arrows-up-down-left-right", ["\uF047", false]], ["arrows-up-to-line", ["\uE4C2", false]], ["asterisk", ["*", false]], ["at", ["@", false]], ["atom", ["\uF5D2", false]], ["audio-description", ["\uF29E", false]], ["austral-sign", ["\uE0A9", false]], ["award", ["\uF559", false]], ["b", ["B", false]], ["baby", ["\uF77C", false]], ["baby-carriage", ["\uF77D", false]], ["backward", ["\uF04A", false]], ["backward-fast", ["\uF049", false]], ["backward-step", ["\uF048", false]], ["bacon", ["\uF7E5", false]], ["bacteria", ["\uE059", false]], ["bacterium", ["\uE05A", false]], ["bag-shopping", ["\uF290", false]], ["bahai", ["\uF666", false]], ["baht-sign", ["\uE0AC", false]], ["ban", ["\uF05E", false]], ["ban-smoking", ["\uF54D", false]], ["bandage", ["\uF462", false]], ["barcode", ["\uF02A", false]], ["bars", ["\uF0C9", false]], ["bars-progress", ["\uF828", false]], ["bars-staggered", ["\uF550", false]], ["baseball", ["\uF433", false]], ["baseball-bat-ball", ["\uF432", false]], ["basket-shopping", ["\uF291", false]], ["basketball", ["\uF434", false]], ["bath", ["\uF2CD", false]], ["battery-empty", ["\uF244", false]], ["battery-full", ["\uF240", false]], ["battery-half", ["\uF242", false]], ["battery-quarter", ["\uF243", false]], ["battery-three-quarters", ["\uF241", false]], ["bed", ["\uF236", false]], ["bed-pulse", ["\uF487", false]], ["beer-mug-empty", ["\uF0FC", false]], ["bell", ["\uF0F3", true]], ["bell-concierge", ["\uF562", false]], ["bell-slash", ["\uF1F6", true]], ["bezier-curve", ["\uF55B", false]], ["bicycle", ["\uF206", false]], ["binoculars", ["\uF1E5", false]], ["biohazard", ["\uF780", false]], ["bitcoin-sign", ["\uE0B4", false]], ["blender", ["\uF517", false]], ["blender-phone", ["\uF6B6", false]], ["blog", ["\uF781", false]], ["bold", ["\uF032", false]], ["bolt", ["\uF0E7", false]], ["bolt-lightning", ["\uE0B7", false]], ["bomb", ["\uF1E2", false]], ["bone", ["\uF5D7", false]], ["bong", ["\uF55C", false]], ["book", ["\uF02D", false]], ["book-atlas", ["\uF558", false]], ["book-bible", ["\uF647", false]], ["book-bookmark", ["\uE0BB", false]], ["book-journal-whills", ["\uF66A", false]], ["book-medical", ["\uF7E6", false]], ["book-open", ["\uF518", false]], ["book-open-reader", ["\uF5DA", false]], ["book-quran", ["\uF687", false]], ["book-skull", ["\uF6B7", false]], ["book-tanakh", ["\uF827", false]], ["bookmark", ["\uF02E", true]], ["border-all", ["\uF84C", false]], ["border-none", ["\uF850", false]], ["border-top-left", ["\uF853", false]], ["bore-hole", ["\uE4C3", false]], ["bottle-droplet", ["\uE4C4", false]], ["bottle-water", ["\uE4C5", false]], ["bowl-food", ["\uE4C6", false]], ["bowl-rice", ["\uE2EB", false]], ["bowling-ball", ["\uF436", false]], ["box", ["\uF466", false]], ["box-archive", ["\uF187", false]], ["box-open", ["\uF49E", false]], ["box-tissue", ["\uE05B", false]], ["boxes-packing", ["\uE4C7", false]], ["boxes-stacked", ["\uF468", false]], ["braille", ["\uF2A1", false]], ["brain", ["\uF5DC", false]], ["brazilian-real-sign", ["\uE46C", false]], ["bread-slice", ["\uF7EC", false]], ["bridge", ["\uE4C8", false]], ["bridge-circle-check", ["\uE4C9", false]], ["bridge-circle-exclamation", ["\uE4CA", false]], ["bridge-circle-xmark", ["\uE4CB", false]], ["bridge-lock", ["\uE4CC", false]], ["bridge-water", ["\uE4CE", false]], ["briefcase", ["\uF0B1", false]], ["briefcase-medical", ["\uF469", false]], ["broom", ["\uF51A", false]], ["broom-ball", ["\uF458", false]], ["brush", ["\uF55D", false]], ["bucket", ["\uE4CF", false]], ["bug", ["\uF188", false]], ["bug-slash", ["\uE490", false]], ["bugs", ["\uE4D0", false]], ["building", ["\uF1AD", true]], ["building-circle-arrow-right", ["\uE4D1", false]], ["building-circle-check", ["\uE4D2", false]], ["building-circle-exclamation", ["\uE4D3", false]], ["building-circle-xmark", ["\uE4D4", false]], ["building-columns", ["\uF19C", false]], ["building-flag", ["\uE4D5", false]], ["building-lock", ["\uE4D6", false]], ["building-ngo", ["\uE4D7", false]], ["building-shield", ["\uE4D8", false]], ["building-un", ["\uE4D9", false]], ["building-user", ["\uE4DA", false]], ["building-wheat", ["\uE4DB", false]], ["bullhorn", ["\uF0A1", false]], ["bullseye", ["\uF140", false]], ["burger", ["\uF805", false]], ["burst", ["\uE4DC", false]], ["bus", ["\uF207", false]], ["bus-simple", ["\uF55E", false]], ["business-time", ["\uF64A", false]], ["c", ["C", false]], ["cable-car", ["\uF7DA", false]], ["cake-candles", ["\uF1FD", false]], ["calculator", ["\uF1EC", false]], ["calendar", ["\uF133", true]], ["calendar-check", ["\uF274", true]], ["calendar-day", ["\uF783", false]], ["calendar-days", ["\uF073", true]], ["calendar-minus", ["\uF272", true]], ["calendar-plus", ["\uF271", true]], ["calendar-week", ["\uF784", false]], ["calendar-xmark", ["\uF273", true]], ["camera", ["\uF030", false]], ["camera-retro", ["\uF083", false]], ["camera-rotate", ["\uE0D8", false]], ["campground", ["\uF6BB", false]], ["candy-cane", ["\uF786", false]], ["cannabis", ["\uF55F", false]], ["capsules", ["\uF46B", false]], ["car", ["\uF1B9", false]], ["car-battery", ["\uF5DF", false]], ["car-burst", ["\uF5E1", false]], ["car-on", ["\uE4DD", false]], ["car-rear", ["\uF5DE", false]], ["car-side", ["\uF5E4", false]], ["car-tunnel", ["\uE4DE", false]], ["caravan", ["\uF8FF", false]], ["caret-down", ["\uF0D7", false]], ["caret-left", ["\uF0D9", false]], ["caret-right", ["\uF0DA", false]], ["caret-up", ["\uF0D8", false]], ["carrot", ["\uF787", false]], ["cart-arrow-down", ["\uF218", false]], ["cart-flatbed", ["\uF474", false]], ["cart-flatbed-suitcase", ["\uF59D", false]], ["cart-plus", ["\uF217", false]], ["cart-shopping", ["\uF07A", false]], ["cash-register", ["\uF788", false]], ["cat", ["\uF6BE", false]], ["cedi-sign", ["\uE0DF", false]], ["cent-sign", ["\uE3F5", false]], ["certificate", ["\uF0A3", false]], ["chair", ["\uF6C0", false]], ["chalkboard", ["\uF51B", false]], ["chalkboard-user", ["\uF51C", false]], ["champagne-glasses", ["\uF79F", false]], ["charging-station", ["\uF5E7", false]], ["chart-area", ["\uF1FE", false]], ["chart-bar", ["\uF080", true]], ["chart-column", ["\uE0E3", false]], ["chart-gantt", ["\uE0E4", false]], ["chart-line", ["\uF201", false]], ["chart-pie", ["\uF200", false]], ["chart-simple", ["\uE473", false]], ["check", ["\uF00C", false]], ["check-double", ["\uF560", false]], ["check-to-slot", ["\uF772", false]], ["cheese", ["\uF7EF", false]], ["chess", ["\uF439", false]], ["chess-bishop", ["\uF43A", true]], ["chess-board", ["\uF43C", false]], ["chess-king", ["\uF43F", true]], ["chess-knight", ["\uF441", true]], ["chess-pawn", ["\uF443", true]], ["chess-queen", ["\uF445", true]], ["chess-rook", ["\uF447", true]], ["chevron-down", ["\uF078", false]], ["chevron-left", ["\uF053", false]], ["chevron-right", ["\uF054", false]], ["chevron-up", ["\uF077", false]], ["child", ["\uF1AE", false]], ["child-dress", ["\uE59C", false]], ["child-reaching", ["\uE59D", false]], ["child-rifle", ["\uE4E0", false]], ["children", ["\uE4E1", false]], ["church", ["\uF51D", false]], ["circle", ["\uF111", true]], ["circle-arrow-down", ["\uF0AB", false]], ["circle-arrow-left", ["\uF0A8", false]], ["circle-arrow-right", ["\uF0A9", false]], ["circle-arrow-up", ["\uF0AA", false]], ["circle-check", ["\uF058", true]], ["circle-chevron-down", ["\uF13A", false]], ["circle-chevron-left", ["\uF137", false]], ["circle-chevron-right", ["\uF138", false]], ["circle-chevron-up", ["\uF139", false]], ["circle-dollar-to-slot", ["\uF4B9", false]], ["circle-dot", ["\uF192", true]], ["circle-down", ["\uF358", true]], ["circle-exclamation", ["\uF06A", false]], ["circle-h", ["\uF47E", false]], ["circle-half-stroke", ["\uF042", false]], ["circle-info", ["\uF05A", false]], ["circle-left", ["\uF359", true]], ["circle-minus", ["\uF056", false]], ["circle-nodes", ["\uE4E2", false]], ["circle-notch", ["\uF1CE", false]], ["circle-pause", ["\uF28B", true]], ["circle-play", ["\uF144", true]], ["circle-plus", ["\uF055", false]], ["circle-question", ["\uF059", true]], ["circle-radiation", ["\uF7BA", false]], ["circle-right", ["\uF35A", true]], ["circle-stop", ["\uF28D", true]], ["circle-up", ["\uF35B", true]], ["circle-user", ["\uF2BD", true]], ["circle-xmark", ["\uF057", true]], ["city", ["\uF64F", false]], ["clapperboard", ["\uE131", false]], ["clipboard", ["\uF328", true]], ["clipboard-check", ["\uF46C", false]], ["clipboard-list", ["\uF46D", false]], ["clipboard-question", ["\uE4E3", false]], ["clipboard-user", ["\uF7F3", false]], ["clock", ["\uF017", true]], ["clock-rotate-left", ["\uF1DA", false]], ["clone", ["\uF24D", true]], ["closed-captioning", ["\uF20A", true]], ["cloud", ["\uF0C2", false]], ["cloud-arrow-down", ["\uF0ED", false]], ["cloud-arrow-up", ["\uF0EE", false]], ["cloud-bolt", ["\uF76C", false]], ["cloud-meatball", ["\uF73B", false]], ["cloud-moon", ["\uF6C3", false]], ["cloud-moon-rain", ["\uF73C", false]], ["cloud-rain", ["\uF73D", false]], ["cloud-showers-heavy", ["\uF740", false]], ["cloud-showers-water", ["\uE4E4", false]], ["cloud-sun", ["\uF6C4", false]], ["cloud-sun-rain", ["\uF743", false]], ["clover", ["\uE139", false]], ["code", ["\uF121", false]], ["code-branch", ["\uF126", false]], ["code-commit", ["\uF386", false]], ["code-compare", ["\uE13A", false]], ["code-fork", ["\uE13B", false]], ["code-merge", ["\uF387", false]], ["code-pull-request", ["\uE13C", false]], ["coins", ["\uF51E", false]], ["colon-sign", ["\uE140", false]], ["comment", ["\uF075", true]], ["comment-dollar", ["\uF651", false]], ["comment-dots", ["\uF4AD", true]], ["comment-medical", ["\uF7F5", false]], ["comment-slash", ["\uF4B3", false]], ["comment-sms", ["\uF7CD", false]], ["comments", ["\uF086", true]], ["comments-dollar", ["\uF653", false]], ["compact-disc", ["\uF51F", false]], ["compass", ["\uF14E", true]], ["compass-drafting", ["\uF568", false]], ["compress", ["\uF066", false]], ["computer", ["\uE4E5", false]], ["computer-mouse", ["\uF8CC", false]], ["cookie", ["\uF563", false]], ["cookie-bite", ["\uF564", false]], ["copy", ["\uF0C5", true]], ["copyright", ["\uF1F9", true]], ["couch", ["\uF4B8", false]], ["cow", ["\uF6C8", false]], ["credit-card", ["\uF09D", true]], ["crop", ["\uF125", false]], ["crop-simple", ["\uF565", false]], ["cross", ["\uF654", false]], ["crosshairs", ["\uF05B", false]], ["crow", ["\uF520", false]], ["crown", ["\uF521", false]], ["crutch", ["\uF7F7", false]], ["cruzeiro-sign", ["\uE152", false]], ["cube", ["\uF1B2", false]], ["cubes", ["\uF1B3", false]], ["cubes-stacked", ["\uE4E6", false]], ["d", ["D", false]], ["database", ["\uF1C0", false]], ["delete-left", ["\uF55A", false]], ["democrat", ["\uF747", false]], ["desktop", ["\uF390", false]], ["dharmachakra", ["\uF655", false]], ["diagram-next", ["\uE476", false]], ["diagram-predecessor", ["\uE477", false]], ["diagram-project", ["\uF542", false]], ["diagram-successor", ["\uE47A", false]], ["diamond", ["\uF219", false]], ["diamond-turn-right", ["\uF5EB", false]], ["dice", ["\uF522", false]], ["dice-d20", ["\uF6CF", false]], ["dice-d6", ["\uF6D1", false]], ["dice-five", ["\uF523", false]], ["dice-four", ["\uF524", false]], ["dice-one", ["\uF525", false]], ["dice-six", ["\uF526", false]], ["dice-three", ["\uF527", false]], ["dice-two", ["\uF528", false]], ["disease", ["\uF7FA", false]], ["display", ["\uE163", false]], ["divide", ["\uF529", false]], ["dna", ["\uF471", false]], ["dog", ["\uF6D3", false]], ["dollar-sign", ["$", false]], ["dolly", ["\uF472", false]], ["dong-sign", ["\uE169", false]], ["door-closed", ["\uF52A", false]], ["door-open", ["\uF52B", false]], ["dove", ["\uF4BA", false]], ["down-left-and-up-right-to-center", ["\uF422", false]], ["down-long", ["\uF309", false]], ["download", ["\uF019", false]], ["dragon", ["\uF6D5", false]], ["draw-polygon", ["\uF5EE", false]], ["droplet", ["\uF043", false]], ["droplet-slash", ["\uF5C7", false]], ["drum", ["\uF569", false]], ["drum-steelpan", ["\uF56A", false]], ["drumstick-bite", ["\uF6D7", false]], ["dumbbell", ["\uF44B", false]], ["dumpster", ["\uF793", false]], ["dumpster-fire", ["\uF794", false]], ["dungeon", ["\uF6D9", false]], ["e", ["E", false]], ["ear-deaf", ["\uF2A4", false]], ["ear-listen", ["\uF2A2", false]], ["earth-africa", ["\uF57C", false]], ["earth-americas", ["\uF57D", false]], ["earth-asia", ["\uF57E", false]], ["earth-europe", ["\uF7A2", false]], ["earth-oceania", ["\uE47B", false]], ["egg", ["\uF7FB", false]], ["eject", ["\uF052", false]], ["elevator", ["\uE16D", false]], ["ellipsis", ["\uF141", false]], ["ellipsis-vertical", ["\uF142", false]], ["envelope", ["\uF0E0", true]], ["envelope-circle-check", ["\uE4E8", false]], ["envelope-open", ["\uF2B6", true]], ["envelope-open-text", ["\uF658", false]], ["envelopes-bulk", ["\uF674", false]], ["equals", ["=", false]], ["eraser", ["\uF12D", false]], ["ethernet", ["\uF796", false]], ["euro-sign", ["\uF153", false]], ["exclamation", ["!", false]], ["expand", ["\uF065", false]], ["explosion", ["\uE4E9", false]], ["eye", ["\uF06E", true]], ["eye-dropper", ["\uF1FB", false]], ["eye-low-vision", ["\uF2A8", false]], ["eye-slash", ["\uF070", true]], ["f", ["F", false]], ["face-angry", ["\uF556", true]], ["face-dizzy", ["\uF567", true]], ["face-flushed", ["\uF579", true]], ["face-frown", ["\uF119", true]], ["face-frown-open", ["\uF57A", true]], ["face-grimace", ["\uF57F", true]], ["face-grin", ["\uF580", true]], ["face-grin-beam", ["\uF582", true]], ["face-grin-beam-sweat", ["\uF583", true]], ["face-grin-hearts", ["\uF584", true]], ["face-grin-squint", ["\uF585", true]], ["face-grin-squint-tears", ["\uF586", true]], ["face-grin-stars", ["\uF587", true]], ["face-grin-tears", ["\uF588", true]], ["face-grin-tongue", ["\uF589", true]], ["face-grin-tongue-squint", ["\uF58A", true]], ["face-grin-tongue-wink", ["\uF58B", true]], ["face-grin-wide", ["\uF581", true]], ["face-grin-wink", ["\uF58C", true]], ["face-kiss", ["\uF596", true]], ["face-kiss-beam", ["\uF597", true]], ["face-kiss-wink-heart", ["\uF598", true]], ["face-laugh", ["\uF599", true]], ["face-laugh-beam", ["\uF59A", true]], ["face-laugh-squint", ["\uF59B", true]], ["face-laugh-wink", ["\uF59C", true]], ["face-meh", ["\uF11A", true]], ["face-meh-blank", ["\uF5A4", true]], ["face-rolling-eyes", ["\uF5A5", true]], ["face-sad-cry", ["\uF5B3", true]], ["face-sad-tear", ["\uF5B4", true]], ["face-smile", ["\uF118", true]], ["face-smile-beam", ["\uF5B8", true]], ["face-smile-wink", ["\uF4DA", true]], ["face-surprise", ["\uF5C2", true]], ["face-tired", ["\uF5C8", true]], ["fan", ["\uF863", false]], ["faucet", ["\uE005", false]], ["faucet-drip", ["\uE006", false]], ["fax", ["\uF1AC", false]], ["feather", ["\uF52D", false]], ["feather-pointed", ["\uF56B", false]], ["ferry", ["\uE4EA", false]], ["file", ["\uF15B", true]], ["file-arrow-down", ["\uF56D", false]], ["file-arrow-up", ["\uF574", false]], ["file-audio", ["\uF1C7", true]], ["file-circle-check", ["\uE5A0", false]], ["file-circle-exclamation", ["\uE4EB", false]], ["file-circle-minus", ["\uE4ED", false]], ["file-circle-plus", ["\uE494", false]], ["file-circle-question", ["\uE4EF", false]], ["file-circle-xmark", ["\uE5A1", false]], ["file-code", ["\uF1C9", true]], ["file-contract", ["\uF56C", false]], ["file-csv", ["\uF6DD", false]], ["file-excel", ["\uF1C3", true]], ["file-export", ["\uF56E", false]], ["file-image", ["\uF1C5", true]], ["file-import", ["\uF56F", false]], ["file-invoice", ["\uF570", false]], ["file-invoice-dollar", ["\uF571", false]], ["file-lines", ["\uF15C", true]], ["file-medical", ["\uF477", false]], ["file-pdf", ["\uF1C1", true]], ["file-pen", ["\uF31C", false]], ["file-powerpoint", ["\uF1C4", true]], ["file-prescription", ["\uF572", false]], ["file-shield", ["\uE4F0", false]], ["file-signature", ["\uF573", false]], ["file-video", ["\uF1C8", true]], ["file-waveform", ["\uF478", false]], ["file-word", ["\uF1C2", true]], ["file-zipper", ["\uF1C6", true]], ["fill", ["\uF575", false]], ["fill-drip", ["\uF576", false]], ["film", ["\uF008", false]], ["filter", ["\uF0B0", false]], ["filter-circle-dollar", ["\uF662", false]], ["filter-circle-xmark", ["\uE17B", false]], ["fingerprint", ["\uF577", false]], ["fire", ["\uF06D", false]], ["fire-burner", ["\uE4F1", false]], ["fire-extinguisher", ["\uF134", false]], ["fire-flame-curved", ["\uF7E4", false]], ["fire-flame-simple", ["\uF46A", false]], ["fish", ["\uF578", false]], ["fish-fins", ["\uE4F2", false]], ["flag", ["\uF024", true]], ["flag-checkered", ["\uF11E", false]], ["flag-usa", ["\uF74D", false]], ["flask", ["\uF0C3", false]], ["flask-vial", ["\uE4F3", false]], ["floppy-disk", ["\uF0C7", true]], ["florin-sign", ["\uE184", false]], ["folder", ["\uF07B", true]], ["folder-closed", ["\uE185", true]], ["folder-minus", ["\uF65D", false]], ["folder-open", ["\uF07C", true]], ["folder-plus", ["\uF65E", false]], ["folder-tree", ["\uF802", false]], ["font", ["\uF031", false]], ["football", ["\uF44E", false]], ["forward", ["\uF04E", false]], ["forward-fast", ["\uF050", false]], ["forward-step", ["\uF051", false]], ["franc-sign", ["\uE18F", false]], ["frog", ["\uF52E", false]], ["futbol", ["\uF1E3", true]], ["g", ["G", false]], ["gamepad", ["\uF11B", false]], ["gas-pump", ["\uF52F", false]], ["gauge", ["\uF624", false]], ["gauge-high", ["\uF625", false]], ["gauge-simple", ["\uF629", false]], ["gauge-simple-high", ["\uF62A", false]], ["gavel", ["\uF0E3", false]], ["gear", ["\uF013", false]], ["gears", ["\uF085", false]], ["gem", ["\uF3A5", true]], ["genderless", ["\uF22D", false]], ["ghost", ["\uF6E2", false]], ["gift", ["\uF06B", false]], ["gifts", ["\uF79C", false]], ["glass-water", ["\uE4F4", false]], ["glass-water-droplet", ["\uE4F5", false]], ["glasses", ["\uF530", false]], ["globe", ["\uF0AC", false]], ["golf-ball-tee", ["\uF450", false]], ["gopuram", ["\uF664", false]], ["graduation-cap", ["\uF19D", false]], ["greater-than", [">", false]], ["greater-than-equal", ["\uF532", false]], ["grip", ["\uF58D", false]], ["grip-lines", ["\uF7A4", false]], ["grip-lines-vertical", ["\uF7A5", false]], ["grip-vertical", ["\uF58E", false]], ["group-arrows-rotate", ["\uE4F6", false]], ["guarani-sign", ["\uE19A", false]], ["guitar", ["\uF7A6", false]], ["gun", ["\uE19B", false]], ["h", ["H", false]], ["hammer", ["\uF6E3", false]], ["hamsa", ["\uF665", false]], ["hand", ["\uF256", true]], ["hand-back-fist", ["\uF255", true]], ["hand-dots", ["\uF461", false]], ["hand-fist", ["\uF6DE", false]], ["hand-holding", ["\uF4BD", false]], ["hand-holding-dollar", ["\uF4C0", false]], ["hand-holding-droplet", ["\uF4C1", false]], ["hand-holding-hand", ["\uE4F7", false]], ["hand-holding-heart", ["\uF4BE", false]], ["hand-holding-medical", ["\uE05C", false]], ["hand-lizard", ["\uF258", true]], ["hand-middle-finger", ["\uF806", false]], ["hand-peace", ["\uF25B", true]], ["hand-point-down", ["\uF0A7", true]], ["hand-point-left", ["\uF0A5", true]], ["hand-point-right", ["\uF0A4", true]], ["hand-point-up", ["\uF0A6", true]], ["hand-pointer", ["\uF25A", true]], ["hand-scissors", ["\uF257", true]], ["hand-sparkles", ["\uE05D", false]], ["hand-spock", ["\uF259", true]], ["handcuffs", ["\uE4F8", false]], ["hands", ["\uF2A7", false]], ["hands-asl-interpreting", ["\uF2A3", false]], ["hands-bound", ["\uE4F9", false]], ["hands-bubbles", ["\uE05E", false]], ["hands-clapping", ["\uE1A8", false]], ["hands-holding", ["\uF4C2", false]], ["hands-holding-child", ["\uE4FA", false]], ["hands-holding-circle", ["\uE4FB", false]], ["hands-praying", ["\uF684", false]], ["handshake", ["\uF2B5", true]], ["handshake-angle", ["\uF4C4", false]], ["handshake-simple", ["\uF4C6", false]], ["handshake-simple-slash", ["\uE05F", false]], ["handshake-slash", ["\uE060", false]], ["hanukiah", ["\uF6E6", false]], ["hard-drive", ["\uF0A0", true]], ["hashtag", ["#", false]], ["hat-cowboy", ["\uF8C0", false]], ["hat-cowboy-side", ["\uF8C1", false]], ["hat-wizard", ["\uF6E8", false]], ["head-side-cough", ["\uE061", false]], ["head-side-cough-slash", ["\uE062", false]], ["head-side-mask", ["\uE063", false]], ["head-side-virus", ["\uE064", false]], ["heading", ["\uF1DC", false]], ["headphones", ["\uF025", false]], ["headphones-simple", ["\uF58F", false]], ["headset", ["\uF590", false]], ["heart", ["\uF004", true]], ["heart-circle-bolt", ["\uE4FC", false]], ["heart-circle-check", ["\uE4FD", false]], ["heart-circle-exclamation", ["\uE4FE", false]], ["heart-circle-minus", ["\uE4FF", false]], ["heart-circle-plus", ["\uE500", false]], ["heart-circle-xmark", ["\uE501", false]], ["heart-crack", ["\uF7A9", false]], ["heart-pulse", ["\uF21E", false]], ["helicopter", ["\uF533", false]], ["helicopter-symbol", ["\uE502", false]], ["helmet-safety", ["\uF807", false]], ["helmet-un", ["\uE503", false]], ["highlighter", ["\uF591", false]], ["hill-avalanche", ["\uE507", false]], ["hill-rockslide", ["\uE508", false]], ["hippo", ["\uF6ED", false]], ["hockey-puck", ["\uF453", false]], ["holly-berry", ["\uF7AA", false]], ["horse", ["\uF6F0", false]], ["horse-head", ["\uF7AB", false]], ["hospital", ["\uF0F8", true]], ["hospital-user", ["\uF80D", false]], ["hot-tub-person", ["\uF593", false]], ["hotdog", ["\uF80F", false]], ["hotel", ["\uF594", false]], ["hourglass", ["\uF254", true]], ["hourglass-end", ["\uF253", false]], ["hourglass-half", ["\uF252", true]], ["hourglass-start", ["\uF251", false]], ["house", ["\uF015", false]], ["house-chimney", ["\uE3AF", false]], ["house-chimney-crack", ["\uF6F1", false]], ["house-chimney-medical", ["\uF7F2", false]], ["house-chimney-user", ["\uE065", false]], ["house-chimney-window", ["\uE00D", false]], ["house-circle-check", ["\uE509", false]], ["house-circle-exclamation", ["\uE50A", false]], ["house-circle-xmark", ["\uE50B", false]], ["house-crack", ["\uE3B1", false]], ["house-fire", ["\uE50C", false]], ["house-flag", ["\uE50D", false]], ["house-flood-water", ["\uE50E", false]], ["house-flood-water-circle-arrow-right", ["\uE50F", false]], ["house-laptop", ["\uE066", false]], ["house-lock", ["\uE510", false]], ["house-medical", ["\uE3B2", false]], ["house-medical-circle-check", ["\uE511", false]], ["house-medical-circle-exclamation", ["\uE512", false]], ["house-medical-circle-xmark", ["\uE513", false]], ["house-medical-flag", ["\uE514", false]], ["house-signal", ["\uE012", false]], ["house-tsunami", ["\uE515", false]], ["house-user", ["\uE1B0", false]], ["hryvnia-sign", ["\uF6F2", false]], ["hurricane", ["\uF751", false]], ["i", ["I", false]], ["i-cursor", ["\uF246", false]], ["ice-cream", ["\uF810", false]], ["icicles", ["\uF7AD", false]], ["icons", ["\uF86D", false]], ["id-badge", ["\uF2C1", true]], ["id-card", ["\uF2C2", true]], ["id-card-clip", ["\uF47F", false]], ["igloo", ["\uF7AE", false]], ["image", ["\uF03E", true]], ["image-portrait", ["\uF3E0", false]], ["images", ["\uF302", true]], ["inbox", ["\uF01C", false]], ["indent", ["\uF03C", false]], ["indian-rupee-sign", ["\uE1BC", false]], ["industry", ["\uF275", false]], ["infinity", ["\uF534", false]], ["info", ["\uF129", false]], ["italic", ["\uF033", false]], ["j", ["J", false]], ["jar", ["\uE516", false]], ["jar-wheat", ["\uE517", false]], ["jedi", ["\uF669", false]], ["jet-fighter", ["\uF0FB", false]], ["jet-fighter-up", ["\uE518", false]], ["joint", ["\uF595", false]], ["jug-detergent", ["\uE519", false]], ["k", ["K", false]], ["kaaba", ["\uF66B", false]], ["key", ["\uF084", false]], ["keyboard", ["\uF11C", true]], ["khanda", ["\uF66D", false]], ["kip-sign", ["\uE1C4", false]], ["kit-medical", ["\uF479", false]], ["kitchen-set", ["\uE51A", false]], ["kiwi-bird", ["\uF535", false]], ["l", ["L", false]], ["land-mine-on", ["\uE51B", false]], ["landmark", ["\uF66F", false]], ["landmark-dome", ["\uF752", false]], ["landmark-flag", ["\uE51C", false]], ["language", ["\uF1AB", false]], ["laptop", ["\uF109", false]], ["laptop-code", ["\uF5FC", false]], ["laptop-file", ["\uE51D", false]], ["laptop-medical", ["\uF812", false]], ["lari-sign", ["\uE1C8", false]], ["layer-group", ["\uF5FD", false]], ["leaf", ["\uF06C", false]], ["left-long", ["\uF30A", false]], ["left-right", ["\uF337", false]], ["lemon", ["\uF094", true]], ["less-than", ["<", false]], ["less-than-equal", ["\uF537", false]], ["life-ring", ["\uF1CD", true]], ["lightbulb", ["\uF0EB", true]], ["lines-leaning", ["\uE51E", false]], ["link", ["\uF0C1", false]], ["link-slash", ["\uF127", false]], ["lira-sign", ["\uF195", false]], ["list", ["\uF03A", false]], ["list-check", ["\uF0AE", false]], ["list-ol", ["\uF0CB", false]], ["list-ul", ["\uF0CA", false]], ["litecoin-sign", ["\uE1D3", false]], ["location-arrow", ["\uF124", false]], ["location-crosshairs", ["\uF601", false]], ["location-dot", ["\uF3C5", false]], ["location-pin", ["\uF041", false]], ["location-pin-lock", ["\uE51F", false]], ["lock", ["\uF023", false]], ["lock-open", ["\uF3C1", false]], ["locust", ["\uE520", false]], ["lungs", ["\uF604", false]], ["lungs-virus", ["\uE067", false]], ["m", ["M", false]], ["magnet", ["\uF076", false]], ["magnifying-glass", ["\uF002", false]], ["magnifying-glass-arrow-right", ["\uE521", false]], ["magnifying-glass-chart", ["\uE522", false]], ["magnifying-glass-dollar", ["\uF688", false]], ["magnifying-glass-location", ["\uF689", false]], ["magnifying-glass-minus", ["\uF010", false]], ["magnifying-glass-plus", ["\uF00E", false]], ["manat-sign", ["\uE1D5", false]], ["map", ["\uF279", true]], ["map-location", ["\uF59F", false]], ["map-location-dot", ["\uF5A0", false]], ["map-pin", ["\uF276", false]], ["marker", ["\uF5A1", false]], ["mars", ["\uF222", false]], ["mars-and-venus", ["\uF224", false]], ["mars-and-venus-burst", ["\uE523", false]], ["mars-double", ["\uF227", false]], ["mars-stroke", ["\uF229", false]], ["mars-stroke-right", ["\uF22B", false]], ["mars-stroke-up", ["\uF22A", false]], ["martini-glass", ["\uF57B", false]], ["martini-glass-citrus", ["\uF561", false]], ["martini-glass-empty", ["\uF000", false]], ["mask", ["\uF6FA", false]], ["mask-face", ["\uE1D7", false]], ["mask-ventilator", ["\uE524", false]], ["masks-theater", ["\uF630", false]], ["mattress-pillow", ["\uE525", false]], ["maximize", ["\uF31E", false]], ["medal", ["\uF5A2", false]], ["memory", ["\uF538", false]], ["menorah", ["\uF676", false]], ["mercury", ["\uF223", false]], ["message", ["\uF27A", true]], ["meteor", ["\uF753", false]], ["microchip", ["\uF2DB", false]], ["microphone", ["\uF130", false]], ["microphone-lines", ["\uF3C9", false]], ["microphone-lines-slash", ["\uF539", false]], ["microphone-slash", ["\uF131", false]], ["microscope", ["\uF610", false]], ["mill-sign", ["\uE1ED", false]], ["minimize", ["\uF78C", false]], ["minus", ["\uF068", false]], ["mitten", ["\uF7B5", false]], ["mobile", ["\uF3CE", false]], ["mobile-button", ["\uF10B", false]], ["mobile-retro", ["\uE527", false]], ["mobile-screen", ["\uF3CF", false]], ["mobile-screen-button", ["\uF3CD", false]], ["money-bill", ["\uF0D6", false]], ["money-bill-1", ["\uF3D1", true]], ["money-bill-1-wave", ["\uF53B", false]], ["money-bill-transfer", ["\uE528", false]], ["money-bill-trend-up", ["\uE529", false]], ["money-bill-wave", ["\uF53A", false]], ["money-bill-wheat", ["\uE52A", false]], ["money-bills", ["\uE1F3", false]], ["money-check", ["\uF53C", false]], ["money-check-dollar", ["\uF53D", false]], ["monument", ["\uF5A6", false]], ["moon", ["\uF186", true]], ["mortar-pestle", ["\uF5A7", false]], ["mosque", ["\uF678", false]], ["mosquito", ["\uE52B", false]], ["mosquito-net", ["\uE52C", false]], ["motorcycle", ["\uF21C", false]], ["mound", ["\uE52D", false]], ["mountain", ["\uF6FC", false]], ["mountain-city", ["\uE52E", false]], ["mountain-sun", ["\uE52F", false]], ["mug-hot", ["\uF7B6", false]], ["mug-saucer", ["\uF0F4", false]], ["music", ["\uF001", false]], ["n", ["N", false]], ["naira-sign", ["\uE1F6", false]], ["network-wired", ["\uF6FF", false]], ["neuter", ["\uF22C", false]], ["newspaper", ["\uF1EA", true]], ["not-equal", ["\uF53E", false]], ["notdef", ["\uE1FE", true]], ["note-sticky", ["\uF249", true]], ["notes-medical", ["\uF481", false]], ["o", ["O", false]], ["object-group", ["\uF247", true]], ["object-ungroup", ["\uF248", true]], ["oil-can", ["\uF613", false]], ["oil-well", ["\uE532", false]], ["om", ["\uF679", false]], ["otter", ["\uF700", false]], ["outdent", ["\uF03B", false]], ["p", ["P", false]], ["pager", ["\uF815", false]], ["paint-roller", ["\uF5AA", false]], ["paintbrush", ["\uF1FC", false]], ["palette", ["\uF53F", false]], ["pallet", ["\uF482", false]], ["panorama", ["\uE209", false]], ["paper-plane", ["\uF1D8", true]], ["paperclip", ["\uF0C6", false]], ["parachute-box", ["\uF4CD", false]], ["paragraph", ["\uF1DD", false]], ["passport", ["\uF5AB", false]], ["paste", ["\uF0EA", true]], ["pause", ["\uF04C", false]], ["paw", ["\uF1B0", false]], ["peace", ["\uF67C", false]], ["pen", ["\uF304", false]], ["pen-clip", ["\uF305", false]], ["pen-fancy", ["\uF5AC", false]], ["pen-nib", ["\uF5AD", false]], ["pen-ruler", ["\uF5AE", false]], ["pen-to-square", ["\uF044", true]], ["pencil", ["\uF303", false]], ["people-arrows", ["\uE068", false]], ["people-carry-box", ["\uF4CE", false]], ["people-group", ["\uE533", false]], ["people-line", ["\uE534", false]], ["people-pulling", ["\uE535", false]], ["people-robbery", ["\uE536", false]], ["people-roof", ["\uE537", false]], ["pepper-hot", ["\uF816", false]], ["percent", ["%", false]], ["person", ["\uF183", false]], ["person-arrow-down-to-line", ["\uE538", false]], ["person-arrow-up-from-line", ["\uE539", false]], ["person-biking", ["\uF84A", false]], ["person-booth", ["\uF756", false]], ["person-breastfeeding", ["\uE53A", false]], ["person-burst", ["\uE53B", false]], ["person-cane", ["\uE53C", false]], ["person-chalkboard", ["\uE53D", false]], ["person-circle-check", ["\uE53E", false]], ["person-circle-exclamation", ["\uE53F", false]], ["person-circle-minus", ["\uE540", false]], ["person-circle-plus", ["\uE541", false]], ["person-circle-question", ["\uE542", false]], ["person-circle-xmark", ["\uE543", false]], ["person-digging", ["\uF85E", false]], ["person-dots-from-line", ["\uF470", false]], ["person-dress", ["\uF182", false]], ["person-dress-burst", ["\uE544", false]], ["person-drowning", ["\uE545", false]], ["person-falling", ["\uE546", false]], ["person-falling-burst", ["\uE547", false]], ["person-half-dress", ["\uE548", false]], ["person-harassing", ["\uE549", false]], ["person-hiking", ["\uF6EC", false]], ["person-military-pointing", ["\uE54A", false]], ["person-military-rifle", ["\uE54B", false]], ["person-military-to-person", ["\uE54C", false]], ["person-praying", ["\uF683", false]], ["person-pregnant", ["\uE31E", false]], ["person-rays", ["\uE54D", false]], ["person-rifle", ["\uE54E", false]], ["person-running", ["\uF70C", false]], ["person-shelter", ["\uE54F", false]], ["person-skating", ["\uF7C5", false]], ["person-skiing", ["\uF7C9", false]], ["person-skiing-nordic", ["\uF7CA", false]], ["person-snowboarding", ["\uF7CE", false]], ["person-swimming", ["\uF5C4", false]], ["person-through-window", ["\uE5A9", false]], ["person-walking", ["\uF554", false]], ["person-walking-arrow-loop-left", ["\uE551", false]], ["person-walking-arrow-right", ["\uE552", false]], ["person-walking-dashed-line-arrow-right", ["\uE553", false]], ["person-walking-luggage", ["\uE554", false]], ["person-walking-with-cane", ["\uF29D", false]], ["peseta-sign", ["\uE221", false]], ["peso-sign", ["\uE222", false]], ["phone", ["\uF095", false]], ["phone-flip", ["\uF879", false]], ["phone-slash", ["\uF3DD", false]], ["phone-volume", ["\uF2A0", false]], ["photo-film", ["\uF87C", false]], ["piggy-bank", ["\uF4D3", false]], ["pills", ["\uF484", false]], ["pizza-slice", ["\uF818", false]], ["place-of-worship", ["\uF67F", false]], ["plane", ["\uF072", false]], ["plane-arrival", ["\uF5AF", false]], ["plane-circle-check", ["\uE555", false]], ["plane-circle-exclamation", ["\uE556", false]], ["plane-circle-xmark", ["\uE557", false]], ["plane-departure", ["\uF5B0", false]], ["plane-lock", ["\uE558", false]], ["plane-slash", ["\uE069", false]], ["plane-up", ["\uE22D", false]], ["plant-wilt", ["\uE5AA", false]], ["plate-wheat", ["\uE55A", false]], ["play", ["\uF04B", false]], ["plug", ["\uF1E6", false]], ["plug-circle-bolt", ["\uE55B", false]], ["plug-circle-check", ["\uE55C", false]], ["plug-circle-exclamation", ["\uE55D", false]], ["plug-circle-minus", ["\uE55E", false]], ["plug-circle-plus", ["\uE55F", false]], ["plug-circle-xmark", ["\uE560", false]], ["plus", ["+", false]], ["plus-minus", ["\uE43C", false]], ["podcast", ["\uF2CE", false]], ["poo", ["\uF2FE", false]], ["poo-storm", ["\uF75A", false]], ["poop", ["\uF619", false]], ["power-off", ["\uF011", false]], ["prescription", ["\uF5B1", false]], ["prescription-bottle", ["\uF485", false]], ["prescription-bottle-medical", ["\uF486", false]], ["print", ["\uF02F", false]], ["pump-medical", ["\uE06A", false]], ["pump-soap", ["\uE06B", false]], ["puzzle-piece", ["\uF12E", false]], ["q", ["Q", false]], ["qrcode", ["\uF029", false]], ["question", ["?", false]], ["quote-left", ["\uF10D", false]], ["quote-right", ["\uF10E", false]], ["r", ["R", false]], ["radiation", ["\uF7B9", false]], ["radio", ["\uF8D7", false]], ["rainbow", ["\uF75B", false]], ["ranking-star", ["\uE561", false]], ["receipt", ["\uF543", false]], ["record-vinyl", ["\uF8D9", false]], ["rectangle-ad", ["\uF641", false]], ["rectangle-list", ["\uF022", true]], ["rectangle-xmark", ["\uF410", true]], ["recycle", ["\uF1B8", false]], ["registered", ["\uF25D", true]], ["repeat", ["\uF363", false]], ["reply", ["\uF3E5", false]], ["reply-all", ["\uF122", false]], ["republican", ["\uF75E", false]], ["restroom", ["\uF7BD", false]], ["retweet", ["\uF079", false]], ["ribbon", ["\uF4D6", false]], ["right-from-bracket", ["\uF2F5", false]], ["right-left", ["\uF362", false]], ["right-long", ["\uF30B", false]], ["right-to-bracket", ["\uF2F6", false]], ["ring", ["\uF70B", false]], ["road", ["\uF018", false]], ["road-barrier", ["\uE562", false]], ["road-bridge", ["\uE563", false]], ["road-circle-check", ["\uE564", false]], ["road-circle-exclamation", ["\uE565", false]], ["road-circle-xmark", ["\uE566", false]], ["road-lock", ["\uE567", false]], ["road-spikes", ["\uE568", false]], ["robot", ["\uF544", false]], ["rocket", ["\uF135", false]], ["rotate", ["\uF2F1", false]], ["rotate-left", ["\uF2EA", false]], ["rotate-right", ["\uF2F9", false]], ["route", ["\uF4D7", false]], ["rss", ["\uF09E", false]], ["ruble-sign", ["\uF158", false]], ["rug", ["\uE569", false]], ["ruler", ["\uF545", false]], ["ruler-combined", ["\uF546", false]], ["ruler-horizontal", ["\uF547", false]], ["ruler-vertical", ["\uF548", false]], ["rupee-sign", ["\uF156", false]], ["rupiah-sign", ["\uE23D", false]], ["s", ["S", false]], ["sack-dollar", ["\uF81D", false]], ["sack-xmark", ["\uE56A", false]], ["sailboat", ["\uE445", false]], ["satellite", ["\uF7BF", false]], ["satellite-dish", ["\uF7C0", false]], ["scale-balanced", ["\uF24E", false]], ["scale-unbalanced", ["\uF515", false]], ["scale-unbalanced-flip", ["\uF516", false]], ["school", ["\uF549", false]], ["school-circle-check", ["\uE56B", false]], ["school-circle-exclamation", ["\uE56C", false]], ["school-circle-xmark", ["\uE56D", false]], ["school-flag", ["\uE56E", false]], ["school-lock", ["\uE56F", false]], ["scissors", ["\uF0C4", false]], ["screwdriver", ["\uF54A", false]], ["screwdriver-wrench", ["\uF7D9", false]], ["scroll", ["\uF70E", false]], ["scroll-torah", ["\uF6A0", false]], ["sd-card", ["\uF7C2", false]], ["section", ["\uE447", false]], ["seedling", ["\uF4D8", false]], ["server", ["\uF233", false]], ["shapes", ["\uF61F", false]], ["share", ["\uF064", false]], ["share-from-square", ["\uF14D", true]], ["share-nodes", ["\uF1E0", false]], ["sheet-plastic", ["\uE571", false]], ["shekel-sign", ["\uF20B", false]], ["shield", ["\uF132", false]], ["shield-cat", ["\uE572", false]], ["shield-dog", ["\uE573", false]], ["shield-halved", ["\uF3ED", false]], ["shield-heart", ["\uE574", false]], ["shield-virus", ["\uE06C", false]], ["ship", ["\uF21A", false]], ["shirt", ["\uF553", false]], ["shoe-prints", ["\uF54B", false]], ["shop", ["\uF54F", false]], ["shop-lock", ["\uE4A5", false]], ["shop-slash", ["\uE070", false]], ["shower", ["\uF2CC", false]], ["shrimp", ["\uE448", false]], ["shuffle", ["\uF074", false]], ["shuttle-space", ["\uF197", false]], ["sign-hanging", ["\uF4D9", false]], ["signal", ["\uF012", false]], ["signature", ["\uF5B7", false]], ["signs-post", ["\uF277", false]], ["sim-card", ["\uF7C4", false]], ["sink", ["\uE06D", false]], ["sitemap", ["\uF0E8", false]], ["skull", ["\uF54C", false]], ["skull-crossbones", ["\uF714", false]], ["slash", ["\uF715", false]], ["sleigh", ["\uF7CC", false]], ["sliders", ["\uF1DE", false]], ["smog", ["\uF75F", false]], ["smoking", ["\uF48D", false]], ["snowflake", ["\uF2DC", true]], ["snowman", ["\uF7D0", false]], ["snowplow", ["\uF7D2", false]], ["soap", ["\uE06E", false]], ["socks", ["\uF696", false]], ["solar-panel", ["\uF5BA", false]], ["sort", ["\uF0DC", false]], ["sort-down", ["\uF0DD", false]], ["sort-up", ["\uF0DE", false]], ["spa", ["\uF5BB", false]], ["spaghetti-monster-flying", ["\uF67B", false]], ["spell-check", ["\uF891", false]], ["spider", ["\uF717", false]], ["spinner", ["\uF110", false]], ["splotch", ["\uF5BC", false]], ["spoon", ["\uF2E5", false]], ["spray-can", ["\uF5BD", false]], ["spray-can-sparkles", ["\uF5D0", false]], ["square", ["\uF0C8", true]], ["square-arrow-up-right", ["\uF14C", false]], ["square-caret-down", ["\uF150", true]], ["square-caret-left", ["\uF191", true]], ["square-caret-right", ["\uF152", true]], ["square-caret-up", ["\uF151", true]], ["square-check", ["\uF14A", true]], ["square-envelope", ["\uF199", false]], ["square-full", ["\uF45C", true]], ["square-h", ["\uF0FD", false]], ["square-minus", ["\uF146", true]], ["square-nfi", ["\uE576", false]], ["square-parking", ["\uF540", false]], ["square-pen", ["\uF14B", false]], ["square-person-confined", ["\uE577", false]], ["square-phone", ["\uF098", false]], ["square-phone-flip", ["\uF87B", false]], ["square-plus", ["\uF0FE", true]], ["square-poll-horizontal", ["\uF682", false]], ["square-poll-vertical", ["\uF681", false]], ["square-root-variable", ["\uF698", false]], ["square-rss", ["\uF143", false]], ["square-share-nodes", ["\uF1E1", false]], ["square-up-right", ["\uF360", false]], ["square-virus", ["\uE578", false]], ["square-xmark", ["\uF2D3", false]], ["staff-snake", ["\uE579", false]], ["stairs", ["\uE289", false]], ["stamp", ["\uF5BF", false]], ["stapler", ["\uE5AF", false]], ["star", ["\uF005", true]], ["star-and-crescent", ["\uF699", false]], ["star-half", ["\uF089", true]], ["star-half-stroke", ["\uF5C0", true]], ["star-of-david", ["\uF69A", false]], ["star-of-life", ["\uF621", false]], ["sterling-sign", ["\uF154", false]], ["stethoscope", ["\uF0F1", false]], ["stop", ["\uF04D", false]], ["stopwatch", ["\uF2F2", false]], ["stopwatch-20", ["\uE06F", false]], ["store", ["\uF54E", false]], ["store-slash", ["\uE071", false]], ["street-view", ["\uF21D", false]], ["strikethrough", ["\uF0CC", false]], ["stroopwafel", ["\uF551", false]], ["subscript", ["\uF12C", false]], ["suitcase", ["\uF0F2", false]], ["suitcase-medical", ["\uF0FA", false]], ["suitcase-rolling", ["\uF5C1", false]], ["sun", ["\uF185", true]], ["sun-plant-wilt", ["\uE57A", false]], ["superscript", ["\uF12B", false]], ["swatchbook", ["\uF5C3", false]], ["synagogue", ["\uF69B", false]], ["syringe", ["\uF48E", false]], ["t", ["T", false]], ["table", ["\uF0CE", false]], ["table-cells", ["\uF00A", false]], ["table-cells-large", ["\uF009", false]], ["table-columns", ["\uF0DB", false]], ["table-list", ["\uF00B", false]], ["table-tennis-paddle-ball", ["\uF45D", false]], ["tablet", ["\uF3FB", false]], ["tablet-button", ["\uF10A", false]], ["tablet-screen-button", ["\uF3FA", false]], ["tablets", ["\uF490", false]], ["tachograph-digital", ["\uF566", false]], ["tag", ["\uF02B", false]], ["tags", ["\uF02C", false]], ["tape", ["\uF4DB", false]], ["tarp", ["\uE57B", false]], ["tarp-droplet", ["\uE57C", false]], ["taxi", ["\uF1BA", false]], ["teeth", ["\uF62E", false]], ["teeth-open", ["\uF62F", false]], ["temperature-arrow-down", ["\uE03F", false]], ["temperature-arrow-up", ["\uE040", false]], ["temperature-empty", ["\uF2CB", false]], ["temperature-full", ["\uF2C7", false]], ["temperature-half", ["\uF2C9", false]], ["temperature-high", ["\uF769", false]], ["temperature-low", ["\uF76B", false]], ["temperature-quarter", ["\uF2CA", false]], ["temperature-three-quarters", ["\uF2C8", false]], ["tenge-sign", ["\uF7D7", false]], ["tent", ["\uE57D", false]], ["tent-arrow-down-to-line", ["\uE57E", false]], ["tent-arrow-left-right", ["\uE57F", false]], ["tent-arrow-turn-left", ["\uE580", false]], ["tent-arrows-down", ["\uE581", false]], ["tents", ["\uE582", false]], ["terminal", ["\uF120", false]], ["text-height", ["\uF034", false]], ["text-slash", ["\uF87D", false]], ["text-width", ["\uF035", false]], ["thermometer", ["\uF491", false]], ["thumbs-down", ["\uF165", true]], ["thumbs-up", ["\uF164", true]], ["thumbtack", ["\uF08D", false]], ["ticket", ["\uF145", false]], ["ticket-simple", ["\uF3FF", false]], ["timeline", ["\uE29C", false]], ["toggle-off", ["\uF204", false]], ["toggle-on", ["\uF205", false]], ["toilet", ["\uF7D8", false]], ["toilet-paper", ["\uF71E", false]], ["toilet-paper-slash", ["\uE072", false]], ["toilet-portable", ["\uE583", false]], ["toilets-portable", ["\uE584", false]], ["toolbox", ["\uF552", false]], ["tooth", ["\uF5C9", false]], ["torii-gate", ["\uF6A1", false]], ["tornado", ["\uF76F", false]], ["tower-broadcast", ["\uF519", false]], ["tower-cell", ["\uE585", false]], ["tower-observation", ["\uE586", false]], ["tractor", ["\uF722", false]], ["trademark", ["\uF25C", false]], ["traffic-light", ["\uF637", false]], ["trailer", ["\uE041", false]], ["train", ["\uF238", false]], ["train-subway", ["\uF239", false]], ["train-tram", ["\uE5B4", false]], ["transgender", ["\uF225", false]], ["trash", ["\uF1F8", false]], ["trash-arrow-up", ["\uF829", false]], ["trash-can", ["\uF2ED", true]], ["trash-can-arrow-up", ["\uF82A", false]], ["tree", ["\uF1BB", false]], ["tree-city", ["\uE587", false]], ["triangle-exclamation", ["\uF071", false]], ["trophy", ["\uF091", false]], ["trowel", ["\uE589", false]], ["trowel-bricks", ["\uE58A", false]], ["truck", ["\uF0D1", false]], ["truck-arrow-right", ["\uE58B", false]], ["truck-droplet", ["\uE58C", false]], ["truck-fast", ["\uF48B", false]], ["truck-field", ["\uE58D", false]], ["truck-field-un", ["\uE58E", false]], ["truck-front", ["\uE2B7", false]], ["truck-medical", ["\uF0F9", false]], ["truck-monster", ["\uF63B", false]], ["truck-moving", ["\uF4DF", false]], ["truck-pickup", ["\uF63C", false]], ["truck-plane", ["\uE58F", false]], ["truck-ramp-box", ["\uF4DE", false]], ["tty", ["\uF1E4", false]], ["turkish-lira-sign", ["\uE2BB", false]], ["turn-down", ["\uF3BE", false]], ["turn-up", ["\uF3BF", false]], ["tv", ["\uF26C", false]], ["u", ["U", false]], ["umbrella", ["\uF0E9", false]], ["umbrella-beach", ["\uF5CA", false]], ["underline", ["\uF0CD", false]], ["universal-access", ["\uF29A", false]], ["unlock", ["\uF09C", false]], ["unlock-keyhole", ["\uF13E", false]], ["up-down", ["\uF338", false]], ["up-down-left-right", ["\uF0B2", false]], ["up-long", ["\uF30C", false]], ["up-right-and-down-left-from-center", ["\uF424", false]], ["up-right-from-square", ["\uF35D", false]], ["upload", ["\uF093", false]], ["user", ["\uF007", true]], ["user-astronaut", ["\uF4FB", false]], ["user-check", ["\uF4FC", false]], ["user-clock", ["\uF4FD", false]], ["user-doctor", ["\uF0F0", false]], ["user-gear", ["\uF4FE", false]], ["user-graduate", ["\uF501", false]], ["user-group", ["\uF500", false]], ["user-injured", ["\uF728", false]], ["user-large", ["\uF406", false]], ["user-large-slash", ["\uF4FA", false]], ["user-lock", ["\uF502", false]], ["user-minus", ["\uF503", false]], ["user-ninja", ["\uF504", false]], ["user-nurse", ["\uF82F", false]], ["user-pen", ["\uF4FF", false]], ["user-plus", ["\uF234", false]], ["user-secret", ["\uF21B", false]], ["user-shield", ["\uF505", false]], ["user-slash", ["\uF506", false]], ["user-tag", ["\uF507", false]], ["user-tie", ["\uF508", false]], ["user-xmark", ["\uF235", false]], ["users", ["\uF0C0", false]], ["users-between-lines", ["\uE591", false]], ["users-gear", ["\uF509", false]], ["users-line", ["\uE592", false]], ["users-rays", ["\uE593", false]], ["users-rectangle", ["\uE594", false]], ["users-slash", ["\uE073", false]], ["users-viewfinder", ["\uE595", false]], ["utensils", ["\uF2E7", false]], ["v", ["V", false]], ["van-shuttle", ["\uF5B6", false]], ["vault", ["\uE2C5", false]], ["vector-square", ["\uF5CB", false]], ["venus", ["\uF221", false]], ["venus-double", ["\uF226", false]], ["venus-mars", ["\uF228", false]], ["vest", ["\uE085", false]], ["vest-patches", ["\uE086", false]], ["vial", ["\uF492", false]], ["vial-circle-check", ["\uE596", false]], ["vial-virus", ["\uE597", false]], ["vials", ["\uF493", false]], ["video", ["\uF03D", false]], ["video-slash", ["\uF4E2", false]], ["vihara", ["\uF6A7", false]], ["virus", ["\uE074", false]], ["virus-covid", ["\uE4A8", false]], ["virus-covid-slash", ["\uE4A9", false]], ["virus-slash", ["\uE075", false]], ["viruses", ["\uE076", false]], ["voicemail", ["\uF897", false]], ["volcano", ["\uF770", false]], ["volleyball", ["\uF45F", false]], ["volume-high", ["\uF028", false]], ["volume-low", ["\uF027", false]], ["volume-off", ["\uF026", false]], ["volume-xmark", ["\uF6A9", false]], ["vr-cardboard", ["\uF729", false]], ["w", ["W", false]], ["walkie-talkie", ["\uF8EF", false]], ["wallet", ["\uF555", false]], ["wand-magic", ["\uF0D0", false]], ["wand-magic-sparkles", ["\uE2CA", false]], ["wand-sparkles", ["\uF72B", false]], ["warehouse", ["\uF494", false]], ["water", ["\uF773", false]], ["water-ladder", ["\uF5C5", false]], ["wave-square", ["\uF83E", false]], ["weight-hanging", ["\uF5CD", false]], ["weight-scale", ["\uF496", false]], ["wheat-awn", ["\uE2CD", false]], ["wheat-awn-circle-exclamation", ["\uE598", false]], ["wheelchair", ["\uF193", false]], ["wheelchair-move", ["\uE2CE", false]], ["whiskey-glass", ["\uF7A0", false]], ["wifi", ["\uF1EB", false]], ["wind", ["\uF72E", false]], ["window-maximize", ["\uF2D0", true]], ["window-minimize", ["\uF2D1", true]], ["window-restore", ["\uF2D2", true]], ["wine-bottle", ["\uF72F", false]], ["wine-glass", ["\uF4E3", false]], ["wine-glass-empty", ["\uF5CE", false]], ["won-sign", ["\uF159", false]], ["worm", ["\uE599", false]], ["wrench", ["\uF0AD", false]], ["x", ["X", false]], ["x-ray", ["\uF497", false]], ["xmark", ["\uF00D", false]], ["xmarks-lines", ["\uE59A", false]], ["y", ["Y", false]], ["yen-sign", ["\uF157", false]], ["yin-yang", ["\uF6AD", false]], ["z", ["Z", false]]]
    );
    window.getFontAwesome6Metadata = () => {
      return new Map(metadata);
    };
    window.getFontAwesome6IconMetadata = (name) => {
      return metadata.get(aliases.get(name) || name);
    };
  })();

  // ts/WoltLabSuite/WebComponent/fa-icon.ts
  (() => {
    let isFA6Free;
    function isFontAwesome6Free() {
      if (isFA6Free === void 0) {
        isFA6Free = true;
        const iconFont = window.getComputedStyle(document.documentElement).getPropertyValue("--fa-font-family");
        if (iconFont === "Font Awesome 6 Pro") {
          isFA6Free = false;
        }
      }
      return isFA6Free;
    }
    const HeightMap = /* @__PURE__ */ new Map([
      [16, 14],
      [24, 18],
      [32, 28],
      [48, 42],
      [64, 56],
      [96, 84],
      [128, 112],
      [144, 130]
    ]);
    class FaIcon extends HTMLElement {
      connectedCallback() {
        if (!this.hasAttribute("size")) {
          this.setAttribute("size", "16");
        }
        this.validate();
        this.setIcon(this.name, this.solid);
        this.setAttribute("aria-hidden", "true");
      }
      validate() {
        if (!HeightMap.has(this.size)) {
          throw new TypeError("Must provide a valid icon size.");
        }
        if (this.name === "") {
          throw new TypeError("Must provide the name of the icon.");
        } else if (!this.isValidIconName(this.name)) {
          throw new TypeError(`The icon '${this.name}' is unknown or unsupported.`);
        }
      }
      setIcon(name, forceSolid = false) {
        if (!this.isValidIconName(name)) {
          throw new TypeError(`The icon '${name}' is unknown or unsupported.`);
        }
        if (!forceSolid && !this.hasNonSolidStyle(name)) {
          forceSolid = true;
        }
        if (name === this.name && forceSolid === this.solid) {
          if (this.shadowRoot !== null) {
            return;
          }
        }
        if (forceSolid) {
          this.setAttribute("solid", "");
        } else {
          this.removeAttribute("solid");
        }
        this.setAttribute("name", name);
        this.updateIcon();
      }
      isValidIconName(name) {
        return name !== null && window.getFontAwesome6IconMetadata(name) !== void 0;
      }
      hasNonSolidStyle(name) {
        if (isFontAwesome6Free()) {
          const [, hasRegularVariant] = window.getFontAwesome6IconMetadata(name);
          if (!hasRegularVariant) {
            return false;
          }
        }
        return true;
      }
      getShadowRoot() {
        if (this.shadowRoot === null) {
          return this.attachShadow({ mode: "open" });
        }
        return this.shadowRoot;
      }
      updateIcon() {
        const root = this.getShadowRoot();
        root.childNodes[0]?.remove();
        if (this.name === "spinner") {
          root.append(this.createSpinner());
        } else {
          const [codepoint] = window.getFontAwesome6IconMetadata(this.name);
          root.append(codepoint);
        }
      }
      createSpinner() {
        const container = document.createElement("div");
        container.innerHTML = `
        <svg class="spinner" viewBox="0 0 50 50">
          <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
        </svg>
      `;
        const style = document.createElement("style");
        style.textContent = `
        div,
        svg {
          height: var(--font-size);
          width: var(--font-size);
        }

        .spinner {
          animation: rotate 2s linear infinite;
        }
          
        .path {
          animation: dash 1.5s ease-in-out infinite;
          stroke: currentColor;
          stroke-linecap: round;
        }

        @keyframes rotate {
          100% {
            transform: rotate(360deg);
          }
        }

        @keyframes dash {
          0% {
            stroke-dasharray: 1, 150;
            stroke-dashoffset: 0;
          }
          50% {
            stroke-dasharray: 90, 150;
            stroke-dashoffset: -35;
          }
          100% {
            stroke-dasharray: 90, 150;
            stroke-dashoffset: -124;
          }
        }
      `;
        container.append(style);
        return container;
      }
      get solid() {
        return this.hasAttribute("solid");
      }
      get name() {
        return this.getAttribute("name") || "";
      }
      get size() {
        const size = this.getAttribute("size");
        if (size === null) {
          return 0;
        }
        return parseInt(size);
      }
      set size(size) {
        if (!HeightMap.has(size)) {
          throw new Error(`Refused to set the invalid icon size '${size}'.`);
        }
        this.setAttribute("size", size.toString());
      }
    }
    window.customElements.define("fa-icon", FaIcon);
  })();

  // ts/WoltLabSuite/WebComponent/woltlab-core-date-time.ts
  {
    const drift = Date.now() - window.TIME_NOW * 1e3;
    const locale = document.documentElement.lang;
    const resolveTimeZone = () => {
      let value = "";
      const meta = document.querySelector('meta[name="timezone"]');
      if (meta) {
        value = meta.content;
        try {
          Intl.DateTimeFormat(void 0, { timeZone: value });
        } catch {
          value = "";
        }
      }
      if (!value) {
        value = Intl.DateTimeFormat().resolvedOptions().timeZone;
      }
      return value;
    };
    const timeZone = resolveTimeZone();
    let todayDayStart;
    let yesterdayDayStart;
    const updateTodayAndYesterday = () => {
      const now = new Date();
      const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      if (todayDayStart !== today.getTime()) {
        todayDayStart = today.getTime();
        const yesterday = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
        yesterdayDayStart = yesterday.getTime();
      }
    };
    updateTodayAndYesterday();
    let TodayOrYesterday;
    ((TodayOrYesterday2) => {
      TodayOrYesterday2[TodayOrYesterday2["Today"] = 0] = "Today";
      TodayOrYesterday2[TodayOrYesterday2["Yesterday"] = -1] = "Yesterday";
    })(TodayOrYesterday || (TodayOrYesterday = {}));
    const DateFormatter = {
      Date: new Intl.DateTimeFormat(locale, { dateStyle: "long", timeZone }),
      DateAndTime: new Intl.DateTimeFormat(locale, { dateStyle: "long", timeStyle: "short", timeZone }),
      DayOfWeekAndTime: new Intl.DateTimeFormat(locale, {
        weekday: "long",
        hour: "2-digit",
        minute: "2-digit",
        timeZone
      }),
      Minutes: new Intl.RelativeTimeFormat(locale),
      TodayOrYesterday: new Intl.RelativeTimeFormat(locale, { numeric: "auto" })
    };
    let TimePeriod;
    ((TimePeriod2) => {
      TimePeriod2[TimePeriod2["OneMinute"] = 60] = "OneMinute";
      TimePeriod2[TimePeriod2["OneHour"] = 3600] = "OneHour";
      TimePeriod2[TimePeriod2["OneDay"] = 86400] = "OneDay";
      TimePeriod2[TimePeriod2["OneWeek"] = 604800] = "OneWeek";
    })(TimePeriod || (TimePeriod = {}));
    class WoltlabCoreDateTimeElement extends HTMLElement {
      #date;
      #timeElement;
      get date() {
        if (this.#date === void 0) {
          const value = this.getAttribute("date");
          if (!value) {
            throw new Error("The 'date' attribute is missing.");
          }
          this.#date = new Date(value);
        }
        return this.#date;
      }
      set date(date) {
        this.setAttribute("date", date.toISOString());
        this.refresh(true);
      }
      get static() {
        return this.hasAttribute("static");
      }
      set static(isStatic) {
        if (isStatic === true) {
          this.setAttribute("static", "");
        } else {
          this.removeAttribute("static");
        }
      }
      connectedCallback() {
        this.refresh(true);
      }
      refresh(updateTitle) {
        const date = this.date;
        const difference = Math.trunc((Date.now() - date.getTime() - drift) / 1e3);
        if (this.#timeElement === void 0) {
          this.#timeElement = document.createElement("time");
          const shadow = this.attachShadow({ mode: "open" });
          shadow.append(this.#timeElement);
        }
        if (updateTitle) {
          this.#timeElement.dateTime = date.toISOString();
          this.#timeElement.title = DateFormatter.DateAndTime.format(date);
        }
        let value;
        if (this.static) {
          value = this.#timeElement.title;
        } else {
          if (difference < 60 /* OneMinute */) {
            value = "TODO: a moment ago";
          } else if (difference < 3600 /* OneHour */) {
            const minutes = Math.trunc(difference / 60 /* OneMinute */);
            value = DateFormatter.Minutes.format(minutes * -1, "minute");
          } else if (date.getTime() > todayDayStart) {
            value = this.#formatTodayOrYesterday(date, 0 /* Today */);
          } else if (date.getTime() > yesterdayDayStart) {
            value = this.#formatTodayOrYesterday(date, -1 /* Yesterday */);
          } else if (difference < 604800 /* OneWeek */) {
            value = DateFormatter.DayOfWeekAndTime.format(date);
          } else {
            value = DateFormatter.Date.format(date);
          }
        }
        value = value.charAt(0).toUpperCase() + value.slice(1);
        this.#timeElement.textContent = value;
      }
      #formatTodayOrYesterday(date, dayOffset) {
        let value = DateFormatter.TodayOrYesterday.format(dayOffset, "day");
        const dateParts = DateFormatter.DayOfWeekAndTime.formatToParts(date);
        if (dateParts[0].type === "weekday") {
          const datePartsWithoutDayOfWeek = dateParts.slice(1).map((part) => part.value);
          datePartsWithoutDayOfWeek.unshift(value);
          value = datePartsWithoutDayOfWeek.join("");
        }
        return value;
      }
    }
    window.customElements.define("woltlab-core-date-time", WoltlabCoreDateTimeElement);
    const refreshAllTimeElements = () => {
      document.querySelectorAll("woltlab-core-date-time").forEach((element) => element.refresh(false));
    };
    let timer = void 0;
    const startTimer = () => {
      timer = window.setInterval(() => {
        updateTodayAndYesterday();
        refreshAllTimeElements();
      }, 6e4);
    };
    document.addEventListener("DOMContentLoaded", () => startTimer(), { once: true });
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) {
        window.clearInterval(timer);
      } else {
        refreshAllTimeElements();
        startTimer();
      }
    });
  }

  // ts/WoltLabSuite/WebComponent/index.ts
  window.WoltLabLanguageStore = LanguageStore_exports;
  window.WoltLabTemplate = Template;
})();
/**
 * Handles the low level management of language items.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
/**
 * Template provides a template scripting compiler
 * similar to the PHP one of WoltLab Suite Core. It supports a limited set of
 * useful commands and compiles templates down to a pure JavaScript Function.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
/**
 * The `<woltlab-core-date-time>` element formats a date time
 * string based on the user’s timezone and website locale. For
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
