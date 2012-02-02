<?php

/**
   Wiki-Text Parser
    * highly configurable
    * easy to implement
    * html indentation
    * structure extraction (from HTML) [TODO]
    * extensive default config
   
   Notes:
     - Inline parser allows line-breaks, this would cause problems if run on an entire document. although a double list would still be misinterpreted as bold without any newlines.
     - Should strip down a lot of the config so that it need not be reset
   Usage:
     - wiki_parse_string( $string, [$config, [$reset]] );
     - new WikiParser( [$string, [$config]] ); $wiki->parseString( [$string] );
   
   Sebastian Mellor <sebble@sebble.com>
 **/

class WikiParser{
    
    var $_Symbols;
    var $_Inline;
    var $_Complex;
    var $_Groups;
    var $_Liners;
    var $_Blocks;
    
    var $_Options;
    
    var $_String;
    var $_HTML;
    var $_Protected;
    
    
    function WikiParser( $string = false, $config = false ){
        
        $this->_String  = '';
        $this->_HTML    = '';
        $this->_Protected   = array(array(),array());
        
        $this->_Symbols = array();
        $this->_Inline  = array();
        $this->_Complex = array();
        $this->_Groups  = array();
        $this->_Liners  = array();
        $this->_Blocks  = array();
        $this->_Options = array();
        
        $this->_Options['regex_escape'] = '¬([\\\\\.^$?\(\)\{\}\[\]\-+*<>=:])¬';
        $this->_Options['regex_attribs'] = '(?:#([a-zA-Z0-9_]+))?((?:\.[a-zA-Z0-9_]+)*)';
        
        $this->_Options['complex_start'] = '[[';
        $this->_Options['complex_end'] = ']]';
        
        $this->_Options['table_class_alt'] = 'odd';
        $this->_Options['table_align'] = 'html';
        $this->_Options['table_cellspacing_fix'] = true;
        
        $this->_Options['tidy_auto'] = true;
        $this->_Options['tidy_indent'] = 0;
        $this->_Options['tidy_indent_step'] = '    ';
        
        if($string) $this->_String = $string;
        if($config) $this->loadConfig($config);
    }
    
    function loadConfig( $config, $reset = false ){
        
        if($reset) $this->reset();
        
        foreach($config['options'] as $opt=>$val){
            $this->setOption($opt,$val);
        }
        foreach($config['symbols'] as $sym){
            $this->setSymbol($sym[0],$sym[1],$sym[2]);
        }
        foreach($config['inlines'] as $sym){
            $this->setInline($sym[0],$sym[1],$sym[2],$sym[3]);
        }
        foreach($config['complex'] as $sym){
            $this->setComplex($sym[0],$sym[1],$sym[2]);
        }
        foreach($config['groups'] as $sym){
            $this->setGroup($sym[0],$sym[1],$sym[2],$sym[3]);
        }
        foreach($config['liners'] as $sym){
            $this->setLiner($sym[0],$sym[1],$sym[2],$sym[3],$sym[4]);
        }
        foreach($config['blocks'] as $sym){
            $this->setBlock($sym[0],$sym[1],$sym[2],$sym[3]);
        }
    }
    
    /* User Config */
    
    function setSymbol( $string, $html, $before = true ){
        
        $this->_Symbols[] = array($string, $html, $before);
    }
    function setInline( $delimiters, $html, $attribs = true, $parse = true ){
        
        $this->_Inline[] = array($delimiters, $html, $attribs, $parse);
    }
    function setComplex( $name, $format, $params=0 ){
        
        $this->_Complex[] = array($name, $format, $params);
    }
    function setGroup( $start, $end, $eval = false, $inline = false ){
        
        $this->_Groups[] = array($start, $end, $eval, $inline);
    }
    function setLiner( $start, $content, $end, $tag, $singleton = false ){
        
        $this->_Liners[] = array($start, $content, $end, $tag, $singleton);
    }
    function setBlock( $delimiter, $tag, $parse = true, $inline = false ){
        
        $this->_Blocks[] = array($delimiter, $tag, $parse, $inline);
    }
    function reset(){
        
        $this->WikiParser();
    }
    
    function setOption( $name, $value ){
        
        $this->_Options[$name] = $value;
    }
    
    /* User Main */
    
