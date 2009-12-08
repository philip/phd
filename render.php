#!@php_bin@
<?php
namespace phpdotnet\phd;
/* $Id$ */

// @php_dir@ gets replaced by pear with the install dir. use __DIR__ when 
// running from SVN
define("__INSTALLDIR__", "@php_dir@" == "@"."php_dir@" ? __DIR__ : "@php_dir@");

function autoload($name)
{
    $file = __INSTALLDIR__ . DIRECTORY_SEPARATOR . str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $name) . '.php';
    // Using fopen() because it has use_include_path parameter.
    if (!$fp = @fopen($file, 'r', true)) {
        v('Cannot find file for %s: %s', $name, $file, E_USER_ERROR);
    }
    fclose($fp);
    require $file;
}
spl_autoload_register(__NAMESPACE__ . '\\autoload');
require_once __INSTALLDIR__ . '/phpdotnet/phd/functions.php';

BuildOptionsParser::getopt();

/* If no docbook file was passed, die */
if (!is_dir(Config::xml_root()) || !is_file(Config::xml_file())) {
    trigger_error("No Docbook file given. Specify it on the command line with --docbook.", E_USER_ERROR);
}
if (!file_exists(Config::output_dir())) {
    v("Creating output directory..", E_USER_NOTICE);
    if (!mkdir(Config::output_dir())) {
        v("Can't create output directory", E_USER_ERROR);
    }
} elseif (!is_dir(Config::output_dir())) {
    v("Output directory is not a file?", E_USER_ERROR);
}

Config::init(array(
    "lang_dir"  => __INSTALLDIR__ . DIRECTORY_SEPARATOR . "phpdotnet" . DIRECTORY_SEPARATOR
                    . "phd" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR
                    . "langs" . DIRECTORY_SEPARATOR,
    "phpweb_version_filename" => Config::xml_root() . DIRECTORY_SEPARATOR . 'version.xml',
    "phpweb_acronym_filename" => Config::xml_root() . DIRECTORY_SEPARATOR . 'entities' . DIRECTORY_SEPARATOR . 'acronyms.xml',
));

$render = new Render();
$reader = new Reader();

// Indexing
if (Index::requireIndexing()) {
    v("Indexing...", VERBOSE_INDEXING);
    // Create indexer
    $format = $render->attach(new Index);

    $reader->open(Config::xml_file());
    $render->execute($reader);

    $render->detach($format);

    v("Indexing done", VERBOSE_INDEXING);
} else {
    v("Skipping indexing", VERBOSE_INDEXING);
}

//Partial Rendering
$idlist = Config::render_ids() + Config::skip_ids();
if (!empty($idlist)) {
    v("Running partial build", VERBOSE_RENDER_STYLE);
    $reader = new Reader_Partial();
} else {
    v("Running full build", VERBOSE_RENDER_STYLE);
}

foreach((array)Config::package() as $package) {
    $factory = Format_Factory::createFactory($package);

    // Default to all output formats specified by the package
    if (count(Config::output_format()) == 0) {
        Config::set_output_format((array)$factory->getOutputFormats());
    }
 
    // Register the formats
    foreach (Config::output_format() as $format) {
        $render->attach($factory->createFormat($format));
    }
}

// Render formats
$reader->open(Config::xml_file());
foreach($render as $format) {
    $format->notify(Render::VERBOSE, true);
}
$render->execute($reader);

v("Finished rendering", VERBOSE_FORMAT_RENDERING);


/*
* vim600: sw=4 ts=4 syntax=php et
* vim<600: sw=4 ts=4
*/

