<?

function dm_config_create(){
    
    $config = array(
        'groups' => array(),
        'blocks' => array(),
        'lines'=>array(),
        'lists'=>array(),
        'symbols'=>array(),
        'tags'=>array(),
        'complex'=>array()
    );
    $configs = func_get_args();
    foreach($configs as $each) {
        foreach($config as $key=>$v) {
            if (isset($each[$key])) {
                $config[$key] = array_merge($config[$key], (array)$each[$key]);
            }
        }
    }
    return $config;
}

$DM_CONFIG_BASIC = array(
    'groups' => array(
        array('{{{','}}}','protect'),
        array('[[[',']]]','block'),
        array('<<<','>>>','inline')
    ),
    'blocks' => array(
        array('>>>','address'),
        array('"""','blockquote'),
        array(':::','pre')
    ),
    'lines'=>array(
        array('======','h6'),
        array('=====','h5'),
        array('====','h4'),
        array('===','h3'),
        array('==','h2'),
        array('=','h1'),
        array('----','hr',true),
    ),
    'lists'=>array(
        array('*','ul'),
        array('#','ol','decimal')
    ),
    'symbols'=>array(
        array('&(?!\#\d+;|[a-zA-Z0-9]+;)','&amp;',true),
        array('<','&lt;'),
        array('£','&pound;'),
        array('\\\\','<br />')
    ),
    'tags'=>array(
        array('``','code'),
        array('##','span'),
        array('\'\'','em'),
        array('**','strong')
    ),
    'complex'=>array(
        array('link','<a%4$s href="%1$s" title="%3$s">%2$s</a>'),
        array('mailto','<a%4$s href="mailto:%1$s" title="%3$s">%2$s</a>'),
        array('img','<img%4$s src="%1$s" title="%3$s" width="%5$s" height="%6$s" />'),
        array('abbr','<abbr%4$s title="%3$s" >%2$s</abbr>'),
        array('acr','<acronym%4$s title="%3$s">%2$s</acronym>')
    )
);



$DM_CONFIG_EXTRA = array(
    'groups' => array(
        array('<script','</script>','protectall'),
        array('<style','</style>','protectall'),
        array('<!--','-->','remove')
    ),
    'blocks' => array(
        array('```','pre')
    ),
    'lines'=>array(
    ),
    'lists'=>array(
        array('1','ol','decimal'),
        array('+','ul'),
        array('-','ul'),
        array('>','ul'),
        array('.','ul'),
        array('~','ul'),
        array('o','ul','circle'),
        array('!','ul','important'),
        array('?','ul','question'),
        array('i','ol','lower-roman'),
        array('I','ol','upper-roman'),
        array('a','ol','lower-alpha'),
        array('A','ol','upper-alpha'),
        array('y','ol','lower-greek'),
        array('Y','ol','upper-greek')
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
        array('--','del'),
        array('~~','del'),
        array('++','ins'),
        array('""','q'),
        array(',,','sub'),
        array('__','sub'),
        array('^^','sup')
    ),
    'complex'=>array(
        array('name','<a%4$s name="%1$s" title="%3$s">%2$s</a>')
    )
);



$DM_CONFIG_FORMS = array(
    'groups' => array(
    ),
    'blocks' => array(
    ),
    'lines'=>array(
        array('+++','legend')
    ),
    'lists'=>array(
    ),
    'symbols'=>array(
    ),
    'tags'=>array(
    ),
    'complex'=>array(
        array('text','<label for="%1$s">%2$s</label><input%4$s type="text" name="%1$s" id="%1$s" placeholder="%3$s" />'),
        array('password','<label for="%1$s">%2$s</label><input%4$s type="password" name="%1$s" id="%1$s" />'),
        array('checkbox','<input%4$s type="checkbox" name="%1$s" id="%1$s_%5$s" value="%5$s" /><label title="%3$s" for="%1$s_%5$s">%2$s</label>'),
        array('radio','<input%4$s type="radio" name="%1$s" id="%1$s_%5$s" value="%5$s" /><label title="%3$s" for="%1$s_%5$s">%2$s</label>'),
        array('textarea','<label for="%1$s">%2$s</label><textarea%4$s name="%1$s" id="%1$s">%3$s</textarea>'),
        array('file','<label for="%1$s">%2$s</label><input%4$s type="file" name="%1$s" id="%1$s" />'),
        array('submit','<input%4$s type="submit" value="%2$s" />'),
        array('reset','<input%4$s type="reset" value="%2$s" />'),
        array('button','<input%4$s type="button" value="%2$s" />'),
        array('form','<form%4$s action="%1$s" method="%5$s"><fieldset><legend>%2$s</legend>')
    )
);



$DM_CONFIG_OBJECTS = array(
    'groups' => array(
    ),
    'blocks' => array(
    ),
    'lines'=>array(
    ),
    'lists'=>array(
    ),
    'symbols'=>array(
    ),
    'tags'=>array(
    ),
    'complex'=>array(
        array('youtube','<iframe width="560" height="315" src="http://www.youtube.com/embed/%1$s" frameborder="0" allowfullscreen></iframe>')
    )
);



$DM_CONFIG_ALL = array(
    'groups' => array(
        array('{{{','}}}','protect'),
        array('<script','</script>','protectall'),
        array('<style','</style>','protectall'),
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
        array('#','ol','decimal'),
        array('1','ol','decimal'),
        array('+','ul'),
        array('-','ul'),
        array('>','ul'),
        array('.','ul'),
        array('~','ul'),
        array('o','ul','circle'),
        array('o','ul','circle'),
        array('!','ul','important'),
        array('?','ul','question'),
        array('i','ol','lower-roman'),
        array('I','ol','upper-roman'),
        array('a','ol','lower-alpha'),
        array('A','ol','upper-alpha'),
        array('y','ol','lower-greek'),
        array('Y','ol','upper-greek')
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

?>