    function parseString( $string = false ){
        
        if($string) $this->_String = $string;
        $this->_String  = str_replace(array("\r\n","\r"),"\n",$this->_String);
        $this->_HTML    = $this->_parseDocument($this->_String);
        if($this->_Options['tidy_auto'])
            $this->_HTML= $this->tidyHTML($this->_HTML);
        
        return $this->_HTML;
    }
    
    function tidyHTML( $indent=false ){
        
        if($indent===false){}else{
            $this->_Options['tidy_indent']=$indent;
        }
        $this->_HTML = $this->_html_indent($this->_HTML);
        return $this->_HTML;
    }
    
    function getStructure( /* unknown */ ){
        
        return $this->_html_structure($this->_HTML);
    }
    
    /* System */
    
    function _parseDocument( $string ){
        
        $string = $this->_parseGroups($string);
        $string = $this->_parseBlocks($string);
        #$string = $this->_parseInline($string); // <-- do this per block element
        
        #echo '<pre>'.htmlentities($string).'</pre><hr />';
        # return protected bits
        $count = 0;
        do {
            $string = str_replace($this->_Protected[0],$this->_Protected[1],$string,$count);
        } while ($count > 0);
        
        return $string;
    }
    
    function _parseGroups( $string ){
        
        foreach($this->_Groups as $group){
            $open  = $group[0];
            $close = $group[1];
            $o = $this->_escape($open[0]);
            $c = $this->_escape($close[0]);
            $orest = $this->_escape(substr($open,1));
            $crest = $this->_escape(substr($close,1));
            
            do {
                // <<< (?: [^<>] | <(?!<<) | >(?!>>) )* >>>
                preg_match_all("¬$o$orest((?:[^$o$c]|$o(?!$orest)|$c(?!$crest))*)$c{$crest}¬",$string,$match,PREG_SET_ORDER);
                
                foreach($match as $nest){
                    $nest[1] = ($group[2]) ? (
                            ($group[3]) ? $this->_parseInline($nest[1]) : $this->_parseDocument($nest[1])
                        ) : $nest[0] ;
                    $this->_protect($nest[0],$nest[1],$string);
                }
            } while(count($match)>0 && $group[2]);
        }
        return $string;
    }
    
