<?php
/*  $Id$ */

class PhDPartialReader extends PhDReader {
    protected $partial = array();
    protected $skip    = array();

    public function __construct($encoding = "UTF-8", $xml_opts = NULL) {
        parent::__construct($encoding, $xml_opts);
        
        $render_ids = PhDConfig::render_ids();
        if ($render_ids !== NULL) {
            if (is_array($render_ids)) {
                $this->partial = $render_ids;
            } else {
                $this->partial[$render_ids] = 1;
            }
            $skip_ids = PhDConfig::skip_ids();
            if ($skip_ids !== NULL) {
                if (is_array($skip_ids)) {
                    $this->skip = $skip_ids;
                } else {
                    $this->skip[$skip_ids] = 1;
                }
            }
        } else {
            throw new Exception("Didn't get any IDs to seek");
        }
    }

    public function read() {
        static $seeked = 0;
        static $currently_reading = false;
        static $currently_skipping = false;
        $ignore = false;

        while($ret = parent::read()) {
            if ($this->isChunk) {
                $id = $this->getAttributeNs("id", PhDReader::XMLNS_XML);
                if (isset($this->partial[$id])) {
                    if ($this->isChunk == PhDReader::CLOSE_CHUNK) {
                        v("%s done", $id, VERBOSE_PARTIAL_READING);

                        unset($this->partial[$id]);
                        --$seeked;
                        $currently_reading = false;
                    } else {
                        v("Starting %s...", $id, VERBOSE_PARTIAL_READING);

                        $currently_reading = $id;
                        ++$seeked;
                    }
                    return $ret;
                } elseif (isset($this->skip[$id])) {
                    if ($this->isChunk == PhDReader::CLOSE_CHUNK) {
                        v("%s done", $id, VERBOSE_PARTIAL_READING);

                        unset($this->skip[$id]);
                        $currently_skipping = false;
                        $ignore = false;
                    } else {
                        v("Skipping %s...", $id, VERBOSE_PARTIAL_READING);

                        $currently_skipping = $id;
                        $ignore = true;
                    }
                } elseif ($currently_skipping && $this->skip[$currently_skipping]) {
                    if ($this->isChunk == PhDReader::OPEN_CHUNK) {
                        v("Skipping child of %s, %s", $currently_reading, $id, VERBOSE_PARTIAL_CHILD_READING);
                    } else {
                        v("%s done", $id, VERBOSE_PARTIAL_CHILD_READING);
                    }

                    $ignore = true;
                } elseif ($currently_reading && $this->partial[$currently_reading]) {
                    if ($this->isChunk == PhDReader::OPEN_CHUNK) {
                        v("Rendering child of %s, %s", $currently_reading, $id, VERBOSE_PARTIAL_CHILD_READING);
                    } else {
                        v("%s done", $id, VERBOSE_PARTIAL_CHILD_READING);
                    }
                    return $ret;
                } elseif (empty($this->partial)) {
                    return false;
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

