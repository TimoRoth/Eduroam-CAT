<?php

/* * ********************************************************************************
 * (c) 2011-13 DANTE Ltd. on behalf of the GN3 and GN3plus consortia
 * License: see the LICENSE file in the root directory
 * ********************************************************************************* */
?>
<?php

/**
 * This file contains a random assortment of useful functions and classes.
 * @author Stefan Winter <stefan.winter@restena.lu>
 * @author Tomasz Wolniewicz <twoln@umk.pl>
 *
 * @package Developer
 */
/**
 * necessary includes
 */
require_once(dirname(dirname(__FILE__)) . "/config/_config.php");

function error($t) {
    print ("$t\n");
}

/**
 * write debug messages to the log
 *
 */
function debug($level, $t) {
    if (Config::$DEBUG_LEVEL >= $level) {
        ob_start();
        printf("%-015s", microtime(TRUE));
        print " ($level) ";
        print_r($t);
        $output = ob_get_clean();
        if (Config::$PATHS['logdir']) {
            $f = fopen(Config::$PATHS['logdir'] . "/debug.log", "a");
            fwrite($f, $output);
            fclose($f);
        } else {
            print $output;
        }
    }
}

/**
 * this direcory delete function has been copied from PHP documentation
 */

function rrmdir($dir) {
    foreach (glob($dir . '/*') as $file) {
        if (is_dir($file))
            rrmdir($file);
        else
            unlink($file);
    }
    rmdir($dir);
}


/**
 * @assert (Array("server1.restena.lu", "othersrv.restena.lu")) == "restena.lu"
 * 
 * @param array $hosts list of strings (hosts)
 * @return string the common suffix
 */
function calculateCommonHostSuffix(array $hosts) {
    // massage names. A trailing dot can be omitted
    foreach ($hosts as $index => $host)
        if (substr($host, -1) == '.') {
            $hosts[$index] = substr($host, 0, -1);
            echo "Changed host to " . $hosts[$index] . "\n";
        }
    // easy :-)
    if (count($hosts) == 1)
        return $hosts[0];
    // not so easy :-(
    else {
        // look for the last dot from the end; if the corresponding substring is
        // different, no common match!
        $explodednames = array();
        foreach ($hosts as $host)
            $explodednames[] = explode('.', $host);
        $commonsuffix = array();
        while (count($explodednames[0]) > 0) {
            $match = TRUE;
            $testname = array_pop($explodednames[0]);
            for ($i = 1; $i < count($explodednames); $i = $i + 1) {
                if (count($explodednames[$i]) == 0)
                    $match = FALSE;
                $bla = array_pop($explodednames[$i]);
                if ($bla != $testname)
                    $match = FALSE;
            }
            if ($match == TRUE)
                $commonsuffix[] = $testname;
            else
                break;
        }
        $finalsuffix = array_reverse($commonsuffix);
        return implode('.', $finalsuffix);
    }
}

function downloadFile($url) {
    $data;
    if (preg_match("/:\/\//", $url)) {
        # we got a URL, download it
        $download = fopen($url, "rb");
        $data = stream_get_contents($download);
        if (!$data) {
            debug(2, "Failed to download the file from $url");
            return FALSE;
        }
        return $data;
    } else {
        debug(3, "The specified string does not seem to be a URL!");
        return FALSE;
    }
}

/**
 * generates a UUID
 *
 * @param string $prefix an extra prefix to set before the UUID
 * @return UUID (possibly prefixed)
 */
function uuid($prefix = '') {
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr($chars, 0, 8) . '-';
    $uuid .= substr($chars, 8, 4) . '-';
    $uuid .= substr($chars, 12, 4) . '-';
    $uuid .= substr($chars, 16, 4) . '-';
    $uuid .= substr($chars, 20, 12);
    return $prefix . $uuid;
}

/**
 * pick a proper value for a given language
 * @param array $val_arr an array of (locale,content) records
 * @param string locale language code
 * @return string localised value corresponding to the chosen
 * locale or to the defalut locale C if a better mach was not available
 */
function getLocalisedValue($val_arr, $locale) {
    if (count($val_arr) > 0) {
        $r_val = array();
        foreach ($val_arr as $val) {
            $try = unserialize($val['value']);
            $r_val[$try['lang']] = $try['content'];
        }
        if (isset($r_val[$locale]))
            $out = $r_val[$locale];
        elseif (isset($r_val['C']))
            $out = $r_val['C'];
        else
            $out = 0;
    } else {
        $out = 0;
    }
    debug(4, "getLocalisedValue:$locale:$out\n");
    return $out;
}

?>
