<?php
/*
+------------------------------------------------------------------------------+
| LiteCommerce                                                                 |
| Copyright (c) 2003-2009 Creative Development <info@creativedevelopment.biz>  |
| All rights reserved.                                                         |
+------------------------------------------------------------------------------+
| PLEASE READ  THE FULL TEXT OF SOFTWARE LICENSE AGREEMENT IN THE  "COPYRIGHT" |
| FILE PROVIDED WITH THIS DISTRIBUTION.  THE AGREEMENT TEXT  IS ALSO AVAILABLE |
| AT THE FOLLOWING URL:                                                        |
| http://www.litecommerce.com/software_license_agreement.html                  |
|                                                                              |
| THIS  AGREEMENT EXPRESSES THE TERMS AND CONDITIONS ON WHICH YOU MAY USE THIS |
| SOFTWARE PROGRAM AND ASSOCIATED DOCUMENTATION THAT CREATIVE DEVELOPMENT, LLC |
| REGISTERED IN ULYANOVSK, RUSSIAN FEDERATION (hereinafter referred to as "THE |
| AUTHOR")  IS  FURNISHING  OR MAKING AVAILABLE TO  YOU  WITH  THIS  AGREEMENT |
| (COLLECTIVELY,  THE "SOFTWARE"). PLEASE REVIEW THE TERMS AND  CONDITIONS  OF |
| THIS LICENSE AGREEMENT CAREFULLY BEFORE INSTALLING OR USING THE SOFTWARE. BY |
| INSTALLING,  COPYING OR OTHERWISE USING THE SOFTWARE, YOU AND  YOUR  COMPANY |
| (COLLECTIVELY,  "YOU")  ARE ACCEPTING AND AGREEING  TO  THE  TERMS  OF  THIS |
| LICENSE AGREEMENT. IF YOU ARE NOT WILLING TO BE BOUND BY THIS AGREEMENT,  DO |
| NOT  INSTALL  OR USE THE SOFTWARE. VARIOUS COPYRIGHTS AND OTHER INTELLECTUAL |
| PROPERTY  RIGHTS PROTECT THE SOFTWARE. THIS AGREEMENT IS A LICENSE AGREEMENT |
| THAT  GIVES YOU LIMITED RIGHTS TO USE THE SOFTWARE AND NOT AN AGREEMENT  FOR |
| SALE  OR  FOR TRANSFER OF TITLE. THE AUTHOR RETAINS ALL RIGHTS NOT EXPRESSLY |
| GRANTED  BY  THIS AGREEMENT.                                                 |
|                                                                              |
| The Initial Developer of the Original Code is Ruslan R. Fazliev              |
| Portions created by Ruslan R. Fazliev are Copyright (C) 2003 Creative        |
| Development. All Rights Reserved.                                            |
+------------------------------------------------------------------------------+
*
* $Id$
*
*/
/* vim: set expandtab tabstop=4 softtabstop=4 foldmethod=marker shiftwidth=4: */

function func_is_array_unique($arr, &$firstValue, $skipValue="") {
    if (!is_array($arr)) {
    	return false;
    }
    for ($i = 0; $i < count($arr); $i++) {
        if (strcmp($arr[$i], $skipValue) === 0) { 
        	continue; 
        }
           
        for ($j = 0; $j < count($arr); $j++) {
            if ($i != $j && strcmp($arr[$i], $arr[$j]) === 0) {
                $firstValue = $arr[$i];
                return false;
            }
        }
    }
        
    return true;
}

/**
* Flush output
*/
function func_flush() { // {{{
	if (preg_match("/Apache(.*)Win/", getenv("SERVER_SOFTWARE")))
		echo str_repeat(" ", 2500);
	flush();
} // }}}

/**
* Prints Javascript code to refresh the browser output page.
*/
function func_refresh_start() { // {{{
    $code =<<<EOT
<script language="javascript">
var loaded = false;

function refresh() {
    window.scroll(0, 10000000);

    if (loaded == false) {
        setTimeout('refresh()', 500);
    }    
}

setTimeout('refresh()', 1000);
</script>
EOT;
    print $code;
} // }}}

function func_refresh_end() {
    print <<<EOT
<SCRIPT language="javascript">
    loaded = true;
</SCRIPT>
EOT;
}
/*
* Executable lookup
* Return false if not executable.
*/
function func_find_executable($filename) { // {{{
    $directories = explode(PATH_SEPARATOR, getenv("PATH"));
	array_unshift($directories, "./bin", "/usr/bin", "/usr/local/bin");

    foreach($directories as $dir) {
        $file = $dir.'/'.$filename;
        if( func_is_executable($file) ) return @realpath($file);
        $file .= ".exe";
        if( func_is_executable($file) ) return @realpath($file);
    }
    return false;
} // }}}

