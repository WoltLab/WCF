/**
 * Grammar for WoltLab/WCF/Template.
 * 
 * Recompile using:
 *    jison -m amd -o Template.grammar.js Template.grammar.jison
 * after making changes to the grammar.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Template.grammar
 */

%lex
%s command
%%

\{\*.*\*\} /* comment */
\{literal\}.*?\{\/literal\} { yytext = yytext.substring(9, yytext.length - 10); return 'T_LITERAL'; }
<command>\"([^"]|\\\.)*\" return 'T_QUOTED_STRING';
<command>\'([^']|\\\.)*\' return 'T_QUOTED_STRING';
\$ return 'T_VARIABLE';
[_a-zA-Z][_a-zA-Z0-9]* { return 'T_VARIABLE_NAME'; }
"."	 return '.';
"["	 return '[';
"]"	 return ']';
"("	 return '(';
")"	 return ')';
"="	 return '=';
"{ldelim}"  return '{ldelim}';
"{rdelim}"  return '{rdelim}';
"{#"	return '{#';
"{@"	return '{@';
"{if "	{ this.begin('command'); return '{if'; }
"{else if " { this.begin('command'); return '{elseif'; }
"{elseif "  { this.begin('command'); return '{elseif'; }
"{else}"    return '{else}';
"{/if}"     return '{/if}';
"{lang}"    return '{lang}';
"{/lang}"   return '{/lang}';
"{include " { this.begin('command'); return '{include'; }
"{implode " { this.begin('command'); return '{implode'; }
"{/implode}" return '{/implode}';
"{foreach "  { this.begin('command'); return '{foreach'; }
"{foreachelse}"  return '{foreachelse}';
"{/foreach}"  return '{/foreach}';
"{"	 return '{';
<command>"}" { this.popState(); return '}';}
"}"	 return '}';
\s+	 return 'T_WS';
<<EOF>>	    return 'EOF';
[^{]	return 'T_ANY';

/lex

%start TEMPLATE
%ebnf

%%

// A valid template is any number of CHUNKs.
TEMPLATE: CHUNK_STAR EOF { return $1 + ";"; };

CHUNK_STAR: CHUNK* {
	var result = $1.reduce(function (carry, item) {
		if (item.encode && !carry[1]) carry[0] += " + '" + item.value;
		else if (item.encode && carry[1]) carry[0] += item.value;
		else if (!item.encode && carry[1]) carry[0] += "' + " + item.value;
		else if (!item.encode && !carry[1]) carry[0] += " + " + item.value;
		
		carry[1] = item.encode;
		return carry;
	}, [ "''", false ]);
	if (result[1]) result[0] += "'";
	
	$$ = result[0];
};

CHUNK:
	PLAIN_ANY -> { encode: true, value: $1.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/(\r\n|\n|\r)/g, '\\n') }
|	T_LITERAL -> { encode: true, value: $1.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/(\r\n|\n|\r)/g, '\\n') }
|	COMMAND -> { encode: false, value: $1 }
;

PLAIN_ANY: T_ANY | '}' | '{' T_WS -> $1 + $2
| ']' | '[' | ')' | '(' | '.' | '=' | T_VARIABLE | T_VARIABLE_NAME | T_QUOTED_STRING | T_WS;

COMMAND:
	'{if' COMMAND_PARAMETERS '}' CHUNK_STAR (ELSE_IF)* ELSE? '{/if}' {
		$$ = "(function() { if (" + $2 + ") { return " + $4 + "; } " + $5.join(' ') + " " + ($6 || '') + " return ''; })()";
	}
|	'{include' COMMAND_PARAMETER_LIST '}' {
		if (!$2['file']) throw new Error('Missing parameter file');
		
		$$ = $2['file'] + ".fetch(v)";
	}
|	'{implode' COMMAND_PARAMETER_LIST '}' CHUNK_STAR '{/implode}' {
		if (!$2['from']) throw new Error('Missing parameter from');
		if (!$2['item']) throw new Error('Missing parameter item');
		if (!$2['glue']) $2['glue'] = "', '";
		
		$$ = "(function() { return " + $2['from'] + ".map(function(item) { v[" + $2['item'] + "] = item; return " + $4 + "; }).join(" + $2['glue'] + "); })()";
	}
|	'{foreach' COMMAND_PARAMETER_LIST '}' CHUNK_STAR FOREACH_ELSE? '{/foreach}' {
		if (!$2['from']) throw new Error('Missing parameter from');
		if (!$2['item']) throw new Error('Missing parameter item');
		
		$$ = "(function() {"
		+ "var looped = false, result = '';"
		+ "if (" + $2['from'] + " instanceof Array) {"
			+ "for (var i = 0; i < " + $2['from'] + ".length; i++) { looped = true;"
				+ "v[" + $2['key'] + "] = i;"
				+ "v[" + $2['item'] + "] = " + $2['from'] + "[i];"
				+ "result += " + $4 + ";"
			+ "}"
		+ "} else {"
			+ "for (var key in " + $2['from'] + ") {"
				+ "if (!" + $2['from'] + ".hasOwnProperty(key)) continue;"
				+ "looped = true;"
				+ "v[" + $2['key'] + "] = key;"
				+ "v[" + $2['item'] + "] = " + $2['from'] + "[key];"
				+ "result += " + $4 + ";"
			+ "}"
		+ "}"
		+ "return (looped ? result : " + ($5 || "''") + "); })()"
	}
|	'{lang}' CHUNK_STAR '{/lang}' -> "Language.get(" + $2 + ")"
|	'{' VARIABLE '}'  -> "StringUtil.escapeHTML(" + $2 + ")"
|	'{#' VARIABLE '}' -> "StringUtil.formatNumeric(" + $2 + ")"
|	'{@' VARIABLE '}' -> $2
|	'{ldelim}' -> "'{'"
|	'{rdelim}' -> "'}'"
;

ELSE: '{else}' CHUNK_STAR -> "else { return " + $2 + "; }"
;

ELSE_IF: '{elseif' COMMAND_PARAMETERS '}' CHUNK_STAR -> "else if (" + $2 + ") { return " + $4 + "; }"
;

FOREACH_ELSE: '{foreachelse}' CHUNK_STAR -> $2
;

// VARIABLE parses a valid variable access (with optional property access)
VARIABLE: T_VARIABLE T_VARIABLE_NAME VARIABLE_SUFFIX* -> "v['" + $2 + "']" + $3.join('');
;

VARIABLE_SUFFIX:
	'[' COMMAND_PARAMETERS ']' -> $1 + $2 + $3
|	'.' T_VARIABLE_NAME -> "['" + $2 + "']"
|	'(' COMMAND_PARAMETERS? ')' -> $1 + ($2 || '') + $3
;

COMMAND_PARAMETER_LIST:
	T_VARIABLE_NAME '=' COMMAND_PARAMETER_VALUE T_WS COMMAND_PARAMETER_LIST { $$ = $5; $$[$1] = $3; }
|	T_VARIABLE_NAME '=' COMMAND_PARAMETER_VALUE { $$ = {}; $$[$1] = $3; }
;

COMMAND_PARAMETER_VALUE: T_QUOTED_STRING | VARIABLE;

// COMMAND_PARAMETERS parses anything that is valid between a command name and the closing brace
COMMAND_PARAMETERS: COMMAND_PARAMETER+ -> $1.join('')
;
COMMAND_PARAMETER: T_ANY | T_WS | '=' | T_QUOTED_STRING | VARIABLE | T_VARIABLE_NAME;
