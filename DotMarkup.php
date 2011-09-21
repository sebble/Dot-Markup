<?php

/*
 Notes:
   o  move parser and config into sys dir
   o  extend parser class to interface class in here
*/

class DotMarkup {

    var $source;
    var $html;
    
    var $groups;
    
    var $config;
    
    var $_config;
    var $_stored;
    
    //// User Functions ////
    
    function parseFile($filename) {
    
        $this->filename = basename($filename);
        $this->source = file_get_contents($filename);
        $this->_parseDocument();
    }
    function parseString($source) {
    
        $this->filename = '';
        $this->source = $filename;
        $this->_parseDocument();
    }
    
    //// Main ////
    
    function _parseDocument() {
    
        $this->_groups = array();
        $string = $this->source;
        echo "--------- Original ---------\n";
        echo $string . "\n\n";
        $string = $this->_parseGroups($string);
        echo "--------- Groups ---------\n";
        echo $string . "\n\n";
        $string = $this->_parseBlock($string);
        $string = $this->_restoreProcessed($string);
        echo "--------- Restored ---------\n";
        echo $string . "\n\n";
        
        $this->html = $string;
    }
    
    function _parseBlock($string) {
    
        $string = $this->_parseBlocks($string);
        echo "--------- Blocks ---------\n";
        echo $string . "\n\n";
        $string = $this->_parseLines($string);
        echo "--------- Lines ---------\n";
        echo $string . "\n\n";
        $string = $this->_parseTables($string);
        echo "--------- Tables ---------\n";
        echo $string . "\n\n";
        $string = $this->_parseDefinitionLists($string);
        echo "--------- Definition Lists ---------\n";
        echo $string . "\n\n";
        $string = $this->_parseLists($string);
        echo "--------- Lists ---------\n";
        echo $string . "\n\n";
        $string = $this->_parseParagraphs($string);
        echo "--------- Paragraphs ---------\n";
        echo $string . "\n\n";
        
        return $string;
    }
    
    function _parseInline($string) {
    
        $string = $this->_parseComplex($string);
        $string = $this->_parseTags($string);
        $string = $this->_parseSymbols($string);
        
        return $string;
    }
    
    //// Elements ////
    
    function _parseGroups($string) {
    
        do {
            $grpFound = false;
            
            ## protected first
            foreach($this->config['groups'] as $group) {
                if ($group[2] == 'protect' || $group[2] == 'protectall') {
                    ## prepare regex
                    $inner = $this->_innerRegex($group[0], $group[1]);
                    #var_dump($inner);
                    preg_match_all("#{$inner}#", $string, $match, PREG_SET_ORDER);
                    foreach($match as $g) {
                        $grpFound = true;
                        $keep = ($group[2] == 'protectall') ? $g[0] : $g[1] ;
                        $this->_extractProcessed($g[0], $keep, $string);
                    }
                }
            }
            foreach($this->config['groups'] as $group) {
                if ($group[2] !== 'protect' && $group[2] !== 'protectall') {
                    ## prepare regex
                    $inner = $this->_innerRegex($group[0], $group[1]);
                    #var_dump($inner);
                    preg_match_all("#{$inner}#", $string, $match, PREG_SET_ORDER);
                    foreach($match as $g) {
                        $grpFound = true;
                        $keep = ($group[2] == 'remove') ? '' : (
                            ($group[2] == 'block') ? $this->_parseBlock($g[1])
                                : $this->_parseInline($g[1])
                        );
                        $this->_extractProcessed($g[0], $keep, $string);
                    }
                }
            }
        } while ($grpFound);
        
        return $string;
    }
    