    function _parseBlocks( $string ){
        
        $attrs  = $this->_Options['regex_attribs'];
        $nl = '(?<=^|\n)';
        $eol = '(?=$|\\n)';
        
        # parse one-liners
        foreach($this->_Liners as $liner){
            preg_match_all("¬{$nl}[ \t]*{$liner[0]}$attrs({$liner[1]}){$liner[2]}[ \t]*{$eol}¬",$string,$match,PREG_SET_ORDER);
            foreach($match as $m){
                $id = ($m[1]=='') ? '' : ' id="'.$m[1].'"' ;
                $class = $this->_class($m[2]);
                $m[3] = ($liner[4]) ? "<{$liner[3]}$id$class />" :
                    "<{$liner[3]}$id$class>".$this->_parseInline($m[3])."</{$liner[3]}>" ;
                $this->_protect($m[0],$m[3],$string);
            }
        }
        
        # parse simple blocks
        foreach($this->_Blocks as $block){
            preg_match_all("¬{$nl}{$block[0]}{$attrs}[ \t]*\n([\s\S]+?){$block[0]}[ \t]*{$eol}¬",$string,$match,PREG_SET_ORDER);
            foreach($match as $m){
                $id = ($m[1]=='') ? '' : ' id="'.$m[1].'"' ;
                $class = $this->_class($m[2]);
                $m[3] = ($block[2]) ? (
                        ($block[3]) ? $this->_parseInline($m[3]) : "\n".$this->_parseDocument($m[3])."\n"
                    ) : $m[0] ;
                $m[3] = "<{$block[1]}$id$class>{$m[3]}</{$block[1]}>";
                $this->_protect($m[0],$m[3],$string);
            }
        }
        
        $lines  = explode("\n",$string);
        $last   = 'NONE';
        $state  = 'NONE';
        $temp   = '';
        $doc    = '';
        
        do{ // while state!='NONE'
        
            if(count($lines)>0){
                $line = array_shift($lines);
                # set state
                if(trim($line)==''){
                    $state = 'BLANK';
                }else
                if(preg_match('|^[0-9a-f]{32}$|',$line)){
                    $state = 'MD5';
                }else
                if(preg_match("¬^[ \t]+([*#]+){$attrs}(:)?[ \t]*([^\n]*)\$¬",$line,$match)){
                    $state = 'LIST';
                }else
                if(preg_match("¬^[ \t]*([:;]){$attrs}[ \t]*([^\n]*)\$¬",$line,$match)){
                    $state = 'DL';
                }else
                if(preg_match("¬^[ \t]*((?:(?:\|\||\!\!){$attrs}{$nct}*)+)(\|\||\!\!){$attrs}[ \t]*\$¬",$line,$match)){
                    $state = 'TABLE';
                }else
                if(preg_match("¬^(?:(#[a-zA-Z0-9_]+)((?:\.[a-zA-Z0-9_]+)+)?|[ \t]*((?:\.[a-zA-Z0-9_]+)+))[ \t]*\$¬",$line,$match)){
                    $state = 'ID_CLASS';
                    //    (?:#([a-zA-Z0-9_]+))?((?:\.[a-zA-Z0-9_]+)*)
                }else{
                    // Add another check before this one to match exactly [ \t]*(.classname)+[ \t]*$
                    // and store this as a classname for items like list, para, table and dl if immediately following
                    // consider ID as well, but this could cause problems with numeric lists
                    // consider anly accepting these value if starting a line (no preceding spaces)
                    $state = 'PARA';
                }
            }else{
                $line='';
                $state='NONE';
            }
            $nct = '(?:[^\!\|]|\!(?!\!)|\|(?!\|))';
            
            # finish off
            if($last!=$state){
                switch($last){
                    case 'ID_CLASS':
                        $temp = $match[1]; $temp2 = $match[2].$match[3];
                    case 'PARA':
                        $doc.= "<p{$temp2}>".$this->_parseInline($temp)."</p>\n";
                        break;
                    case 'LIST':
                        $doc.= $this->_listdiff($temp,'',':'); // <-- stops extra li
                        break;
                    case 'DL':
                        $doc.= "</dl>\n";
                        break;
                    case 'TABLE':
                        if(!$temp2) $doc.="</tbody>\n";
                        $doc.= "</table>\n";
                        break;
                }
                $temp=null;$temp2=null;
            }
            
            # start/continue
            switch($state){
                case 'PARA':
                    if($last==$state){$temp.=" ".$line;}
                    else{
                        if(preg_match("¬^[ \t]*{$attrs}[ \t]([\s\S]+)¬",$line,$m)){
                            $id = ($m[1]=='') ? '' : ' id="'.$m[1].'"' ;
                            $class = $this->_class($m[2]);
                            $temp2=$id.$class;
                            $temp=$m[3];
                        }else{
                            $temp=$line;
                        }
                    }
                    break;
                case 'LIST':
                    if($temp==$match[1]){
                        $id = ($match[2]=='') ? '' : ' id="'.$match[2].'"' ;
                        $class = $this->_class($match[3]);
                        $doc.= "</li>\n<li$id$class>".$this->_parseInline($match[5]);
                    }else{
                        $id = ($match[2]=='') ? '' : ' id="'.$match[2].'"' ;
                        $class = $this->_class($match[3]);
                        $doc.= $this->_listdiff($temp,$match[1],$match[4],$id.$class);
                        $doc.= $this->_parseInline($match[5]);
                    }
                    $temp = $match[1];
                    break;
                case 'DL':
                    if($last!=$state){$doc.="<dl>\n";}
                    $id = ($match[2]=='') ? '' : ' id="'.$match[2].'"' ;
                    $class = $this->_class($match[3]);
                    $doc.= ($match[1]==';') ?
                        "<dt$id$class>".$this->_parseInline($match[4])."</dt>\n" :
                        "<dd$id$class>".$this->_parseInline($match[4])."</dd>\n" ;
                    break;
                case 'TABLE':
                    if($temp%2==0&&$match[4]=='||'&&$temp!=0) $match[6].='.'.$this->_Options['table_class_alt'];
                    $cid = ($match[5]=='') ? '' : ' id="'.$match[5].'"' ;
                    $cid.= $this->_class($match[6]);
                    $cs = ($this->_Options['table_cellspacing_fix'])?' cellspacing="0"':'';
                    if($last!=$state){$doc.="<table$cid$cs>\n";$temp=0;$temp2=false;}
                    if(preg_match("¬^[ \t]*\!\!{$attrs}\"({$nct}*)\"\!\![ \t]*([^\n]*)\$¬",$line,$m)){
                        $id = ($m[1]=='') ? '' : ' id="'.$m[1].'"' ;
                        $class = $this->_class($m[2]);
                        $doc.= "<caption$id$class>".$this->_parseInline($m[3])."</caption>\n";
                    }else{
                        if($match[4]=='!!'&&$temp==0){
                            $doc.="<thead><tr$cid>";$temp++;}
                        elseif($match[4]=='||'&&$temp<2){
                            $doc.="<tbody>\n<tr$cid>";$temp=2;}
                        elseif($match[4]=='!!'&&$temp==1){
                            $doc.="<tfoot><tr$cid>";$temp++;}
                        elseif($match[4]=='!!'&&$temp>1){
                            $doc.="</tbody>\n<tfoot><tr$cid>";$temp++;$temp2=true;}
                        elseif($match[4]=='||'&&$temp>1){
                            $doc.="<tr$cid>";$temp++;}
                        preg_match_all("¬(\|\||\!\!){$attrs}({$nct}*)¬",$match[1],$m,PREG_SET_ORDER);
                        foreach($m as $cell){
                            $d = ($cell[1]=='||') ? 'd' : 'h' ;
                            $id = ($cell[2]=='') ? '' : ' id="'.$cell[2].'"' ;
                            # alignment
                            #echo '<pre>';var_dump($cell[4]);echo'</pre>';
                            preg_match('¬^([ \t]*)[^\\n]*?([ \t]*)$¬',$cell[4],$a);
                            #echo '<pre>';var_dump($a);echo'</pre>';
                            if(strlen($a[1])>1 && strlen($a[2])>1){ $align = 'center';
                            }elseif(strlen($a[1])>1){               $align = 'right';
                            }else{                                  $align = 'left'; }
                            if($this->_Options['table_align']=='html'){$align=" align=\"$align\"";}
                            else{$cell[3].='.'.$this->_Options['table_align'].$align;$align='';}
                            $class = $this->_class($cell[3]);
                            $doc.="<t$d$class$id$align>".$this->_parseInline($cell[4])."</t$d>";
                        }
                        if($match[4]=='!!'&&$temp==1){$doc.="</tr></thead>\n";}
                        elseif($match[4]=='!!'&&$temp>1){$doc.="</tr></tfoot>\n";}
                        else{$doc.="</tr>\n";}
                    }
                    #print_r($match);
                    break;
                case 'MD5':
                    $doc.= $line."\n";
                    break;
            }
            
            $last = $state;
        } while ($state!='NONE');
        
        # for each block
        $string = $this->_parseInline($doc);
        
        return $string;
    }
    
