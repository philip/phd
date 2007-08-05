<?php
/*  $Id$ */

class XHTMLPhDFormat extends PhDFormat {
    protected $map = array( /* {{{ */
        'article'               => 'format_container_chunk',
        'author'                => 'div',
        'authorgroup'           => 'div', /* DocBook-xsl prints out "by" (i.e. "PHP Manual by ...") */
        'appendix'              => 'format_container_chunk',
        'application'           => 'span',
        'bibliography'          => array(
            /* DEFAULT */          'div',
            'article'           => 'format_chunk',
            'book'              => 'format_chunk',
            'part'              => 'format_chunk',
        ),
        'book'                  => 'format_container_chunk',
        'chapter'               => 'format_container_chunk',
        'colophon'              => 'format_chunk',
        'firstname'             => 'span',
        'surname'               => 'span',
        'othername'             => 'span',
        'honorific'             => 'span',
        'glossary'              => array(
            /* DEFAULT */          'div',
            'article'           => 'format_chunk',
            'book'              => 'format_chunk',
            'part'              => 'format_chunk',
        ),
        'classname'             => 'span',
        'code'                  => 'code',
        'collab'                => 'span',
        'collabname'            => 'span',
        'command'               => 'span',
        'computeroutput'        => 'span',
        'constant'              => 'span',
        'emphasis'              => 'em',
        'enumname'              => 'span',
        'entry'                 => array(
            /* DEFAULT */          'format_entry',
            'row'               => array(
                /* DEFAULT */      'format_row_entry',
                'thead'         => 'format_thead_entry',
                'tfoot'         => 'format_tfoot_entry',
                'tbody'         => 'format_tbody_entry',
            ),
        ),
        'envar'                 => 'span',
        'filename'              => 'span',
        'glossterm'             => 'span',
        'holder'                => 'span',
        'index'                 => array(
            /* DEFAULT */          'div',
            'article'           => 'format_chunk',
            'book'              => 'format_chunk',
            'part'              => 'format_chunk',
        ),
        'info'                  => 'div',
        'informaltable'         => 'table',
        'itemizedlist'          => 'ul',
        'listitem'              => array(
            /* DEFAULT */          'li',
            'varlistentry'      => 'format_varlistentry_listitem',
        ),
        'literal'               => 'span',
        'mediaobject'           => 'div',
        'methodparam'           => 'span',
        'member'                => 'li',
        'note'                  => 'div',
        'option'                => 'span',    
        'orderedlist'           => 'ol',
        'para'                  => 'p',
        'parameter'             => 'tt',
        'part'                  => 'format_container_chunk',
        'partintro'             => 'div',
        'personname'            => 'span',
        'preface'               => 'format_chunk',
        'productname'           => 'span',
        'propname'              => 'span',
        'property'              => 'span',
        'proptype'              => 'span',
        'refentry'              => 'format_chunk',
        'reference'             => 'format_container_chunk',
        'sect1'                 => 'format_chunk',
        'sect2'                 => 'format_chunk',
        'sect3'                 => 'format_chunk',
        'sect4'                 => 'format_chunk',
        'sect5'                 => 'format_chunk',
        'section'               => 'format_chunk',
        'set'                   => 'format_chunk',
        'setindex'              => 'format_chunk',
        'simplelist'            => 'ul',
        'simpara'               => 'p',
        'systemitem'            => 'format_systemitem',
        'table'                 => 'format_table',
        'term'                  => 'span',
        'title'                 => array(
            /* DEFAULT */          'h1',
            'legalnotice'       => 'h4',
            'section'           => 'h2',
            'sect1'             => 'h2',
            'sect2'             => 'h3',
            'sect3'             => 'h4',
            'refsect1'          => 'h3',
            'example'           => 'h4',
            'note'              => 'h4',
        ),
        'type'                  => 'format_type',
        'userinput'             => 'format_userinput',
        'variablelist'          => 'format_variablelist',
        'varlistentry'          => 'format_varlistentry',
        'varname'               => 'var',
        'xref'                  => 'format_link',
        'year'                  => 'span',
    ); /* }}} */