/*
* Emulator for the is_executable function if it doesn't exists (f.e. under windows)
*/
function func_is_executable($file) { // {{{
    if(@function_exists("is_executable")) return @is_executable($file);
    return @is_file ($file) && @is_readable($file);
} // }}}

/**
* shutdown function
*/
function shutdown() { // {{{
    exit();
} // }}}

/**
* custom error handler function
*/
function errorHandler($errno, $errmsg, $filename, $linenum, $context) { // {{{
    $errors = array(E_ERROR);
    if (!in_array($errno, $errors)) {
        return;
    }
    switch ($errno) {
        case E_ERROR:
        case E_USER_ERROR:
            echo "<b>FATAL</b> [$errno] $errmsg<br>\n";
            echo "  Fatal error in line ".$linenum." of file ".$filename;
            echo ", PHP ".PHP_VERSION." (".PHP_OS.")<br>\n";
            if (function_exists("debug_backtrace")) {
                echo "Debug backtrace:<br>\n";
                echo "<pre>";
                print_r(debug_backtrace());
                echo "</pre>\n";
            }
            exit(1);
            break;
        case E_WARNING:
        case E_USER_WARNING:
            echo "<b>WARNING</b> [$errno] $errmsg<br>\n";
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            echo "<b>NOTICE</b> [$errno] $errmsg<br>\n";
            break;
        default:
            echo "Unkown error type: [$errno] $errmsg<br>\n";
            break;
    }
} // }}}

function func_define($name, $value) { // {{{
    if (!defined($name)) {
        define($name, $value);
    }
} // }}}

function getmicrotime() { // {{{
    list($usec, $sec) = explode(" ",microtime()); 
    return ((float)$usec + (float)$sec);
} // }}}

// define actual permissions
// mode - one of 0777, 0755, 0666, 0644
function get_filesystem_permissions($mode, $file = null) {
    static $mode0777, $mode0755, $mode0666, $mode0644, $mode0666_fnp, $mode0644_fnp;
 
    // try to setup values from config
    if(!isset($mode0777) || !isset($mode0755) || !isset($mode0666) || !isset($mode0644) ) {
    	global $options;        
        // 0777  ---------------------------------------------
        if (!isset($mode0777)) {
            if (get_php_execution_mode() != 0) {
                if (isset($options['filesystem_permissions']) && isset($options['filesystem_permissions']['privileged_permission_dir'])) {
                    $mode0777 = base_convert($options['filesystem_permissions']['privileged_permission_dir'], 8, 10);
                }
            } else {
                if (isset($options['filesystem_permissions']) && isset($options['filesystem_permissions']['nonprivileged_permission_dir_all'])) {
                    $mode0777 = base_convert($options['filesystem_permissions']['nonprivileged_permission_dir_all'], 8, 10);
                }
            }
        }
        // 0755  ---------------------------------------------
        if (!isset($mode0755)) {
            if (get_php_execution_mode() != 0) {
                if (isset($options['filesystem_permissions']) && isset($options['filesystem_permissions']['privileged_permission_dir'])) {
                    $mode0755 = base_convert($options['filesystem_permissions']['privileged_permission_dir'], 8, 10);
                }
            } else {
                if (isset($options['filesystem_permissions']) && isset($options['filesystem_permissions']['nonprivileged_permission_dir'])) {
                    $mode0755 = base_convert($options['filesystem_permissions']['nonprivileged_permission_dir'], 8, 10);
                }
            }
        }
        // 0666  ---------------------------------------------
        if (!isset($mode0666)) {
            if (get_php_execution_mode() != 0) {
                if (isset($options['filesystem_permissions']) && isset($options['filesystem_permissions']['privileged_permission_file'])) {
                    $mode0666 = base_convert($options['filesystem_permissions']['privileged_permission_file'], 8, 10);
                    if (isset($options['filesystem_permissions']) && isset($options['filesystem_permissions']['privileged_permission_file_nonphp'])) {
                        $mode0666_fnp = base_convert($options['filesystem_permissions']['privileged_permission_file_nonphp'], 8, 10);
                    } else {
                    	$mode0666_fnp = $mode0666;
                    }
                }
            } else {
                if (isset($options['filesystem_permissions']) && isset($options['filesystem_permissions']['nonprivileged_permission_file_all'])) {
                    $mode0666 = base_convert($options['filesystem_permissions']['nonprivileged_permission_file_all'], 8, 10);
					$mode0666_fnp = $mode0666;
                }
            }
        }
        // 0644  ---------------------------------------------
        if (!isset($mode0644)) {
            if (get_php_execution_mode() != 0) {
                if (isset($options['filesystem_permissions']) && isset($options['filesystem_permissions']['privileged_permission_file'])) {
                    $mode0644 = base_convert($options['filesystem_permissions']['privileged_permission_file'], 8, 10);
                    if (isset($options['filesystem_permissions']) && isset($options['filesystem_permissions']['privileged_permission_file_nonphp'])) {
                        $mode0644_fnp = base_convert($options['filesystem_permissions']['privileged_permission_file_nonphp'], 8, 10);
                    } else {
                    	$mode0644_fnp = $mode0644;
                    }
                }
            } else {
                if (isset($options['filesystem_permissions']) && isset($options['filesystem_permissions']['nonprivileged_permission_file'])) {
                    $mode0644 = base_convert($options['filesystem_permissions']['nonprivileged_permission_file'], 8, 10);
					$mode0644_fnp = $mode0644;
                }
            }
        }
    }
    
    if (($mode == 0777) && (isset($mode0777))) {
        return $mode0777;
    }
    
    if (($mode == 0755) && (isset($mode0755))) {
        return $mode0755;
    }
    
    if (($mode == 0666) && (isset($mode0666))) {
    	if (isset($file) && @is_file($file)) {
    		$path_parts = @pathinfo($file);
    		if (strtolower($path_parts["extension"]) == "php") {
        		return $mode0666;
    		} else {
        		return $mode0666_fnp;
    		}
    	} else {
        	return $mode0666;
        }
    }
    
    if (($mode == 0644) && (isset($mode0644))) {
    	if (isset($file) && @is_file($file)) {
    		$path_parts = @pathinfo($file);
    		if (strtolower($path_parts["extension"]) == "php") {
        		return $mode0644;
    		} else {
        		return $mode0644_fnp;
    		}
    	} else {
        	return $mode0644;
        }
    }
    
    // default return
    return $mode;
}


