/* PEG Grammar for DotMarkup */

{
    /* Some useful functions */
    function flatten(array){
        var flat = [];
        for (var i = 0, l = array.length; i < l; i++){
            var type = Object.prototype.toString.call(array[i]).split(' ').pop().split(']').shift().toLowerCase();
            if (type) { flat = flat.concat(/^(array|collection|arguments|object)$/.test(type) ? flatten(array[i]) : array[i]); }
        }
        return flat;
    }
}


start
  = blankline? b:(block blankline?)* { return flatten(b).join(""); }

blankline
  = [\n]+ {return '\n';}

block
  = paragraph
  / heading

heading
  = sp? h:[=]+ [ \t]* t:text eol 
      { var i = h.length; return '<h'+i+'>'+t+'</h'+i+'>\n'; }

paragraph
  = p:[a-zA-Z \.:!,?]+ eol { return '<p>'+p.join('')+'</p>'; }



/* Useful character classes  */
eol
  = [ \t]* [\n]
text
  = p:(word sp*)* { return flatten(p).join(""); }
sp
  = [ \t]+ ![\n]
word
  = w:[A-Za-z0-9`¬!"£$%^&*()_\-+={};'#:@~,./<>?\[\]]+ { return w.join(""); }