    protected $CURRENT_ID  = "";
    protected $ext         = "html";
    
    public function __construct(PhDReader $reader, array $IDs, array $IDMap, $ext = "html") {
        parent::__construct($reader, $IDs, $IDMap, $ext);
    }
    /* Overwrite PhDFormat::readContent() to convert special HTML chars */
    public function readContent($content = null) {
        return htmlspecialchars(PhDFormat::readContent($content), ENT_QUOTES, "UTF-8");
    }
    public function __call($func, $args) {
        if ($args[0]) {
            trigger_error("No mapper found for '{$func}'", E_USER_WARNING);
            return "<font color='red' size='+3'>{$args[1]}</font>";
        }
        return "<font color='red' size='+3'>/{$args[1]}</font>";
    }
    public function transformFromMap($open, $tag, $name) {
        if ($open) {
            return sprintf('<%s class="%s">', $tag, $name);
        }
        return "</$tag>";
    }
    public function CDATA($str) {
        return sprintf('<div class="phpcode">%s</div>', highlight_string($str, 1));
    }

    public function format_container_chunk($open, $name) {
        $this->CURRENT_ID = $id = PhDFormat::getID();
        if ($open) {
            return sprintf('<div id="%s" class="%s">', $id, $name);
        }
        return "</div>";
    }
    public function format_chunk($open, $name) {
        $this->CURRENT_ID = $id = PhDFormat::getID();
        if ($open) {
            return sprintf('<div id="%s" class="%s">', $id, $name);
        }
        return "</div>";
    }
    public function format_function($open, $name) {
        return sprintf('<a href="function.%s.html">%1$s</a>', $this->readContent());
    }
    public function format_refsect1($open, $name) {
        if ($open) {
            return sprintf('<div class="refsect %s">', PhDFormat::readAttribute("role"));
        }
        return "</div>\n";
    }
    public function format_link($open, $name) {
        $content = $fragment = "";
        $class = $name;
        if($linkto = PhDFormat::readAttribute("linkend")) {
            $id = $href = PhDFormat::getFilename($linkto);
            if ($id != $linkto) {
                $fragment = "#$linkto";
            }
            $href .= ".".$this->ext;
        } elseif($href = PhDFormat::readAttributeNs("href", PhDReader::XMLNS_XLINK)) {
            $content = "&raquo; ";
            $class .= " external";
        }
        $content .= $name == "xref" ? PhDFormat::getDescription($id, false) : $this->readContent($name);
        return sprintf('<a href="%s%s" class="%s">%s</a>', $href, $fragment, $class, $content);
    }
    public function format_methodsynopsis($open, $root) {
        /* We read this element to END_ELEMENT so $open is useless */
        $content = '<div class="methodsynopsis">';

        while($child = PhDFormat::getNextChild($root)) {
            if ($child["type"] == XMLReader::END_ELEMENT) {
                $content .= "</span>\n";
                continue;
            }
            $name = $child["name"];
            switch($name) {
            case "type":
            case "parameter":
            case "methodname":
                $content .= sprintf('<span class="%s">%s</span>', $name, $this->readContent($name));
                break;

            case "methodparam":
                $content .= '<span class="methodparam">';
                break;
            }
        }
        $content .= "</div>";
        return $content;
    }
    public function format_refnamediv($open, $root) {
        while ($child = PhDFormat::getNextChild($root)) {
            $name = $child["name"];
            switch($name) {
            case "refname":
                $refname = $this->readContent($name);
                break;
            case "refpurpose":
                $refpurpose = $this->readContent($name);
                break;
            }
        }
        
        return sprintf('<div class="refnamediv"><span class="refname">%s</span><span class="refpurpose">%s</span></div>', $refname, $refpurpose);
    }
    public function format_variablelist($open, $name) {
        if ($open) {
            return "<dl>\n";
        }
        return "</dl>\n";
    }
    public function format_varlistentry($open, $name) {
        if ($open) {
            $id = PhDFormat::getID();
            if ($id) {
                return sprintf('<dt id="%s">', $id);
            }
            return "<dt>\n";
        }
        return "</dt>\n";
    }
    public function format_varlistentry_listitem($open, $name) {
        if ($open) {
            return "<dd>\n";
        }
        return "</dd>\n";
    }
    public function format_userinput($open, $name) {
        if ($open) {
            return sprintf('<strong class="%s"><code>', $name);
        }
        return "</code></strong>\n";
    }
    public function format_systemitem($open, $name) {
        if ($open) {
            switch($this->readAttribute("role")) {
            case "directive":
            /* FIXME: Different roles should probably be handled differently */
            default:
                return sprintf('<code class="systemitem %s">', $name);
            }
        }
        return "</code>\n";
    }
    public function format_type($open, $name) {
        $type = $this->readContent($name);
        $t = strtolower($type);
        $href = $fragment = "";

        switch($t) {
        case "bool":
            $href = "language.types.boolean";
            break;
        case "int":
            $href = "language.types.integer";
            break;
        case "double":
            $href = "language.types.float";
            break;
        case "boolean":
        case "integer":
        case "float":
        case "string":
        case "array":
        case "object":
        case "resource":
        case "null":
            $href = "language.types.$t";
            break;
        case "mixed":
        case "number":
        case "callback":
            $href = "language.pseudo-types";
            $fragment = "language.types.$t";
            break;
        }
        if ($href) {
            return sprintf('<a href="%s.%s%s" class="%s %s">%5$s</a>', $href, $this->ext, ($fragment ? "#$fragment" : ""), $name, $type);
        }
        return sprintf('<span class="%s %s">%2$s</span>', $name, $type);
    }