    function _parseInline( $string, $inside = false ){
                                    /* this stops symbols being done twice */
                                    /* but shouldn't happen with the protection on */
        
        $string = trim($string);
        $attrs  = $this->_Options['regex_attribs'];
        $left   = $this->_escape($this->_Options['complex_start']);
        $right  = $this->_escape($this->_Options['complex_end']);
        //$bar    = preg_replace($escape,'\\\\\\1',
        //            $this->_Options['complex_divider']);
        
        # protect manual entities
        preg_match_all("¬&#?[a-z0-9]+;¬i",$string,$match,PREG_SET_ORDER);
        foreach($match as $m){
            $this->_protect($m[0],$m[0],$string);
        }
        
        # first round of symbols
        foreach($this->_Symbols as $sym){
            if($sym[2]){
                //$string = str_replace($sym[0], $sym[1], $string);
                $this->_protect($sym[0],$sym[1],$string);
            }
        }
        
        # simple inline elements
        foreach($this->_Inline as $inl){
            $inl[0] = $this->_escape($inl[0]);
            if($inl[2]){
                # advanced version
                preg_match_all("¬{$inl[0]}$attrs ?([\s\S]+?){$inl[0]}¬",$string,$match,PREG_SET_ORDER);
                foreach($match as $p){
                    $p[3] = ($inl[3]) ? $this->_parseInline($p[3]) : $p[3] ;
                    $id = ($p[1]=='') ? '' : ' id="'.$p[1].'"' ;
                    $class = $this->_class($p[2]);
                    $p[4] = "<{$inl[1]}{$id}{$class}>{$p[3]}</{$inl[1]}>\n";
                    $this->_protect($p[0],$p[4],$string);
                }
            }else{
                #$string = preg_replace("¬{$inl[0]}([\s\S]+?){$inl[0]}¬","<{$inl[1]}>\\1</{$inl[1]}>",$string); # replace with match all (protect)
                preg_match_all("¬{$inl[0]}([\s\S]+?){$inl[0]}¬",$string,$match,PREG_SET_ORDER);
                foreach($match as $m){
                    $m[1] = ($inl[3]) ? "<{$inl[1]}>".$this->_parseInline($m[1])."</{$inl[1]}>" : "<{$inl[1]}>{$m[1]}</{$inl[1]}>" ;
                    $this->_protect($m[0],$m[1],$string);
                }
            }
        }
        
        # complex inline elements
        foreach($this->_Complex as $clx){
            preg_match_all("¬$left{$clx[0]}$attrs(?:\:([^|$right\"\|\s]+)(?:\|([^\s$right\"]+))?)?(?: ([^\"$right]+)(?:\"([^\"$right]+)\")?)?{$right}¬",$string,$match,PREG_SET_ORDER);
            foreach($match as $m){
                $format = array();
                $id = ($m[1]=='') ? '' : ' id="'.$m[1].'"' ;
                $class = $this->_class($m[2]);
                $params = explode(',',$m[4]);
                $format[0] = $clx[1];
                $format[1] = $m[3];
                $format[2] = $this->_parseInline($m[5]);
                $format[3] = $this->_parseInline($m[6]);
                $format[4] = $id.$class;
                for($i=0;$i<$clx[2];$i++){
                    $format[] = $params[$i];
                }
                $out = call_user_func_array('sprintf',$format);
                $this->_protect($m[0],$out,$string);
            }
        }
        
        # second round of symbols
        foreach($this->_Symbols as $sym){
            if(!$sym[2]){
                //string = str_replace($sym[0], $sym[1], $string
                $this->_protect($sym[0],$sym[1],$string);
            }
        }
        
        #$string = htmlentities($string);
        
        return $string;
    }
    