    function _parseBlocks($string) {
    
        foreach($this->config['blocks'] as $block) {
            ## prepare regex
            $re_id  = '(\#[a-z_][a-z0-9_\-]*)?((?:\.[a-z_][a-z0-9_\-]*)+)?';
            $block[0] = $this->_escapeRegexString($block[0]);
            $regex  = "^{$block[0]}{$re_id}$\r?\n([\w\W]*?)\r?\n^{$block[0]}$";
            preg_match_all("#^{$regex}$#mi", $string, $match, PREG_SET_ORDER);
            foreach($match as $b) {
                $id   = ($b[1] == '') ? '' : ' id="'.substr($b[1],1).'"' ; 
                $id   = ($b[2] == '') ? $id : $id.' class="'.
                  str_replace('.',' ',substr($b[2],1)).'"' ; 
                $html = "<{$block[1]}{$id}>" . $this->_parseInline($b[3])
                  . "</{$block[1]}>";
                $this->_extractProcessed($b[0], $html, $string);
            }
        }
        return $string;
    }
    
    function _parseLines($string) {
    
        foreach($this->config['lines'] as $line) {
            ## prepare regex
            $re_id  = '(\#[a-z_][a-z0-9_\-]*)?((?:\.[a-z_][a-z0-9_\-]*)+)?';
            $line[0] = $this->_escapeRegexString($line[0]);
            $regex  = "^[ \t]*{$line[0]}{$re_id}([^\r\n]*?)(?:{$line[0]})?$";
            preg_match_all("#^{$regex}$#mi", $string, $match, PREG_SET_ORDER);
            foreach($match as $b) {
                $id   = ($b[1] == '') ? '' : ' id="'.substr($b[1],1).'"' ; 
                $id   = ($b[2] == '') ? $id : $id.' class="'.
                  str_replace('.',' ',substr($b[2],1)).'"' ; 
                ## is this a singleton? (<hr />)
                ## note: isset instead of isTrue to avoid notice when not defined
                $html =  (isset($line[2])) ? "<{$line[1]}{$id} />" :
                    "<{$line[1]}{$id}>" . $this->_parseInline($b[3]) . "</{$line[1]}>" ;
                $this->_extractProcessed($b[0], $html, $string);
            }
        }
        return $string;
    }
    
    function _parseTables($string) {
    
        ## prepare regex
        $pre_id  = '(?:(?:(\#[a-z_][a-z0-9_\-]*)|((?:\.[a-z_][a-z0-9_\-]*)+)|(\#[a-z_][a-z0-9_\-]*)((?:\.[a-z_][a-z0-9_\-]*)+))\r?\n)?';
        $re_cap = '(?:^[ \t]*"([^\r\n]+)"$\r?\n)?';
        $re_id  = '(?:\#[a-z_][a-z0-9_\-]*)?(?:(?:\.[a-z_][a-z0-9_\-]*)+)?';
        $re_tr  = "^[ \t]*(?:\|\||!!)[<>=~]?\d*{$re_id}(?:.*?(?:\|\||!!){$re_id})+$\r?\n";
        $regex  = "^{$pre_id}{$re_cap}((?:{$re_tr})+)";
        
        preg_match_all("#$regex#mi", $string, $match, PREG_SET_ORDER);
        
        foreach($match as $t) {
            $t[0] = rtrim($t[0]); ## restore trailing newline
            #$id   = ($b[1] == '') ? '' : ' id="'.substr($b[1],1).'"' ; 
            #$id   = ($b[2] == '') ? $id : $id.' class="'.
            #  str_replace('.',' ',substr($b[2],1)).'"' ; 
            ## is this a singleton? (<hr />)
            ## note: isset instead of isTrue to avoid notice when not defined
            #$html =  (isset($line[2])) ? "<{$line[1]}{$id} />" :
            #    "<{$line[1]}{$id}>" . $this->_parseInline($b[3]) . "</{$line[1]}>" ;
            $this->_extractProcessed($t[0], "..table..", $string);
        }
        
        return $string;
    }
    
