<?php

require 'configs.php';
require 'DotMarkup.php';

$Doc = new DotMarkup;
$Doc->config = dm_config_create($DM_CONFIG_BASIC, $DM_CONFIG_EXTRA, $DM_CONFIG_FORMS);
$Doc->parseFile('test.dm');
echo $Doc->html;
$Doc->parseFile('sample.dm');
echo $Doc->html;