    /* Utilities */
    
    function _html_indent( $string ){
        
        $indent = intval($this->_Options['tidy_indent']);
        $step   = $this->_Options['tidy_indent_step'];
        $lines  = explode("\n",$string);
        $tidy   = '';
        
        while(count($lines)>0){
            $line = array_shift($lines);
            
            $open  = preg_match_all('|<[a-z][a-z0-9]*(?:[ \t][^>]*[^/>])?>|i',$line,$m);
            $close = preg_match_all('|</[ \t]*[a-z][a-z0-9]*[ \t]*>|i',$line,$m);
            $indent += $open - $close;
            if($open>$close){
                $fix = $open-$close;
            }elseif($close>$open){
                $fix = $close-$open-1;
            }else{
                $fix=0;
            }
            $doc.= str_repeat($step,$indent-$fix).$line."\n";
        }
        return $doc;
    }
    
    function _html_structure(){
        
        // simple DOM result
    }
    
    function _protect($match, $result, &$string){
        
        $md5 = md5($result);
        $this->_Protected[0][] = $md5;
        $this->_Protected[1][] = $result;
        $string = str_replace($match,$md5,$string);
    }
    
    function _class($string){
        
        $class = explode('.',$string);
        array_shift($class);
        $class = (count($class)==0)?'':' class="'.implode(' ',$class).'"';
        return $class;
    }
    
    function _escape($string){
        
        return preg_replace($this->_Options['regex_escape'],'\\\\\\1',$string);
    }
    
    function _listdiff($a, $b, $c='', $cid=''){
        
        $chA = preg_split('//',$a,-1,PREG_SPLIT_NO_EMPTY);
        $chB = preg_split('//',$b,-1,PREG_SPLIT_NO_EMPTY);
        $diff = array('+');
        for($i=0,$j=max(count($chA),count($chB));$i<$j;$i++){
            if($chA[$i]!=$chB[$i]){ 
                if(isset($chA[$i])) array_unshift($diff,$chA[$i]); # add (in reverse) the lists to close
                if(isset($chB[$i])) array_push($diff,$chB[$i]); # add to end the lists to open
            }
        }
        $add = false; $doc = '';
        if(end($diff)=='+') array_pop($diff);
        foreach($diff as $d){
            if($d=='+'){ $add=true;
            }elseif(!$add && $d=='*'){
                $doc.= "</li>\n</ul>\n";
            }elseif(!$add && $d=='#'){
                $doc.= "</li>\n</ol>\n";
            }elseif($add && $d=='*'){
                $doc.= "\n<ul>\n<li$cid>";
            }elseif($add && $d=='#'){
                $doc.= "\n<ol>\n<li$cid>";
            }
        }
        if(!$add && $c==''){ $doc.= "</li>\n<li$cid>"; }
        return $doc;
    }
    
};