// copy single file and set permissions
function copyFile($from, $to, $mode=0666) {
    
	if ($mode == 0666) {
		$mode = get_filesystem_permissions(0666, $from);
	} elseif ($mode == 0644) {
		$mode = get_filesystem_permissions(0644, $from);
	}
    
    $result = false;
    
    if (@is_file($from)) {
        $result = @copy($from, $to);
        if (!$result) {
			mkdirRecursive(dirname($to));
        	$result = @copy($from, $to);
        }
        @umask(0000);
        $result = $result && @chmod($to, $mode);
    }
    
    return $result;
}

function copyRecursive($from, $to, $mode = 0666, $dir_mode = 0777) { // {{{
	$orig_dir_mode = $dir_mode;
	if ($dir_mode == 0777) {
		$dir_mode = get_filesystem_permissions(0777);
	} elseif ($dir_mode == 0755) {
		$dir_mode = get_filesystem_permissions(0755);
	}
	$orig_mode = $mode;
	if ($mode == 0666) {
		$mode = get_filesystem_permissions(0666, $from);
	} elseif ($mode == 0644) {
		$mode = get_filesystem_permissions(0644, $from);
	}

    if (@is_file($from)) {
        @copy($from, $to);
        @umask(0000);
        @chmod($to, $mode);
        return;
    } else if (@is_dir($from)) {
        if (!@file_exists($to)) {
            @umask(0000);
            $attempts = 5;
            while (!@mkdir($to, $dir_mode)) {
                @unlinkRecursive($to);
                $attempts --; 
                if ($attempts < 0) {
                    if($_REQUEST['target'] == "wysiwyg") {
                        echo "<font color='red'>Warning: Can't create directory $to: permission denied</font>";
                        echo '<br /><br /><a href="admin.php?target=wysiwyg">Click to return to admin interface</a>';
                    } else {
                        echo "Can't create directory $to: permission denied";
                    }
                    die;
                }
            }
        }
        if ($handle = @opendir($from)) {
            while (false !== ($file = @readdir($handle))) {
                if (!($file == "." || $file == "..")) {
                    copyRecursive($from . '/' . $file, $to . '/' . $file, $orig_mode, $orig_dir_mode);
                }
            }
            @closedir($handle);
        }
    } else {
        return 1;
    }
} // }}}

