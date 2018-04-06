<?php
/**
 * Constants file.
 * This file for define all custom constant of LP.
 * @author LVP [levanphu.info] <vanphupc50@gmail.com>
 * @copyright 2018 LP Group.
 * @since 1.0
 */
class SBException extends Exception
{
	
	 public function __toString() {
	 	$error =  "WARNING  :: $this->message".'By'. $_SERVER['HTTP_USER_AGENT'];
	 	Logs::wr($error);
	 	return $error;
    }

    // Will be set to TRUE when an exception is caught
    public static $has_error = FALSE;

    /**
     * Dual-purpose PHP error and exception handler. Uses the kohana_error_page
     * view to display the message.
     *
     * @param   integer|object  exception object or error code
     * @param   string          error message
     * @param   string          filename
     * @param   integer         line number
     * @return  void
     */
    public static function exception_handler($exception, $message = NULL, $file = NULL, $line = NULL)
    {
        try{
            // PHP errors have 5 args, always
            $PHP_ERROR = (func_num_args() === 5);

            // Test to see if errors should be displayed
            if ($PHP_ERROR AND (error_reporting() & $exception) === 0)
                return;

            // This is useful for hooks to determine if a page has an error
            self::$has_error = TRUE;

            // Error handling will use exactly 5 args, every time
            if ($PHP_ERROR)
            {
                $code     = $exception;
                $type     = 'PHP Error';
            }
            else
            {
                $code     = $exception->getCode();
                $type     = get_class($exception);
                $message  = $exception->getMessage();
                $file     = $exception->getFile();
                $line     = $exception->getLine();
            }

            // Remove the DOCROOT from the path, as a security precaution
            $file = preg_replace('|^'.preg_quote(DOCROOT).'|', '', $file);
            $arr_file = explode('\\',$file);
            $file = is_array($arr_file) ? $arr_file[count($arr_file)-1] : '';

            throw new SBException('Code: '.$code.' Type: '.$type.' Fatal Error: '.$message.' File: '.$file.' Line: '.$line);

        }catch (Exception $e){
            throw new SBException('Fatal Error: '.$e->getMessage().' File: '.$e->getFile().' Line: '.$e->getLine());
        }
    }
}
?>