    function _parseDefinitionLists($string) {
    
        ## prepare regex
        $pre_id  = '(?:(?:(\#[a-z_][a-z0-9_\-]*)|((?:\.[a-z_][a-z0-9_\-]*)+)|(\#[a-z_][a-z0-9_\-]*)((?:\.[a-z_][a-z0-9_\-]*)+))\r?\n)?';
        $re_id  = '(?:\#[a-z_][a-z0-9_\-]*)?(?:(?:\.[a-z_][a-z0-9_\-]*)+)?';
        $re_dli = "^[ \t]+[:;]+:?{$re_id}[ \t]+[^\r\n]*$\r?\n";
        $regex  = "^{$pre_id}((?:{$re_dli})+)";
        
        preg_match_all("#$regex#mi", $string, $match, PREG_SET_ORDER);
        
        foreach($match as $l) {
            $l[0] = rtrim($l[0]); ## restore trailing newline
            #$id   = ($b[1] == '') ? '' : ' id="'.substr($b[1],1).'"' ;
            #$id   = ($b[2] == '') ? $id : $id.' class="'.
            #  str_replace('.',' ',substr($b[2],1)).'"' ;
            ## is this a singleton? (<hr />)
            ## note: isset instead of isTrue to avoid notice when not defined
            #$html =  (isset($line[2])) ? "<{$line[1]}{$id} />" :
            #    "<{$line[1]}{$id}>" . $this->_parseInline($b[3]) . "</{$line[1]}>" ;
            $this->_extractProcessed($l[0], "..defn list..", $string);
        }
        return $string;
    }
    
    function _parseLists($string) {
    
        ## New version of list parser will allow multiple symbols
        ## Symbols may optionally add a class to the list item
        
        ## prepare regex
        $symbols = array_map(function($a){return $a[0];}, $this->config['lists']);
        $re_symb = $this->_escapeRegexString(implode('',$symbols));
        #var_dump($re_symb);
        ## includes A-Z so that symbols can be case sensitive
        $pre_id  = '(?:(?:(\#[a-zA-Z_][a-zA-Z0-9_\-]*)|((?:\.[a-zA-Z_][a-zA-Z0-9_\-]*)+)|(\#[a-zA-Z_][a-zA-Z0-9_\-]*)((?:\.[a-zA-Z_][a-zA-Z0-9_\-]*)+))\r?\n)?';
        $re_id  = '(?:\#[a-zA-Z_][a-zA-Z0-9_\-]*)?(?:(?:\.[a-zA-Z_][a-zA-Z0-9_\-]*)+)?';
        #$pre_id = "(?:({$re_id})\r?\n)?";
        $re_li  = "^[ \t]+[{$re_symb}]+:?{$re_id}[ \t]+[^\r\n]*$\r?\n";
        $regex  = "^{$pre_id}((?:{$re_li})+)";
        
        preg_match_all("#$regex#m", $string, $match, PREG_SET_ORDER);
        
        foreach($match as $l) {
            $l[0] = rtrim($l[0]); ## restore trailing newline
            #$id   = ($b[1] == '') ? '' : ' id="'.substr($b[1],1).'"' ;
            #$id   = ($b[2] == '') ? $id : $id.' class="'.
            #  str_replace('.',' ',substr($b[2],1)).'"' ;
            ## is this a singleton? (<hr />)
            ## note: isset instead of isTrue to avoid notice when not defined
            #$html =  (isset($line[2])) ? "<{$line[1]}{$id} />" :
            #    "<{$line[1]}{$id}>" . $this->_parseInline($b[3]) . "</{$line[1]}>" ;
            $this->_extractProcessed($l[0], "..list..", $string);
        }
        
        return $string;
    }
    
