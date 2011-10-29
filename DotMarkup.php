<?php

/*
 Notes:
   o  move parser and config into sys dir
   o  extend parser class to interface class in here
 
 Bugs:
   o  uses str_replace for completed transforms
     -> should use preg_replace_callback instead
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
        $this->source = $source;
        $this->_parseDocument();
    }
    
    //// Main ////
    
    function _parseDocument() {
    
        $this->_groups = array();
        $string = $this->source;
        #echo "--------- Original ---------\n";
        #echo $string . "\n\n";
        $string = $this->_parseGroups($string);
        #echo "--------- Groups ---------\n";
        #echo $string . "\n\n";
        $string = $this->_parseBlock($string);
        $string = $this->_restoreProcessed($string);
        #echo "--------- Restored ---------\n";
        #echo $string . "\n\n";
        
        $this->html = $string;
    }
    
    function _parseBlock($string) {
    
        $string = $this->_parseBlocks($string);
        #echo "--------- Blocks ---------\n";
        #echo $string . "\n\n";
        $string = $this->_parseLines($string);
        #echo "--------- Lines ---------\n";
        #echo $string . "\n\n";
        $string = $this->_parseTables($string);
        #echo "--------- Tables ---------\n";
        #echo $string . "\n\n";
        $string = $this->_parseDefinitionLists($string);
        #echo "--------- Definition Lists ---------\n";
        #echo $string . "\n\n";
        $string = $this->_parseLists($string);
        #echo "--------- Lists ---------\n";
        #echo $string . "\n\n";
        $string = $this->_parseParagraphs($string);
        #echo "--------- Paragraphs ---------\n";
        #echo $string . "\n\n";
        
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
        $re_id2 = '(\#[a-z_][a-z0-9_\-]*)?((?:\.[a-z_][a-z0-9_\-]*)+)?';
        $re_th  = "^[ \t]*(?:\|\||!!)[<>=]?(?:[\^~]\d+)?{$re_id}(?:.*?!!{$re_id2})+$\r?\n";
        $re_tr  = "^[ \t]*(?:\|\||!!)[<>=]?(?:[\^~]\d+)?{$re_id}(?:.*?\|\|{$re_id})+$\r?\n";
        $re_tr2 = "^[ \t]*(?:\|\||!!)[<>=]?(?:[\^~]\d+)?{$re_id}(?:.*?(?:\|\|||!!){$re_id2})+$\r?\n";
        $re_td  = "(\|\||!!)([<>=]?)(?:~(\d+))?(?:\^(\d+))?{$re_id2}((?:[^!\|]|!(?!!)|\|(?!\|))+)";
        $regex  = "^{$pre_id}{$re_cap}((?:{$re_th})?)((?:{$re_tr})+)((?:{$re_th})?)";
        
        preg_match_all("#$regex#mi", $string, $match, PREG_SET_ORDER);
        
        #print_r($match);
        
        foreach($match as $t) {
            $t[0] = rtrim($t[0]); ## restore trailing newline
            
            $_id = $t[1].$t[3];
            $_cls = $t[2].$t[4];
            $id   = ($_id == '') ? '' : ' id="'.substr($_id,1).'"' ;
            $id   = ($_cls == '') ? $id : $id.' class="'.
              str_replace('.',' ',substr($_cls,1)).'"' ;
            
            $html = "<table{$id}>\n";
            if ($t[5] != '') $html .= "<caption>".$this->_parseInline($t[5])."</caption>\n";
            if ($t[6] != '') {
                $id   = ($t[7] == '') ? '' : ' id="'.substr($t[7],1).'"' ;
                $id   = ($t[8] == '') ? $id : $id.' class="'.
                  str_replace('.',' ',substr($t[8],1)).'"' ;
                preg_match_all("#{$re_td}#mi", $t[6], $cs, PREG_SET_ORDER);
                array_pop($cs);
                #print_r($cs);
                $html .= "<thead><tr{$id}>";
                foreach ($cs as $c) {
                    ### This version doesn't have alignment!
                    $id   = ($c[5] == '') ? '' : ' id="'.substr($c[5],1).'"' ;
                    $id   = ($c[6] == '') ? $id : $id.' class="'.
                      str_replace('.',' ',substr($c[6],1)).'"' ;
                    $cols = (intval($c[3])>0) ? " colspan=\"{$c[3]}\"" : '' ;
                    $rows = (intval($c[4])>0) ? " rowspan=\"{$c[4]}\"" : '' ;
                    
                    $html .= "<td{$id}{$cols}{$rows}>".$this->_parseInline(trim($c[7]))."</td>";
                    
                }
                $html .= "</tr></thead>\n";
            }
            ## split rows
            preg_match_all("#{$re_tr2}#mi", $t[9], $trs, PREG_SET_ORDER);
            $html .= "<tbody>\n";
            foreach ($trs as $tr) {
                $id   = (!isset($tr[1])||$tr[1]=='') ? '' : ' id="'.substr($tr[1],1).'"' ;
                $id   = (!isset($tr[2])) ? $id : $id.' class="'.
                  str_replace('.',' ',substr($tr[2],1)).'"' ;
                preg_match_all("#{$re_td}#mi", $tr[0], $cs, PREG_SET_ORDER);
                array_pop($cs);
                #print_r($cs);
                $html .= "    <tr{$id}>";
                foreach ($cs as $c) {
                    ### This version doesn't have alignment!
                    ### or <th> cells!
                    $id   = ($c[5] == '') ? '' : ' id="'.substr($c[5],1).'"' ;
                    $id   = ($c[6] == '') ? $id : $id.' class="'.
                      str_replace('.',' ',substr($c[6],1)).'"' ;
                    $cols = (intval($c[3])>0) ? " colspan=\"{$c[3]}\"" : '' ;
                    $rows = (intval($c[4])>0) ? " rowspan=\"{$c[4]}\"" : '' ;
                    
                    $html .= "<td{$id}{$cols}{$rows}>".$this->_parseInline(trim($c[7]))."</td>";
                    
                }
                $html .= "</tr>\n";
            }
            $html .= "</tbody>\n";
            if ($t[10] != '') {
                @$id   = ($t[11] == '') ? '' : ' id="'.substr($t[11],1).'"' ;
                @$id   = ($t[12] == '') ? $id : $id.' class="'.
                  str_replace('.',' ',substr($t[12],1)).'"' ;
                preg_match_all("#{$re_td}#mi", $t[10], $cs, PREG_SET_ORDER);
                array_pop($cs);
                #print_r($cs);
                $html .= "<tfoot><tr{$id}>";
                foreach ($cs as $c) {
                    ### This version doesn't have alignment!
                    $id   = ($c[5] == '') ? '' : ' id="'.substr($c[5],1).'"' ;
                    $id   = ($c[6] == '') ? $id : $id.' class="'.
                      str_replace('.',' ',substr($c[6],1)).'"' ;
                    $cols = (intval($c[3])>0) ? " colspan=\"{$c[3]}\"" : '' ;
                    $rows = (intval($c[4])>0) ? " rowspan=\"{$c[4]}\"" : '' ;
                    
                    $html .= "<td{$id}{$cols}{$rows}>".$this->_parseInline(trim($c[7]))."</td>";
                    
                }
                $html .= "</tr></tfoot>\n";
            }
            
            $html .= "</table>";
            $this->_extractProcessed($t[0], $html, $string);
        }
        
        return $string;
    }
    
    function _parseDefinitionLists($string) { ///////////// TO DO
    
        ## prepare regex
        $pre_id  = '(?:(?:(\#[a-z_][a-z0-9_\-]*)|((?:\.[a-z_][a-z0-9_\-]*)+)|(\#[a-z_][a-z0-9_\-]*)((?:\.[a-z_][a-z0-9_\-]*)+))\r?\n)?';
        $re_id  = '(?:\#[a-z_][a-z0-9_\-]*)?(?:(?:\.[a-z_][a-z0-9_\-]*)+)?';
        $re_id2  = '(\#[a-z_][a-z0-9_\-]*)?((?:\.[a-z_][a-z0-9_\-]*)+)?';
        $re_dli = "^[ \t]+[:;]{$re_id}[ \t]+[^\r\n]*$\r?\n";
        $re_dli2 = "^[ \t]+([:;]){$re_id2}[ \t]+([^\r\n]*)$\r?\n";
        $regex  = "^{$pre_id}((?:{$re_dli})+)";
        
        preg_match_all("#$regex#mi", $string, $match, PREG_SET_ORDER);
        
        foreach($match as $dl) {
            $dl[0] = rtrim($dl[0]); ## restore trailing newline
            
            $_id = $dl[1].$dl[3];
            $_cls = $dl[2].$dl[4];
            $id   = ($_id == '') ? '' : ' id="'.substr($_id,1).'"' ;
            $id   = ($_cls == '') ? $id : $id.' class="'.
              str_replace('.',' ',substr($_cls,1)).'"' ;
            
            $html = "<dl{$id}>\n";
            ## split lines
            preg_match_all("#{$re_dli2}#mi", $dl[5], $ml, PREG_SET_ORDER);
            
            foreach($ml as $dli) {
                $id   = ($dli[2] == '') ? '' : ' id="'.substr($dli[2],1).'"' ;
                $id   = ($dli[3] == '') ? $id : $id.' class="'.
                  str_replace('.',' ',substr($dli[3],1)).'"' ;
                $dty = ($dli[1] == ';') ? 'dt' : 'dd' ;
                $html .= "    <{$dty}{$id}>" . $this->_parseInline($dli[4]) . "</{$dty}>\n" ;
                
            }
            
            $html .= "</dl>";
            $this->_extractProcessed($dl[0], $html, $string);
        }
        return $string;
    }
    
    function _parseLists($string) { ///////////// TO DO
    
        ## New version of list parser will allow multiple symbols
        ## Symbols may optionally add a class to the list item
        
        ## prepare regex
        $symbols = array_map(function($a){return $a[0];}, $this->config['lists']);
        $re_symb = $this->_escapeRegexString(implode('',$symbols));
        #var_dump($re_symb);
        ## includes A-Z so that symbols can be case sensitive
        $pre_id  = '(?:(?:(\#[a-zA-Z_][a-zA-Z0-9_\-]*)|((?:\.[a-zA-Z_][a-zA-Z0-9_\-]*)+)|(\#[a-zA-Z_][a-zA-Z0-9_\-]*)((?:\.[a-zA-Z_][a-zA-Z0-9_\-]*)+))\r?\n)?';
        $re_id  = '(?:\#[a-zA-Z_][a-zA-Z0-9_\-]*)?(?:(?:\.[a-zA-Z_][a-zA-Z0-9_\-]*)+)?';
        $re_id2 = '(\#[a-zA-Z_][a-zA-Z0-9_\-]*)?((?:\.[a-zA-Z_][a-z0-9A-Z_\-]*)+)?';
        #$pre_id = "(?:({$re_id})\r?\n)?";
        $re_li  = "^[ \t]+[{$re_symb}]+:?{$re_id}[ \t]+[^\r\n]*$\r?\n";
        $re_li2 = "^[ \t]+([{$re_symb}]+):?{$re_id2}[ \t]+([^\r\n]*)$\r?\n";
        $regex  = "^{$pre_id}((?:{$re_li})+)";
        
        preg_match_all("#$regex#m", $string, $match, PREG_SET_ORDER);
        
        foreach($match as $l) {
            $l[0] = rtrim($l[0]); ## restore trailing newline
            
            $_id = $l[1].$l[3];
            $_cls = $l[2].$l[4];
            $id   = ($_id == '') ? '' : ' id="'.substr($_id,1).'"' ;
            $id   = ($_cls == '') ? $id : $id.' class="'.
              str_replace('.',' ',substr($_cls,1)).'"' ;
            
            ## This version only allows single depth lists!
            
            ## split lines
            preg_match_all("#{$re_li2}#mi", $l[5], $ml, PREG_SET_ORDER);
            
            $html = "<ul{$id}>\n";
            foreach($ml as $li) {
                $lty = 'ul'; ## this in wrong place...
                foreach ($this->config['lists'] as $cl) {
                    if ($cl[0] == $li[1]) {
                        $lty = $cl[1];
                        if (isset($cl[2])) $li[3] .= '.'.$cl[2];
                    }
                }
                
                $id   = ($li[2] == '') ? '' : ' id="'.substr($li[2],1).'"' ;
                $id   = ($li[3] == '') ? $id : $id.' class="'.
                  str_replace('.',' ',substr($li[3],1)).'"' ;
                
                $html .= "    <li{$id}>" . $this->_parseInline($li[4]) . "</li>\n" ;
            }
            $html .= "</ul>";
            
            $this->_extractProcessed($l[0], $html, $string);
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
        
            if (preg_match('#^[a-z]+$#',$complex[0])) {
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
                    
                    @$html = call_user_func_array('sprintf', $format);
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

