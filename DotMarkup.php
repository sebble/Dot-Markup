<?php

class DotMarkup {

    var $source;
    var $filename;
    
    const NEWLINE = '(?<=^|\n)';
    
    //// User Functions ////
    
    function loadFile($filename) {
    
        $this->filename = basename($filename);
        $this->source = file_get_contents($filename);
        $this->_prepareSource();
    }
    function loadString($source) {
    
        $this->filename = '';
        $this->source = $filename;
        $this->_prepareSource();
    }
    
    function parseDocument() {
    
        // generate doc outline
        $lines = explode("\n", $this->source);
        
        $previous = array(
            'block'=>'none'
        );
        $overview = array();
        foreach ($lines as $no => $line) {
            
            
        }
    }
    
    //// Utility Functions ////
    
    function _prepareSource() {
    
        ## fix newlines (no need if using preg_split)
        
    }
};

$Doc = new DotMarkup;
$Doc->loadFile('sample.md');
$Doc->parseDocument();

#var_dump($Doc);
print_r($Doc->lines);