    function _parseParagraphs($string) {
    
        ## Allow empty classes to force paragraph when using list symbol
        ## i.e., ".  my paragraph"
        ## How can we skip MD5s?
        
        $justParas = preg_replace('#[a-f0-9]{32}#', '', $string);
        
        $re_id  = '(\#[a-z_][a-z0-9_\-]*)?((?:\.[a-z_][a-z0-9_\-]*)+)?';
        $regex  = "^(?:\.[ \t]|{$re_id})?[ \t]*([^ \t\r\n][\w\W]+?)(?=\r?\n[ \t]*\r?\n)";
        
        preg_match_all("#$regex#m", $justParas, $match, PREG_SET_ORDER);
        
        foreach ($match as $p) {
            if (trim($p[3]) == '') continue;
            
            $id   = ($p[1] == '') ? '' : ' id="'.substr($p[1],1).'"' ;
            $id   = ($p[2] == '') ? $id : $id.' class="'.
              str_replace('.',' ',substr($p[2],1)).'"' ;
            $html = "<p{$id}>" . $this->_parseInline($p[3]) . "</p>" ;
            $this->_extractProcessed($p[0], $html, $string);
        }
        
        return $string;
    }
    
    //// Inline ////
    
    function _parseComplex($string) {
    
        $re_id  = '(\#[a-z_][a-z0-9_\-]*)?((?:\.[a-z_][a-z0-9_\-]*)+)?';
        $left   = '[[';
        $right  = ']]';
        $l      = $this->_escapeRegexString($left[0]);
        $r      = $this->_escapeRegexString($right[0]);
        $left   = $this->_escapeRegexString($left);
        $right  = $this->_escapeRegexString($right);
                
        foreach($this->config['complex'] as $complex) {
        
            if (preg_match('#[a-z]+#',$complex[0])) {
                ## Dot Markup compex objects
                $regex = "{$left}{$complex[0]}{$re_id}(?:\:([^|{$r}\"\|\s]+)(?:\|([^\s{$r}\"]+))?)?(?: ([^\"{$r}]+)(?: \"([^\"{$r}]+)\")?)?{$right}";
                
                preg_match_all("#{$regex}#i", $string, $match, PREG_SET_ORDER);
                $p = preg_match_all('#%\d+\$s#', $complex[1], $m);
                
                foreach ($match as $c) {
                
                    $id   = ($c[1] == '') ? '' : ' id="'.substr($c[1],1).'"' ;
                    $id   = ($c[2] == '') ? $id : $id.' class="'.
                      str_replace('.',' ',substr($c[2],1)).'"' ;
                    
                    $format = array();
                    $format[0] = $complex[1];
                    $format[1] = (isset($c[3])) ? $this->_parseInline($c[3]) : '';
                    $format[2] = (isset($c[5])) ? $this->_parseInline($c[5]) : '';
                    $format[3] = (isset($c[6])) ? $this->_parseInline($c[6]) : '';
                    $format[4] = $id;
                    $params = (isset($m[4])) ? explode(',',$m[4]) : array() ;
                    $format = array_merge($format, $params);
                    
                    for ($i=1;$i<=$p;$i++) {
                        if (!isset($format[$i])) $format[$i] = '';
                    }
                    
                    $html = call_user_func_array('sprintf', $format);
                    $html = preg_replace("# [a-z\-]+=\"\"(?=[ >])#", '', $html);
                    $this->_extractProcessed($c[0], $html, $string);
                }
            } else {
                ## custom complex identifiers...
                preg_match_all("#{$complex[0]}#", $string, $match, PREG_SET_ORDER);
                
                foreach ($match as $c) {
                    $format = $c;
                    $format[0] = $complex[1];
                    $html = call_user_func_array('sprintf', $format);
                    $this->_extractProcessed($c[0], $html, $string);
                }
            }
        }
        return $string;
    }
    
