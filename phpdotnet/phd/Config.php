<?php
/* Generated by PhD $Id$ at Thu, 09 Aug 2007 15:10:10 +0200. */

$OPTIONS = array (
  'output_format' => array('xhtml'),
  'output_theme' => array(
    'xhtml' => array(
      'php' => array(
        'phpweb',
        'chunkedhtml',
        'bightml',
      ),
    ),
  ),
  'index' => true,
  'xml_root' => '/home/bjori/php/doc',
  'language' => 'en',
  'fallback_language' => 'en',
  'enforce_revisions' => false,
  'compatibility_mode' => true,
  'build_log_file' => 'none',
  'debug' => true,
);

if ($argc == 2) {
    $OPTIONS["xml_root"] = $argv[1];
}

while (!is_dir($OPTIONS["xml_root"]) || !is_file($OPTIONS["xml_root"] . "/.manual.xml")) {
    print "I need to know where you keep your '.manual.xml' file (I didn't find it in " . $OPTIONS["xml_root"] . "): ";
    $root = trim(fgets(STDIN));
    if (is_file($root)) {
        $OPTIONS["xml_root"] = dirname($root);
    } else {
        $OPTIONS["xml_root"] = $root;
    }
}