function mkdirRecursive($dir, $mode = 0777) { // {{{
	if ($mode == 0777) {
		$mode = get_filesystem_permissions(0777);
	} elseif ($mode == 0755) {
		$mode = get_filesystem_permissions(0755);
	}

	$dirstack = array();
    while (!@is_dir($dir) && $dir != '/') {
		array_unshift($dirstack, $dir);
		$dir = @dirname($dir);
	}
	$ret = true;
	while ($newdir = array_shift($dirstack)) {
        if (substr($newdir, -2) == "..") continue;
        @umask(0000);
        $attempts = 5;
        while (!@mkdir($newdir, $mode)) {
            @unlinkRecursive($newdir);
            $attempts --; 
            if ($attempts < 0) {
                die("Can't create directory $newdir: permission denied");
            }
        }
	}
	return $ret;
} // }}}

function unlinkRecursive($dir) { // {{{
    if (substr($dir,-1) == '/' || substr($dir,-1) == '\\') {
        $dir = substr($dir, 0, strlen($dir)-1);
    }
    if (@is_dir($dir)) { 
        if ($dh = @opendir($dir)) { 
            while (($file = @readdir($dh)) !== false) { 
                if ($file!='.' && $file!='..') {
                    unlinkRecursive($dir . '/'. $file);
                }
            } 
            @closedir($dh); 
        }
        @rmdir($dir);
    } else if (@is_file($dir)) {
        @unlink($dir);
    }
} // }}}

/**
* Parses the hostname specification. Converts the FQDN hostname
* to dotted hostname, for example
*
*    www.hosting.com:81 -> .hosting.com
*
*/
function func_parse_host($host) { // {{{
    // parse URL
    if (substr(strtolower($host), 0, 7) != 'http://') {
        $host = 'http://' . $host;
    }
    $url_details = func_parse_url($host);
    $host = isset($url_details["host"]) ? $url_details["host"] : $host;
    
    // strip WWW hostname
    if (substr(strtolower($host), 0, 4) == 'www.') {
        $host = substr_replace($host, '', 0, 3);
    }   
    return $host;
} // }}}

function func_parse_url($url) { // {{{
    global $options;
    
    $parts_default = array(
            "scheme"   => "http",
            "host"     => $options["host_details"]["http_host"],
            "port"     => "", 
            "user"     => "", 
            "pass"     => "", 
            "path"     => $options["host_details"]["web_dir"],
            "query"     => "", 
            "fragment" => ""
            );
    $parsed_parts = @parse_url($url);
    if (!is_array($parsed_parts)) {
    	$parsed_parts = array();
    }
    return array_merge($parts_default, $parsed_parts);
} // }}}

/**
* Uploads SQL patch into the database. If $connection is not defined, uses
* mysql_query($sql) syntax, otherwise mysql_query($sql, $connection);
* If $ignoreErrors is true, it will display all SQL errors and proceed.
*/
function query_upload($filename, $connection = null, $ignoreErrors = false, $is_restore = false) { // {{{
	if (!$fp = @fopen($filename, "rb")) {
        echo "<font color=red>[Failed to open $filename]</font></pre>\n";
        return false;
    }

	$command = "";
	$counter = 1;

	while (!feof($fp)) {
        $c = '';
        // read SQL statement from file
        do {
		    $c .= fgets($fp, 1024);
		    $endPos = strlen($c) - 1;
        } while ($c{$endPos} != "\n" && !feof($fp));
        $c = chop($c);
        // skip comments
        if ($c{0} == '#') continue;
        if (substr($c, 0, 2) == '--') continue;

        // parse SQL statement
		$command .= $c;
		if (substr($command, -1) == ';') {
			$command = substr($command, 0, strlen($command)-1);

            $table_name = "";
			if (preg_match("/^CREATE TABLE ([_a-zA-Z0-9]*)/i", $command, $matches)) {
				$table_name = $matches[1];
				echo "Creating table [$table_name] ... "; flush();
            } elseif (preg_match("/^ALTER TABLE ([_a-zA-Z0-9]*)/i", $command, $matches)) {
                $table_name = $matches[1];
                echo "Altering table [$table_name] ... "; flush();
			} elseif (preg_match("/^DROP TABLE IF EXISTS ([_a-zA-Z0-9]*)/i", $command, $matches)) {
                $table_name = $matches[1];
                echo "Deleting table [$table_name] ... "; flush();
            } else {
                $counter ++;
            }    

            // execute SQL
			if (is_resource($connection)) {
                mysql_query($command, $connection);
            } else {
                mysql_query($command);
            }
            if (is_resource($connection)) {
                $myerr = mysql_error($connection);
            } else {
                $myerr = mysql_error();
            }
            // check for errors
            if (!empty($myerr)) {
                query_upload_error($myerr, $ignoreErrors);
                if (!$ignoreErrors) {
                    break;
                }    
            } elseif ($table_name != "") {
                echo "<font color=green>[OK]</font><br>\n";
            } elseif (!($counter % 20)) {
                echo "."; flush();
            }

			$command = "";
			flush();
		}
	}

	fclose($fp);
    if ($counter>20) {
        print "\n";
    }

    if($is_restore) return empty($myerr);

	return $ignoreErrors ? true : empty($myerr);
} // }}}

