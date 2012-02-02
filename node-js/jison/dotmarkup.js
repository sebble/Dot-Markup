// dotmarkup.js (generator)
var Parser = require("jison").Parser;

var grammar = {
    "lex": {
        "rules": [
           ["\\s+", "/* skip whitespace */"],
           ["[0-9]+(?:\\.[0-9]+)?\\b", "return 'NUMBER';"],
           ["\\*",                     "return '*';"],
           ["\\/",                     "return '/';"],
           ["-",                       "return '-';"],
           ["\\+",                     "return '+';"],
           ["\\^",                     "return '^';"],
           ["\\(",                     "return '(';"],
           ["\\)",                     "return ')';"],
           ["PI\\b",                   "return 'PI';"],
           ["E\\b",                    "return 'E';"],
           ["$",                       "return 'EOF';"]
        ]
    },

    "operators": [
        ["left", "+", "-"],
        ["left", "*", "/"],
        ["left", "^"],
        ["left", "UMINUS"]
    ],

    "bnf": {
        "expressions" :[[ "e EOF",   "print($1); return $1;"  ]],
        
        "e" :[[ "e + e",   "$$ = $1 + $3;" ],
              [ "e - e",   "$$ = $1 - $3;" ],
              [ "e * e",   "$$ = $1 * $3;" ],
              [ "e / e",   "$$ = $1 / $3;" ],
              [ "e ^ e",   "$$ = Math.pow($1, $3);" ],
              [ "- e",     "$$ = -$2;", {"prec": "UMINUS"} ],
              [ "( e )",   "$$ = $2;" ],
              [ "NUMBER",  "$$ = Number(yytext);" ],
              [ "E",       "$$ = Math.E;" ],
              [ "PI",      "$$ = Math.PI;" ]]
    }
};

var parser = new Parser(grammar);

// generate source, ready to be written to disk
//var parserSource = parser.generate();

console.log(parser.parse("5*3"));
// you can also use the parser directly from memory

console.log(parser.parse("adfe34bc e82a"));
// returns true

//console.log(parser.parse("adfe34bc zxg"));
// throws lexical error
