<?php

/**************
 * CONFIGURATION
 **************/

    const DS = DIRECTORY_SEPARATOR;

    $CODE_DIR = 'code' . DS;
    $DOC_DIR = 'doc' . DS . 'php-chunked-xhtml' . DS;

    ini_set('display_warnings', 'off');
    ini_set('display_errors', 'off');
    error_reporting( -1 );

/**************
 * SANDBOX
 **************/

    // require the sandbox lib
    require_once 'lib/PHPSandbox/phpsandbox.php';

    // empty tmp directory
    foreach(glob('tmp' . DS . '*') as $file) {
        unlink($file);
    }

    // copy prepend and append files to tmp directory
    copy('lib' . DS . 'PHPSandbox' . DS . 'phpsandbox-prepend.php', 'tmp' . DS . 'phpsandbox-prepend.php');
    copy('lib' . DS . 'PHPSandbox' . DS . 'phpsandbox-append.php', 'tmp' . DS . 'phpsandbox-append.php');

    // init the sandbox
    $sandbox = new PHPSandbox(array(
        'display_errors' => 'log_to_file',
        'off' => true,
        'tmp_dir' => __DIR__ . DS . 'tmp',
        'directory_protection_allow_tmp' => false,
        'auto_prepend_file' => realpath('tmp' . DS . 'phpsandbox-prepend.php'),
        'auto_append_file' => realpath('tmp' . DS . 'phpsandbox-append.php'),
        'max_execution_time' => 10,
    ));

/**************
 * PHP MANUAL
 **************/

function phpDocFromError($description) {
    global $DOC_DIR;

    $allFunctions = get_defined_functions();
    $allFunctions = $allFunctions['internal'];

    foreach($allFunctions as $function) {
        if (strpos($description, $function . '(') !== false) {
            $function = str_replace('_', '-', $function);
            $docfilename = $DOC_DIR . 'function.' . $function . '.html';
            if (file_exists($docfilename)) {
                return getDocStyle() . file_get_contents($docfilename);
            }
        }
    }
    return '';
}

function getDocStyle() {
    return "<style>
        body, html {
            margin: 0;
            padding: 0;
        }
        body {
            color: #666;
            font-family: sans-serif;
            font-size: 0.9em;
        }
        a {
            color: #666;
            pointer-events: none;
        }
        .manualnavbar, hr {
            display: none;
        }
    </style>";
}

/**************
 * HELPER
 **************/

function getHash() {
    $hash = $_GET['hash'];
    $valid = preg_match("/^[a-zA-Z0-9]*$/", $hash);
    if ($valid === 1) {
        return $hash;
    } else {
        die('Go hack yourself please');
    }
}

/**************
 * BY CODE
 **************/

    if (isset($_GET['code'])) {
        $code = $_GET['code'];

        if (isset($_GET['hash'])) {
            $hash = getHash();
            $filename = $CODE_DIR . $hash . '.php';
        } else {
            do {
                $hash = substr(md5(time()), 0, 5);
                $filename = $CODE_DIR . $hash . '.php';
            } while (file_exists($filename));
        }

        file_put_contents($filename, $code);

/**************
 * BY HASH
 **************/

    } else if (isset($_GET['hash'])) {
        $hash = getHash();
        $filename = $CODE_DIR . $hash . '.php';

        if (!file_exists($filename)) {
            header("HTTP/1.0 404 Not Found");
            header("Status: 404 Not Found");

            die("File not found");
        } else {
            $code = file_get_contents($filename);
        }
    }

/**************
 * RETURN FUNCTION
 **************/

    function error($error) {
        global $hash, $code;

        $return = array(
            'hash' => $hash,
            'error' => $error,
            'output' => ''
        );
        if (!isset($_GET['code'])) {
            $return['code'] = base64_encode($code);
        }
        if (isset($error['message'])) {
            $return['output'] = base64_encode(phpDocFromError($error['message']));
        }

        header("HTTP/1.0 500 Internal Server Error");
        header("Status: 500 Internal Server Error");

        ob_end_clean();
        echo json_encode($return);
        exit;
    }

    function write($output) {
        global $hash, $code, $sandbox;

        $return = array(
            'hash' => $hash,
            'time' => $sandbox->runTime(),
            'output' => base64_encode(getDocStyle() . $output)
        );
        if (!isset($_GET['code'])) {
            $return['code'] = base64_encode($code);
        }
        ob_end_clean();
        echo json_encode($return);
        exit;
    }

/**************
 * RUN THE FILE
 **************/

    $output = $sandbox->runFile($filename, array(), false);

/**************
 * OUTPUT RESULT
 **************/

    // error?
    if (substr_count($output, '{error_Xs96YbrAZD}')) {
        $output = str_replace('{error_Xs96YbrAZD}', '', $output);
        error(unserialize($output));
    } else {
        write($output);
    }