function query_upload_error($myerr, $ignoreErrors) { // {{{
	if (empty($myerr)) {
		echo "\n";
		echo "<font color=green>[OK]</font>\n";
	} elseif ($ignoreErrors) {
		echo "<font color=blue>[NOTE: $myerr]</font>\n";
	} else {
		echo "<font color=red>[FAILED: $myerr]</font>\n";
    }
} // }}}

/**
* Generates a code consisting of $length characters from the set [A-Z0-9].
* Used as GC & discount coupon code, as well as installation auth code, etc.
*/
function generate_code($length = 8) { // {{{
    $salt = "ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
    srand((double)microtime()*1000000);
    $i = 0;

    $code = "";
    while ($i < $length) {
        $num = rand() % 35;
        $tmp = substr($salt, $num, 1);
        $code = $code . $tmp;
        $i++;
    }

    return $code;
} // }}}

/**
* Reads classes dependencies (class file, class extends)
* 
* @param String $base Catalog where the class deps. file is located
*/
function func_read_classes($base = "", $list = "classes.lst", $verbose = true) { // {{{
	if (strlen($base) && ($base{0} == '/' || $base{1} == ':')) {
	    $list = $base . "/" . $list;
	} else {
	    $list = "classes/" . ($base != "" ? "$base/" : "") . $list;
	}
    if (!$handle = @fopen($list, "rb")) {
        if ($verbose) {
            die("Class dependencies file not found: $list");
        } else {
            return;
        }    
    }
    while ($columns = fgetcsv($handle, 65535, ":")) {
        $GLOBALS["xlite_class_files"][$columns[0]] = $columns[1];
        $GLOBALS["xlite_class_deps"][$columns[0]] = $columns[2];
    }
    fclose($handle);

} // }}}

function func_die($message) { // {{{
	static $ignore;
	static $trace_message;

	$trace_message .= "<pre>\n";
    if (function_exists("debug_backtrace")) {
        foreach (debug_backtrace() as $trace) {
            if (!isset($trace["file"])) {
                $trace["file"] = "?";
            }
            if (!isset($trace["line"])) {
                $trace["line"] = "?";
            }
            if (!isset($trace["class"])) {
                $trace["class"] = "?";
            }
            $trace_args = (isset($trace["args"]) && is_array($trace["args"]) && count($trace["args"]) > 0) ? implode(',', $trace["args"]) : "n/a";
    		$trace_message .= $trace["class"]."::".$trace["function"] . "(". basename($trace["file"]).":".$trace["line"].") <b>args:</b> $trace_args\n";
        }
    }
	$trace_message .= $message . "</pre>\n" . $ignore . "\n";

	if (!$ignore) {
		$ignore ++;
		// func_new can also cause func_die being called
    	$dialog =& func_new("Dialog");
    }

	global $options;
	if ((isset($options["log_details"]["suppress_errors"]) && $options["log_details"]["suppress_errors"])) {
		$log_name = $options["log_details"]["name"];
		if (is_writable(dirname($log_name))) {
			if (file_exists($log_name) && !is_writable($log_name)) {
				if ((isset($options["log_details"]["suppress_logging_errors"]) && $options["log_details"]["suppress_logging_errors"])) {
                	if (is_object($dialog)) {
                    	$dialog->redirect();
                	} else {
                		die;
                	}
				} else {
            		$trace_message = str_replace("<pre>", "\n", $trace_message);
                    $trace_message = str_replace("</pre>", "\n", $trace_message);
                    $trace_message = "<pre>" . htmlspecialchars($trace_message) . "</pre>";
                    die ($trace_message);
        		}
			}
            require_once "Log.php";
            $logger =& Log::singleton("file", $log_name, "XLite kernel panic");
            $log_message = str_replace("<pre>", "\n" . str_repeat("-", 50), $trace_message);
            $log_message = str_replace("</pre>", "\n" . str_repeat("-", 50) . "\n", $log_message);
            $logger->log($log_message, "LOG_ERR");
        } else {
			if ((isset($options["log_details"]["suppress_logging_errors"]) && $options["log_details"]["suppress_logging_errors"])) {
            	if (is_object($dialog)) {
                	$dialog->redirect();
            	} else {
            		die;
            	}
			} else {
        		$trace_message = str_replace("<pre>", "\n", $trace_message);
                $trace_message = str_replace("</pre>", "\n", $trace_message);
                $trace_message = "<pre>" . htmlspecialchars($trace_message) . "</pre>";
                die ($trace_message);
        	}
        }

    	if (is_object($dialog)) {
        	$dialog->redirect();
    	}

		die;
    }

	$trace_message = str_replace("<pre>", "\n", $trace_message);
    $trace_message = str_replace("</pre>", "\n", $trace_message);
    $trace_message = "<pre>" . htmlspecialchars($trace_message) . "</pre>";
    die ($trace_message);
} // }}}

