<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>phpTyper</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <link rel="stylesheet" href="css/bootstrap.min.css">
        <style>
            body {
                padding-top: 60px;
            }
        </style>
        <link rel="stylesheet" href="css/bootstrap-responsive.min.css">

        <link rel="stylesheet" href="js/vendor/codemirror-3.1/lib/codemirror.css">
        <link rel="stylesheet" href="js/vendor/codemirror-3.1/theme/monokai.css">
        <link rel="stylesheet" href="js/vendor/codemirror-3.1/addon/dialog/dialog.css">

        <link rel="stylesheet" href="css/main.css">

        <script src="js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>

        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <link rel="icon" href="favicon.ico" type="image/x-icon">
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

        <!-- This code is taken from http://twitter.github.com/bootstrap/examples/hero.html -->

        <div class="navbar navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <a class="brand" href="javascript:void(null);">phpTyper</a>
                    <div class="nav-collapse collapse">
                        <ul class="nav">
                            <li class="">
                                <a href="javascript:window.location.href='#';window.location.reload(true);">
                                    New document
                                </a>
                            </li>
                            <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                    History
                                    <b class="caret"></b>
                                </a>
                                <ul class="dropdown-menu history"></ul>
                            </li>
                        </ul>
                        <ul class="nav pull-right">
                            <li class="">
                                <a href="#help" data-toggle="modal">Help</a>
                            </li>
                            <li class="">
                                <a href="#about" data-toggle="modal">About</a>
                            </li>
                        </ul>
                    </div><!--/.nav-collapse -->
                </div>
            </div>
        </div>

        <div class="container">

            <!-- Example row of columns -->
            <div class="row">
                <div class="span6">
                    <fieldset>
                        <legend>Start typing</legend>
                    </fieldset>
                    <div class='editor-pane'>
                        <form>
                            <div class='editor'>
                                <textarea name='code'>
&lt;pre&gt;
&lt;?php

echo 'Hello world';</textarea>
                            </div>
                            <div class='toolbar'>
                                <button type="submit" class="btn btn-inverse">Run &raquo;</button>
                                <span>Ctrl+Enter</span>
                                <label class="checkbox">
                                    <input type="checkbox" checked> Autorun
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="span6">
                    <fieldset>
                        <legend>
                            Output
                            <span class='time'></span>
                        </legend>
                    </fieldset>
                    <div class='output-pane'>
                        <div class='alert alert-block alert-error fade'>
                            <h4 class="alert-heading">Oh snap! You got an error!</h4>
                            <p>
                                And nothing explains it
                            </p>
                        </div>
                        <div class='loading'>
                            <div id="movingBallG">
                                <div class="movingBallLineG"></div>
                                <div id="movingBallG_1" class="movingBallG"></div>
                            </div>
                        </div>
                        <div class='output'></div>
                    </div>
               </div>
            </div>

        </div> <!-- /container -->

        <!-- Modals -->
        <div id="help" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="helpLabel" aria-hidden="true">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="helpLabel">Help</h3>
          </div>
          <div class="modal-body">
            <p>
                <h4>Sandbox</h4>
                <p>
                    All code is executed in a <a href='http://en.wikipedia.org/wiki/Sandbox_(software_development)'>sandbox</a>, hence the PHP environment may be different than you're used to and we restricted the use of some functions (see below).
                </p>
                <h4 id='environment'>Environment</h4>
                <dl>
                    <dt>file access</dt>
                    <dd>The open_basedir directive is set and you can read and write to only one directory: <code><?php echo realpath('tmp' . DIRECTORY_SEPARATOR); ?></code></dd>

                    <dt>http</dt>
                    <dd>Your code is not executed in a <a href='http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol'>HTTP context</a>, so global variables such as <code>$_GET</code>, <code>$_POST</code>, <code>$_COOKIE</code> and <code>$_SESSION</code> are unavailable.</dd>
                </dl>
                <h4 id='restricted'>Unavailable function</h4>
                <ul>
                    <li>exec()</li>
                    <li>passthru()</li>
                    <li>shell_exec()</li>
                    <li>system()</li>
                    <li>proc_open()</li>
                    <li>popen()</li>
                    <li>curl_exec()</li>
                    <li>curl_multi_exec()</li>
                    <li>parse_ini_file()</li>
                    <li>show_source()</li>
                    <li>pcntl_fork()</li>
                    <li>pcntl_exec()</li>
                    <li>session_start()</li>
                    <li>phpinfo()</li>
                    <li>ini_set()</li>
                <ul>
            </p>
          </div>
          <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
          </div>
        </div>

        <div id="about" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="aboutLabel" aria-hidden="true">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="aboutLabel">About phpTyper</h3>
          </div>
          <div class="modal-body">
            <p>
                phpTyper is like a <a href='http://en.wikipedia.org/wiki/WYSIWYG'>WYSISYG</a> editor for the <a href='http://en.wikipedia.org/wiki/PHP'>PHP programming language</a>.
                It is created by web developers at <a href="http://nabble.nl">Nabble</a> because the <a href="http://php.net/manual/en/features.commandline.interactive.php">interactive PHP
                shell</a> wasn't HTML5 enough for them.
            </p>
            <p>
                Front-end created using <a href="http://twitter.github.com/bootstrap/">Twitter Bootstrap</a>, <a href="http://codemirror.net/">CodeMirror</a> and <a href="http://jonnystromberg.com/hash-js">hash.js</a>.
                The execution environment uses parts of <a href="https://github.com/fregster/PHPSandbox">PHPSandbox</a>.
            </p>
            <p>
                <a class='btn btn-info' href="#help" data-dismiss="modal" data-toggle="modal">
                    Show me the help page
                </a>
            </p>
          </div>
          <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
          </div>
        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.9.1.min.js"><\/script>')</script>

        <script src="js/vendor/bootstrap.min.js"></script>

        <script src="js/vendor/codemirror-3.1/lib/codemirror.js"></script>
        <script src="js/vendor/codemirror-3.1/addon/edit/matchbrackets.js"></script>
        <!--<script src="js/vendor/codemirror-3.1/addon/selection/active-line.js"></script>
        <script src="js/vendor/codemirror-3.1/addon/dialog/dialog.js"></script>
        <script src="js/vendor/codemirror-3.1/addon/search/searchcursor.js"></script>
        <script src="js/vendor/codemirror-3.1/addon/search/search.js"></script>
        <script src="js/vendor/codemirror-3.1/addon/search/match-highlighter.js"></script>-->

        <script src="js/vendor/codemirror-3.1/mode/htmlmixed/htmlmixed.js"></script>
        <script src="js/vendor/codemirror-3.1/mode/xml/xml.js"></script>
        <script src="js/vendor/codemirror-3.1/mode/javascript/javascript.js"></script>
        <script src="js/vendor/codemirror-3.1/mode/css/css.js"></script>
        <script src="js/vendor/codemirror-3.1/mode/clike/clike.js"></script>
        <script src="js/vendor/codemirror-3.1/mode/php/php.js"></script>

        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>

        <script>
            var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
            (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
            g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
            s.parentNode.insertBefore(g,s)}(document,'script'));
        </script>
    </body>
</html>
