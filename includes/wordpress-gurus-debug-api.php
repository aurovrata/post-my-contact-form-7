<?php
/**
* Error logging and notices
* @since 1.0.0
* @var string $message to log if in debug mode
*/
defined('WP_GURUS_DEBUG') || define('WP_GURUS_DEBUG', false);

if( !function_exists('debug_msg') ){
  if (true === WP_GURUS_DEBUG) {
     $debug_msg_last_line='';
     $debug_msg_last_file='';
   }
   /**
   * Function to log debug messages.
   * Requires WP_GURUS_DEBUG = true, define it in your wp-config.php file.
   * Set WP_DEBUG to true, and WP_DEBUG_DISPLAY to false (so no errors are printed to screen), and WP_DEBUG_LOG to true so messages are logged to wp-content/debug.log file.
   */
  function debug_msg($message, $prefix='', $trace=0) {
    if (true === WP_GURUS_DEBUG) {
      global $debug_msg_last_line,$debug_msg_last_file;
      $backtrace = debug_backtrace();
      $file = $backtrace[0]['file'];
      $line = $backtrace[0]['line'];
      $files = explode('/',$file);
      $dirs = explode('/',plugin_dir_path( __FILE__ ));
      $files = array_diff($files,$dirs);
      $file = implode('/',$files);
      $msg='DEBUG_MSG:'.($trace?' --------------- ':'');
      if (is_array($message) || is_object($message)) {
          $msg.=$prefix.print_r($message, true);
      } else {
          $msg.=$prefix.$message;
      }
      if(true===$trace || ($file != $debug_msg_last_file && $line != $debug_msg_last_line)){
        if($trace===true){
          $trace = sizeof($backtrace);
          $msg.=PHP_EOL;
        }
        for($idx=($trace-1); $idx>0; $idx--){
          $msg.='['.$backtrace[$idx]['line'].']->/'.$backtrace[$idx]['file'].PHP_EOL;
        }
        $msg.= ($trace?'':PHP_EOL)."/$file:$line";
        $debug_msg_last_file=$file;
        $debug_msg_last_line=$line;
        if($trace) $msg.=PHP_EOL.'-----------------------------------------------------';
      }
      error_log($msg);
    }
  }
} ?>