/**
* Strips slashes and trims the specified array values 
* (strips from strings only)
*
* @access private
* @param  array $array The array to strip slashes
*/
function func_strip_slashes(&$array) { // {{{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            func_strip_slashes($array[$key]);
        } elseif (is_string($value)) {
            $array[$key] = trim(stripslashes($value));
        }
    }
} // }}}

function func_htmldecode($encoded) { // {{{
    return strtr($encoded,array_flip(get_html_translation_table(HTML_ENTITIES)));
} // }}}

function func_ends_with($str, $end) { // {{{
    return substr($str, -strlen($end)) == $end;
} // }}}

function func_starts_with($str, $start) { // {{{
    return substr($str, 0, strlen($start)) == $start;
} // }}}

function func_cleanup_cache($cache, $verbose = false) { // {{{
    $cacheDir = "var/run";
    if ($verbose) {
    	echo "Cleaning up $cache cache ";
    }
    if ($cache == "classes") {
        global $options;
        $cacheDir = $options["decorator_details"]["compileDir"];
        // remove last '/'
        if ($cacheDir{strlen($cacheDir)-1} == '/') {
            $cacheDir = substr($cacheDir, 0, strlen($cacheDir)-1);
        }
        $cache = "";
    }

    clearstatcache();

    if (is_dir("$cacheDir/$cache") || 
		(($cache == "skins") && (file_exists("$cacheDir/cart.html.php") || file_exists("$cacheDir/cart.html.init.php")))) {

        if (func_is_locked("cache") || ($_lock_cache = func_lock("cache"))) {
            unlinkRecursive("$cacheDir/$cache");
            sleep(3);
            $endlessCounter = 0;
            while (file_exists("$cacheDir/$cache")) {
                unlinkRecursive("$cacheDir/$cache");
                sleep(3);
            	$endlessCounter ++;
            	if ($endlessCounter > 10) {
            		break;
            	}
            }
			if ($cache == "skins") {
				unlinkRecursive("$cacheDir/cart.html.php");
				unlinkRecursive("$cacheDir/cart.html.init.php");
			}
            if ($_lock_cache) {
            	func_unlock("cache");
            }
        }
        if ($verbose) {
        	echo "$cacheDir/$cache ... [<font color=green>OK</font>]<br>";
        }
    } elseif ($verbose) {
    	echo "$cacheDir/$cache ... [<font color=red>NOT FOUND</font>]<br>";
    }
} // }}}

//
// This function create file lock in temporaly directory
// It will return file descriptor, or false.
//
function func_lock($lockname, $ttl = 15, $cycle_limit = 0) { // {{{
    global $_lock_hash, $options;        

    if (empty($lockname)) {
        return false;
    }

    if (!empty($_lock_hash[$lockname])) {
        return $_lock_hash[$lockname];
    }

    $lockDir = $options["decorator_details"]["lockDir"];
    // remove last '/'
    if ($lockDir{strlen($lockDir)-1} == '/') {
        $lockDir = substr($lockDir, 0, strlen($lockDir)-1);
    }
    if (!is_dir($lockDir)) {
        if (!mkdirRecursive($lockDir, 0755)) return false;
    }
    $fname = $lockDir."/".$lockname.".lock";

    // Generate current id
    $id = md5(uniqid(rand(0, substr(floor(getmicrotime()*1000), 3)), true));
    $_lock_hash[$lockname] = $id;

    $file_id = false;
    $limit = $cycle_limit;
    while (($limit-- > 0 || $cycle_limit <= 0)) {
        if (!file_exists($fname)) {

            # Write locking data
            $fp = @fopen($fname, "w");
            if ($fp) {
                @fwrite($fp, $id.time());
                fclose($fp);
            }
        }

        $fp = @fopen($fname, "r");
        if (!$fp)
            return false;

        $tmp = @fread($fp, 43);
        fclose($fp);

        $file_id = substr($tmp, 0, 32);
        $file_time = (int) substr($tmp, 32);

        if ($file_id == $id)
            break;

        if ($ttl > 0 && time() > $file_time+$ttl) {
            @unlink($fname);
            continue;
        }

        sleep(1);
    }

    return $file_id == $id ? $id : false;
} // }}}