global $WIKI_DEFAULT_CONFIG;
$WIKI_DEFAULT_CONFIG = array(
    'options'=>array(
        'table_class_alt'=>'odd',
        'table_align'=>'html', // <-- or a classname prefix i.e. 'td_' => 'td_left','td_center','td_right' -- blank also allowed
        'table_cellspacing_fix'=>true,
        'complex_start'=>'[[',
        'complex_end'=>']]',
        'tidy_indent'=>0,
        'tidy_indent_step'=>'    '
    ),
    'symbols'=>array(
        array('\\\\','<br />'),
        array('(c)','&copy;'),
        array('(C)','&copy;'),
        array('(r)','&reg;'),
        array('(R)','&reg;'),
        array('(tm)','&trade;'),
        array('(TM)','&trade;'),
        array('[TM]','&trade;'),
        array(' - ',' &ndash; '),
        array('--','&mdash;',false),
        array('...','&hellip;'),
        array('<<','&laquo;',false),
        array('>>','&raquo;',false),
        array('^o^','&deg;',false),
        array('^2','&sup2;',false),
        array('^3','&sup3;',false),
        array('1/2','&frac12;'),
        array('1/4','&frac14;'),
        array('3/4','&frac34;'),
        array(' x ','&times;'),
        array(' / ','&divide;'),
        array('+-','&plusmn;'),
        array('!=','&ne;'),
        array('<=','&le;'),
        array('>=','&ge;'),
        array('<--','&larr;'),
        array('-->','&rarr;'),
        array('<-->','&harr;'),
        array('___','&nbsp;'),
        array('£','&pound;',false)
    ),
    'inlines'=>array(
        array('``','code'),
        array('--','del'),
        array('##','span'), // <-- for e.g., highlighted text
        array('~~','del'),
        array('///','em'),
        array('\'\'\'','strong'),
        array('++','ins'),
        array('\'\'','q'),
        array('**','strong',false),
        array(',,','sub'),
        array('^^','sup',false)
    ),
    'complex'=>array(
        array('link','<a%4$s href="%1$s" title="%3$s">%2$s</a>'),
        #array('a','<a%4$s href="%1$s">%2$s</a>'),
        #array('e','<a%4$s href="mailto:%1$s">%1$s</a>'),
        #array('f','<a%4$s href="mailto:%1$s">%2$s</a>'),
        array('img','<img%4$s src="%1$s" title="%3$s" width="%5$s" height="%6$s" />',2),
        array('abbr','<abbr%4$s title="%2$s" >%1$s</abbr>'),
        array('acr','<acronym%4$s title="%2$s">%1$s</acronym>'),
        array('name','<a%4$s name="%1$s" title="%3$s">%2$s</a>'),
        #array('ia','<a%4$s href="%5$s"><img%4$s src="%1$s" alt="%2$s" title="%3$s" width="%6$s" height="%7$s /></a>',3)
    ),
    'groups'=>array(
        array('<script',/*grab all until*/'</script>'),
        array('<!--',/*grab all until*/'-->'),
        array('<style',/*grab all until*/'</style>'),
        array('{{{',/*grab all until*/'}}}',true/*evaluate*/,false),
        array('<<<',/*grab all until*/'>>>',true/*evaluate*/,true/*inline*/)
    ),
    'liners'=>array(
        array('={6}','[^=\n]+','[=]*','h6'),
        array('={5}','(?:[^=\n]|\\\\=)+','[=]*','h5'),
        array('={4}','(?:[^=\n]|\\\\=)+','[=]*','h4'),
        array('===','(?:[^=\n]|\\\\=)+','[=]*','h3'),
        array('==','(?:[^=\n]|\\\\=)+','[=]*','h2'),
        array('=','(?:[^=\n]|\\\\=)+','[=]*','h1'),
        array('-{4,}','','','hr',true/*singleton => no eval*/)
    ),
    'blocks'=>array(
        array('>>>','address',true,true/* parseInline */),
        array('"""','blockquote',true,false/* parseDocument */),
        array(':::','pre',true,true),
        array(',,,','pre',false/* passthrough */) /* Maybe these should be flags */
    )
);

function wiki_parse_string( $string, $config = false, $reset = false ){
    
    global $WIKI_DEFAULT_CONFIG;
    $wiki = new WikiParser($string, $WIKI_DEFAULT_CONFIG);
    if($config) $wiki->loadConfig($config,$reset);
    return $wiki->parseString();
}

?>