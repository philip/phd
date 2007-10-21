<?php
/*  $Id$ */

class PhDPartialReader extends PhDReader {
    protected $partial = array();

    public function __construct($opts, $encoding = "UTF-8", $xml_opts = NULL) {
        parent::__construct($opts, $encoding, $xml_opts);

        if (isset($opts["render_ids"])) {
            if (is_array($opts["render_ids"])) {
                $this->partial = $opts["render_ids"];
            } else {
                $this->partial[$opts["render_ids"]] = 1;
            }
        } else {
            throw new Exception("Didn't get any IDs to seek");
        }
    }

    public function read() {
        static $seeked = 0;
        static $currently_reading = false;
        $ignore = false;

        while($ret = parent::read()) {
            if ($this->isChunk) {
                $id = $this->getAttributeNs("id", PhDReader::XMLNS_XML);
                if (isset($this->partial[$id])) {
                    if ($this->isChunk == PhDReader::CLOSE_CHUNK) {
                        unset($this->partial[$id]);
                        --$seeked;
                        $currently_reading = false;
                    } else {
                        $currently_reading = $id;
                        ++$seeked;
                    }
                    return $ret;
                } elseif ($currently_reading && $this->partial[$currently_reading]) {
                    return $ret;
                } else {
                    $ignore = true;
                }
            } elseif (!$ignore && $seeked > 0) {
                return $ret;
            }
        }
        return $ret;
    }
}

/*
* vim600: sw=4 ts=4 fdm=syntax syntax=php et
* vim<600: sw=4 ts=4
*/

