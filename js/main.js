$(document).ready(function() {

    Modernizr.load({
        test: window.btoa && window.atob,
        nope: 'js/vendor/base64.min.js',
        complete: phpTyper
    });

});

var phpTyper = function() {

    // Cache
    var $textarea = $('.editor textarea');
    var $output = $('.output');
    var $autorun = $('.editor-pane .toolbar label input');

    var doNotUpdateOutput = false;
    var h;
    var activeLine;
    var dirty = false;
    var loading;

    // CodeMirror
    var codeEditor = CodeMirror.fromTextArea($textarea[0], {

        theme                   : 'monokai',

        indentUnit              : 4,
        indentWithTabs          : true,
        enterMode               : "keep",
        tabMode                 : "shift",

        styleActiveLine         : true,
        showCursorWhenSelecting : true,
        highlightSelectionMatches: true,
        autofocus               : true,

        lineNumbers             : true,
        matchBrackets           : true,
        mode                    : "application/x-httpd-php",

    });

    setTimeout(function() {
        codeEditor.on('change', function(instance, changeObj) {
            if (changeObj.text[0] !== '') {
                setDirty(true);
                clearErrorLine();
            }
        });
    }, 5000);

    codeEditor.on('cursorActivity', function() {
        if (activeLine !== codeEditor.getCursor().line) {
            activeLine = codeEditor.getCursor().line;
            if (dirty === true && $autorun.is(':checked')) {
                runFromEditor();
            }
        }
    });

    $(document).on('keyup', function(e) {
        if (e.ctrlKey && e.which === 13) {
            runFromEditor();
        }
    });

    // Size of panes
    $(window).on("debouncedresize", function(e) {
        var viewportHeight = $(window).height();
        // editor
        codeEditor.setSize('100%', (viewportHeight - 180) + 'px');
        codeEditor.refresh();
        // output
        $output.css({height: (viewportHeight - 180) + 'px' });
    }).trigger('debouncedresize');

    // Alert box
    var showAlert = function(title, text) {
        hideAlert();
        var alertElm = $(".output-pane .alert");
        alertElm.find('h4').text(title);
        alertElm.find('p').text(text);
        alertElm.show();
        if (Modernizr.opacity) {
            alertElm.addClass('in');
        }
    };

    var hideAlert = function() {
        if (Modernizr.opacity) {
            $('.output-pane .alert').removeClass('in');
        } else {
            $('.output-pane .alert').hide();
        }
    };

    // Loading
    var showLoading = function() {
        $output.empty();
        loading = setTimeout(function() {
            $('.output-pane .loading').fadeIn();
        }, 500);
    };

    var hideLoading = function() {
        if (loading) {
            clearTimeout(loading);
        }
        $('.output-pane .loading').hide();
    };

    // AJAX error
    $(document).ajaxError(function(e, jqXHR, ajaxSettings, thrownError) {
        if (jqXHR.status === 500) {
            if (data = $.parseJSON(jqXHR.responseText)) {
                setOutput(data);
                setErrorLine(data.error.line);
                $('span.time').text('');
                showAlert(data.error.message, ''); // We highlighted the line which caused this error
                var alertHeight = $(".output-pane .alert").height() + 55;
                $output.find('iframe').css({
                    marginTop: alertHeight + 'px',
                    height: ($output.height() - alertHeight) + 'px'
                });
            }
        } else if (jqXHR.status === 404) {
            hideLoading();
            showAlert('Code not found', 'Open a new document to get started');
        } else {
            hideLoading();
            showAlert('Something went wrong with your request', 'Please try again later');
        }
    });

    // Run
    var setDirty = function(val) {
        dirty = val;
        if (dirty === true) {
            $('.editor-pane button').addClass('btn-inverse');
        } else {
            $('.editor-pane button').removeClass('btn-inverse');
        }
    };

    var createIframe = function(parent, html) {
        iframeElm = $('<iframe />');
        iframeElm.attr('src', 'empty.html');
        iframeElm.attr('frameBorder', "0");
        iframeElm.appendTo(parent);
        iframeElm.on('load', function () {
            setTimeout(function() {
                iframeElm.contents().find('body').html(html);
            }, 0);
        });
    };

    var setOutput = function(data) {
        hideLoading();
        if (typeof(hash.get('h')) === 'undefined') {
            h = data.hash;
            doNotUpdateOutput = true;
            hash.add({ h: h });
            addHistory(h);
        }
        if (data.code) {
            codeEditor.setValue(atob(data.code));
        }
        if (data.time) {
            $('span.time').text(data.time + 'ms');
        }
        createIframe($output, atob(data.output));
    };

    var clearErrorLine = function() {
        codeEditor.eachLine(function(line) {
            codeEditor.removeLineClass(line, 'wrap', 'error');
        });
        codeEditor.refresh();
    };

    var setErrorLine = function(line) {
        codeEditor.addLineClass(line - 1, 'wrap', 'error');
        codeEditor.refresh();
    };

    var runFromEditor = function() {
        setDirty(false);
        hideAlert();
        clearErrorLine();
        showLoading();
        var code = codeEditor.getValue();
        var data = { code: code, hash: h };
        $.getJSON('run.php', data, setOutput);
    };

    var runFromHash = function() {
        showLoading();
        var data = { hash: h };
        $.getJSON('run.php', data, setOutput);
    }

    var scrollToResults = function() {
        var scrollTo = $('div.span6:eq(1)').offset().top;
        $('html, body').animate({
            scrollTop: scrollTo
        }, 500);
    }

    $('.editor-pane form').on('submit', function(e) {
        e.preventDefault();
        runFromEditor();
        scrollToResults();
        return false;
    });

    // refresh?
    var checkHashAndRefresh = function() {
        if (typeof(hash.get('h')) !== 'undefined') {
            h = hash.get('h');
            codeEditor.setValue('');
            runFromHash();
        }
    };
    checkHashAndRefresh();

    // local history
    var history;

    $(window).on('hashchange', function() {
        if (!doNotUpdateOutput) {
            checkHashAndRefresh();
        } else {
            doNotUpdateOutput = false;
        }
    });

    var addHistory = function(hash) {
        if (!Modernizr.localstorage) return;

        history.push(hash);
        localStorage.setItem("history", JSON.stringify(history));
        addHistoryMenu(hash);
    };

    var addHistoryMenu = function(hash, title) {
        if ($('ul.history li').length > 10) return;
        $('ul.history li.disabled').remove();

        var title = title || hash;
        var url = hash ? '#h=' + hash : 'javascript:void(null);';
        var $item = $('<li/>');
        if (!hash) { $item.addClass('disabled'); }
        var $link = $('<a/>', { 'href': url });
        $link.text(title);
        $link.appendTo($item);
        $item.prependTo('ul.history');
    };

    if (Modernizr.localstorage) {
        history = JSON.parse( localStorage.getItem("history") );
        if (history && history.length) {
            for (var i in history) {
                addHistoryMenu(history[i]);
            }
        } else {
            history = [];
            addHistoryMenu(false, 'No history yet');
        }
    } else {
        addHistoryMenu(false, 'Unavailable');
    }

};