//
// This function releases file lock which is previously created by func_lock
//
function func_unlock($lockname) { // {{{
    global $_lock_hash, $options;        

    if (empty($lockname) || empty($_lock_hash[$lockname])) {
        return false;
    }

    $lockDir = $options["decorator_details"]["lockDir"];
    // remove last '/'
    if ($lockDir{strlen($lockDir)-1} == '/') {
        $lockDir = substr($lockDir, 0, strlen($lockDir)-1);
    }
    if (!is_dir($lockDir)) {
        if (!mkdirRecursive($lockDir, 0755)) return false;
    }
    $fname = $lockDir."/".$lockname.".lock";
    if (!file_exists($fname)) {
        return false;
    }

    $fp = fopen($fname, "r");
    if (!$fp) {
        return false;
    }

    $tmp = fread($fp, 43);
    fclose($fp);

    $file_id = substr($tmp, 0, 32);
    $file_time = (int) substr($tmp, 32);

    if ($file_id == $_lock_hash[$lockname]) {
        @unlink($fname);
    }

    unset($_lock_hash[$lockname]);

    return true;
} // }}}

//
// This function checks, whether the lock is active
//
function func_is_locked($lockname, $ttl = 15) { // {{{
    global $_lock_hash, $options;        

    if (empty($lockname)) {
        return false;
    }

    $lockDir = $options["decorator_details"]["lockDir"];
    // remove last '/'
    if ($lockDir{strlen($lockDir)-1} == '/') {
        $lockDir = substr($lockDir, 0, strlen($lockDir)-1);
    }
    $fname = $lockDir."/".$lockname.".lock";
    if (!file_exists($fname)) {
        if (!file_exists($fname)) {
            return false;
        }
    }

    $fp = fopen($fname, "r");
    if (!$fp) {
        return false;
    }

    $tmp = fread($fp, 43);
    fclose($fp);

    $file_id = substr($tmp, 0, 32);
    $file_time = (int) substr($tmp, 32);

    if ($ttl > 0 && time() > $file_time+$ttl) {
        @unlink($fname);
        return false;
    }

    return true;
} // }}}

function func_shop_closed($reason = null) { // {{{
    @readfile('shop_closed.html');
    if ($reason) {
    	echo "<!-- Reason: $reason -->";
    }
    die("<!-- shop closed -->");
} // }}}

// func_https_request($method, $url, $vars) {{{
function func_https_request ($method, $url, $vars) {
	$request = func_new('HTTPS');

    $_vars = array ();
    if (is_array($vars)) {
    	foreach ($vars as $k=>$v) {
        	list ($var_key, $var_value) = explode ("=", $v, 2);
            if (!isset($var_value)) {
            	$var_value = "";
            }

            $_vars [$var_key] = $var_value;
        }
    }

    $vars = $_vars;

    $request->url = $url;
    $request->data = $vars;

    if ($GLOBALS["debug"]) {
		echo "request->data:<pre>"; print_r($request->data); echo "</pre><br>";
    }
    $request->request ();

    if ($GLOBALS["debug"]) {
    	echo "request->response:<pre>"; print_r($request->response); echo "</pre><br>";
    }
    return array ("", $request->response);
}
//}}}

function &func_parse_csv($line, $delimiter, $q, &$error) { // {{{
    $line = trim($line);
    if (empty($q)) {
        return explode($delimiter, $line);
    }
    $arr = array();
    $state = "outside";
    $field = "";
    $error = "";
    for ($i=0; $i<=strlen($line); $i++) {
        if ($i==strlen($line)) $char = "EOL";
        else $char = $line{$i};
        if ($state == "outside") {
            if ($char == $q) {
                $state = "inside";
                $field = "";
            } elseif ($char == $delimiter || $char == "EOL") {
                // empty field
                $arr[] = "";
            } else {
                $state = "field";
                $field = $char;
            }
        } elseif ($state == "inside") {
            if ($char == $q) {
                $state = "quote inside";
            } else if ($char == "EOL") {
                $error = "Unexpected end of line; $q expected";
                return null;
            } else {
                $field .= $char;
            }
        } elseif ($state == "quote inside") {
            if ($char == $q) { // double-quote
                $state = "inside";
                $field .= $q;
            } elseif ($char == $delimiter || $char == "EOL") {
                $arr[] = $field;
                $state = "outside";
            } else {
                $error = "Unexpected character $char outside quotes: $q expected (pos $i)";
                return null;
            }
        } elseif ($state == "field") {
            if ($char == $delimiter || $char == "EOL") {
                $state = "outside";
                $arr[] = $field;
            } else {
                $field .= $char;
            }
        }
    }
    return $arr;
} // }}}

