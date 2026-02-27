<?php 
class My_log extends CI_Log  {

    function My_log ()
    {
        parent::__construct();

        $this->ci =& get_instance();
    }

    public function write_log($level, $msg) { //here overriding
        if ($this->_enabled === FALSE)
        {
        return FALSE;
        }

        $level = strtoupper($level);

        if ( ! isset($this->_levels[$level]) OR
        ($this->_levels[$level] > $this->_threshold))
        {
        return FALSE;
        }

        /* HERE YOUR LOG FILENAME YOU CAN CHANGE ITS NAME */
        $filepath = $this->_log_path.'testing-'.date('Y-m-d').EXT;
        $message  = '';

        if ( ! file_exists($filepath))
        {
        $message .= "<"."?php  if ( ! defined('BASEPATH'))
        exit('No direct script access allowed'); ?".">\n\n";
        }

        if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
        {
        return FALSE;
        }

        $message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' ';
        $message .= date($this->_date_fmt). ' --> '.$msg."\n";

        flock($fp, LOCK_EX);
        fwrite($fp, $message);
        flock($fp, LOCK_UN);
        fclose($fp);

        @chmod($filepath, FILE_WRITE_MODE);
        return TRUE;
    }
}