    function _parseTags($string) {
    
        $re_id  = '(\#[a-z_][a-z0-9_\-]*)?((?:\.[a-z_][a-z0-9_\-]*)+)?';
        
        foreach($this->config['tags'] as $tag) {
            
            $tag_   = $this->_escapeRegexString($tag[0]);
            preg_match_all("#{$tag_}{$re_id} ?([\s\S]+?){$tag_}#i", $string, $match, PREG_SET_ORDER);
            foreach ($match as $t) {
            
                $id   = ($t[1] == '') ? '' : ' id="'.substr($t[1],1).'"' ;
                $id   = ($t[2] == '') ? $id : $id.' class="'.
                  str_replace('.',' ',substr($t[2],1)).'"' ;
                
                $html = "<{$tag[1]}{$id}>{$t[3]}</{$tag[1]}>";
                $this->_extractProcessed($t[0], $html, $string);
            }
        }
        
        return $string;
    }
    
    function _parseSymbols($string) {
    
        foreach ($this->config['symbols'] as $symb) {
            
            if (isset($symb[2]) && $symb[2]) {
                $string = preg_replace("#{$symb[0]}#", $symb[1], $string);
            } else {
                $string = str_replace($symb[0], $symb[1], $string);
            }
        }
        return $string;
    }
    
    //// Sub-routines ////
    
    function _extractProcessed($source, $processed, &$string) {
    
        if ($processed == '') {
            $string = str_replace($source, '', $string);
        } else {
            $md5 = md5($source);
            $this->_stored[$md5] = $processed;
            $string = str_replace($source, $md5, $string);
        }
    }
    
    function _restoreProcessed($string) {
    
        do {
            $md5Found = false;
            preg_match_all('#[a-f0-9]{32}#', $string, $match, PREG_SET_ORDER);
            
            foreach($match as $md5) {
                $md5Found = true;
                $string = str_replace($md5[0], $this->_stored[$md5[0]], $string);
            }
        } while ($md5Found);
        return $string;
    }
    
    //// Utilities ////
    
    function _innerRegex($open, $close) {
    
        $o    = $this->_escapeRegexString($open[0]);
        $pen  = $this->_escapeRegexString(substr($open,1));
        $c    = $this->_escapeRegexString($close[0]);
        $lose = $this->_escapeRegexString(substr($close,1));
        
        // open ( [^oc] | o(?!pen) | c(?!lose) )* close
        return "{$o}{$pen}((?:[^{$o}{$c}]+|{$o}(?!{$pen})|{$c}(?!{$lose}))*?){$c}{$lose}";
    }
    
    function _escapeRegexString($string) {
    
        $chars = '\[\]\\\\\^\$\.\|\?\*\+\(\)\{\}\-\/\#';
        return preg_replace("#([$chars])#", '\\\\\\1', $string);
        
        $replace = array('[',']','\\','^','$','.','|','?','*','+','(',')','{','}','-','/','#');
        $with    = array('\[','\]','\\\\','\^','\$','\.','\|','\?','\*','\+','\(','\)','\{','\}','\-','\/','\#');
        
        return str_replace($replace, $with, $string);
    }
    
    function _idClass($string) {
    
        $re_idc = '';
        preg_match("#(\#[a-z_][a-z0-9_]*)?((?:\.[a-z_][a-z0-9_]*)+)?#i", $string, $match);
        var_dump($match);
    }
};