function func_construct_csv($fields, $delimiter, $q) { // {{{
    $test = "";
    $fs = array();
    foreach ($fields as $f) {
        if (empty($q)) {
            $fs[] = strtr($f, "\n\r", "  ");
        } else {
            $fs[] = $q.strtr(str_replace("$q", "$q$q", $f), "\n\r", "  ").$q;
        }
    }
    return implode($delimiter, $fs);
} // }}}

function func_version_compare($ver1, $ver2) { // {{{
    if (function_exists("version_compare"))
        return version_compare($ver1, $ver2);

    $ver1 = str_replace("..", ".", preg_replace("/([^\d\.]+)/S", ".\\1.", str_replace(array("_", "-", "+"), array(".", ".", "."), $ver1)));
    $ver2 = str_replace("..", ".", preg_replace("/([^\d\.]+)/S", ".\\1.", str_replace(array("_", "-", "+"), array(".", ".", "."), $ver2)));

    $ver1 = (array)explode(".", $ver1);
    $ver2 = (array)explode(".", $ver2);

    $ratings = array(
        "/^dev$/i" => -100,
        "/^alpha$/i" => -90,
        "/^a$/i" => -90,
        "/^beta$/i" => -80,
        "/^b$/i" => -80,
        "/^RC$/i" => -70,
        "/^pl$/i" => -60
    );
    foreach ($ver1 as $k => $v) {
        if (!is_numeric($v))
            $v = preg_replace(array_keys($ratings), array_values($ratings), $v);

        if (!is_numeric($ver2[$k]))
            $ver2[$k] = preg_replace(array_keys($ratings), array_values($ratings), $ver2[$k]);

        $r = strcmp($v, $ver2[$k]);
        if ($r != 0)
            return $r;
    }

    return 0;
} // }}}
 
function func_convert_to_byte($file_size) { // {{{ 
    $val = trim($file_size);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
} // }}}

function func_check_memory_limit($current_limit, $required_limit) { // {{{ 
    $limit = func_convert_to_byte($current_limit);
    $required = func_convert_to_byte($required_limit);
    if ($limit < $required) {
        # workaround for http://bugs.php.net/bug.php?id=36568
        if ((LC_OS_IS_WIN) && (func_version_compare(phpversion(),"5.1.0") < 0))
            return true;

        @ini_set('memory_limit', $required_limit);
        $limit = @ini_get('memory_limit');
        return (strcasecmp($limit, $required_limit) == 0);
    }

    return true; 
} // }}}

function func_set_memory_limit($new_limit) { // {{{ 
    $current_limit = @ini_get("memory_limit");
    return func_check_memory_limit($current_limit, $new_limit);
} // }}}

if (!function_exists("get_php_execution_mode")) {
function get_php_execution_mode() {
    global $options;
	return isset($options['filesystem_permissions']['permission_mode']) ? $options['filesystem_permissions']['permission_mode'] : 0;
}
}

function func_array_merge() { // {{{
    $args = func_get_args();
    foreach ($args as $k => $arg) {
    if (is_null($arg) || !is_array($arg) || count($arg)==0)
        unset($args[$k]);
    }
    if (count($args)==0) return array();
    return call_user_func_array('array_merge', $args);
} // }}}

function func_is_timezone_changable() { // {{{
    return function_exists("date_default_timezone_set") && class_exists("DateTimeZone");
} // }}}

function func_get_timezone() { // {{{
    if (function_exists("date_default_timezone_get"))
        return @date_default_timezone_get();
    else
        return null;
} // }}}

function func_set_timezone() { // {{{
    global $options;
    $link = mysql_connect($options["database_details"]["hostspec"], $options["database_details"]["username"], $options["database_details"]["password"]);
    $query = "SELECT value FROM xlite_config WHERE name='time_zone'";
    if ($link && mysql_select_db($options["database_details"]["database"], $link) && $result = mysql_query($query, $link)) {
        $tz = mysql_fetch_assoc($result);
        if (is_array($tz) && isset($tz["value"])) {
            @date_default_timezone_set($tz["value"]);
            if (func_get_timezone() == $tz["value"])
                return true;
        }
    }
    return false;
} // }}}

function func_get_timezones() { // {{{
    if (class_exists("DateTimeZone")) {
        $timezone_identifiers = DateTimeZone::listIdentifiers();
    } else {
        $timezone_identifiers = null;
    }
    return $timezone_identifiers;
} // }}}

// WARNING :
// Please ensure that you have no whitespaces / empty lines below this message.
// Adding a whitespace or an empty line below this line will cause a PHP error.
?>