    /* TODO: Move the logic to PhDFormat? */
    /* NOTE: This is not a full implementation, just a proof-of-concept */
    public function format_table($open, $name) {
        if ($open) {
            return '<table border="5">';
        }
        return "</table>\n";
    }
    public function format_tgroup($open, $name) {
        if ($open) {
            $this->TABLE_COLS = $this->readAttribute("cols");
            $this->COLSPEC = array();
            return "<colgroup>\n";
        }
        return "</colgroup>\n";
    }
    public function format_colspec($open, $name) {
        if ($open) {
            $colname = $this->readAttribute("colname");
            if ($colnum = $this->readAttribute("colnum")) {
                $this->COLSPEC[$colnum] = $colname;
            } else {
                $count = count($this->COLSPEC);
                $this->COLSPEC[$count] = $colname;
            }
            return "<col />"; // Probably throw in couple of width and align attributes
        }
        /* noop */
    }
    public function format_thead($open, $name) {
        if ($open) {
            return "<thead>";
        }
        return "</thread>\n";
    }
    public function format_tbody($open, $name) {
        if ($open) {
            return "<tbody>";
        }
        return "</tbody>";
    }
    public function format_row($open, $name) {
        if ($open) {
            return "<tr>\n";
        }
        return "</tr>\n";
    }
    public function format_thead_entry($open, $name) {
        if ($open) {
            if ($start = $this->readAttribute("namest")) {
                $from = array_search($start, $this->COLSPEC);
                $end = $this->readAttribute("nameend");
                $to = array_search($end, $this->COLSPEC);
                return sprintf('<th colspan="%d">', $end-$to);
            }
            return '<th>';
        }
        return '</th>';
    }
    public function format_tfoot_entry($open, $name) {
        return $this->format_thead_entry($open, $name);
    }
    public function format_tbody_entry($open, $name) {
        if ($open) {
            $colspan = 1;
            $rows = 1;
            if ($start = $this->readAttribute("namest")) {
                $from = array_search($start, $this->COLSPEC);
                $end = $this->readAttribute("nameend");
                $to = array_search($end, $this->COLSPEC);
                $colspan = $to-$from+1;
            }
            if ($morerows = $this->readAttribute("morerows")) {
                $rows += $morerows;
            }
            return sprintf('<td colspan="%d" rowspan="%d">', $colspan, $rows);
        }
        return "</td>";
    }

}

/*
* vim600: sw=4 ts=4 fdm=syntax syntax=php et
* vim<600: sw=4 ts=4
*/