$Doc = new DotMarkup;
$Doc->config = array(
    'groups' => array(
        array('{{{','}}}','protect'),
        array('<script','</script>','protectall'),
        array('<!--','-->','remove'),
        array('[[[',']]]','block'),
        array('<<<','>>>','inline')
    ),
    'blocks' => array(
        array('>>>','address'),
        array('"""','blockquote'),
        array(':::','pre'),
        array('```','pre')
    ),
    'lines'=>array(
        array('======','h6'),
        array('=====','h5'),
        array('====','h4'),
        array('===','h3'),
        array('==','h2'),
        array('=','h1'),
        array('----','hr',true),
        array('+++','legend')
    ),
    'lists'=>array(
        array('*','ul'),
        array('#','ol'),
        array('+','ul'),
        array('-','ul'),
        array('>','ul'),
        array('.','ul'),
        array('~','ul'),
        array('o','ul'),
        array('!','ul','important'),
        array('?','ul','question'),
        array('i','ol','roman'),
        array('I','ol','roman-caps')
    ),
    'symbols'=>array(
        array('&(?!\#\d+;|[a-zA-Z0-9]+;)','&amp;',true),
        array('<','&lt;'),
        array('(c)','&copy;'),
        array('(C)','&copy;'),
        array('(r)','&reg;'),
        array('(R)','&reg;'),
        array('(tm)','&trade;'),
        array('(TM)','&trade;'),
        array('[TM]','&trade;'),
        array(' - ',' &ndash; '),
        array('--','&mdash;'),
        array('...','&hellip;'),
        array('<<','&laquo;'),
        array('>>','&raquo;'),
        array('^o','&deg;'),
        array('^2','&sup2;'),
        array('^3','&sup3;'),
        array('+-','&plusmn;'),
        array('!=','&ne;'),
        array('<=','&le;'),
        array('>=','&ge;'),
        array('<->','&harr;'),
        array('<-','&larr;'),
        array('->','&rarr;'),
        array('___','&nbsp;'),
        array('£','&pound;'),
        array('\\\\','<br />'),
        array('\b1/2\b','&frac12;',true),
        array('\b1/4\b','&frac14;',true),
        array('\b3/4\b','&frac34;',true),
        array('(\b\d+ ?)/(?= ?\d+\b)','\\1&divide;',true),
        array('(\b\d+ ?)x(?= ?\d+\b)','\\1&times;',true),
        array('  (?=\r?\n)','<br />',true)
    ),
    'tags'=>array(
        array('``','code'),
        array('--','del'),
        array('##','span'),
        array('~~','del'),
        array('\'\'','em'),
        array('++','ins'),
        array('""','q'),
        array('**','strong'),
        array(',,','sub'),
        array('__','sub'),
        array('^^','sup'),
        array('^^','sup')
    ),
    'complex'=>array(
        array('link','<a%4$s href="%1$s" title="%3$s">%2$s</a>'),
        array('mailto','<a%4$s href="mailto:%1$s" title="%3$s">%2$s</a>'),
        array('img','<img%4$s src="%1$s" title="%3$s" width="%5$s" height="%6$s" />'),
        array('abbr','<abbr%4$s title="%3$s" >%2$s</abbr>'),
        array('acr','<acronym%4$s title="%3$s">%2$s</acronym>'),
        array('name','<a%4$s name="%1$s" title="%3$s">%2$s</a>'),
        array('text','<label for="%1$s">%2$s</label><input%4$s type="text" name="%1$s" id="%1$s" placeholder="%3$s" />'),
        array('password','<label for="%1$s">%2$s</label><input%4$s type="password" name="%1$s" id="%1$s" />'),
        array('checkbox','<input%4$s type="checkbox" name="%1$s" id="%1$s_%5$s" value="%5$s" /><label title="%3$s" for="%1$s_%5$s">%2$s</label>'),
        array('radio','<input%4$s type="radio" name="%1$s" id="%1$s_%5$s" value="%5$s" /><label title="%3$s" for="%1$s_%5$s">%2$s</label>'),
        array('textarea','<label for="%1$s">%2$s</label><textarea%4$s name="%1$s" id="%1$s">%3$s</textarea>'),
        array('file','<label for="%1$s">%2$s</label><input%4$s type="file" name="%1$s" id="%1$s" />'),
        array('submit','<input%4$s type="submit" value="%2$s" />'),
        array('reset','<input%4$s type="reset" value="%2$s" />'),
        array('button','<input%4$s type="button" value="%2$s" />'),
        array('form','<form%4$s action="%1$s" method="%5$s"><fieldset><legend>%2$s</legend>'),
        array('\[([^\]]+)\]\(([^\)]+)\)','<a href="%1$s">%2$s</a>')
    )
);
$Doc->parseFile('test.dm');
echo $Doc->html;

#var_dump($Doc);
#print_r($Doc->lines);
