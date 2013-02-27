$(document).ready(function() {

    Modernizr.load({
        test: window.btoa && window.atob,
        nope: 'js/vendor/base64.min.js',
        complete: phpTyper
    });

});

var phpTyper = function() {

    // Cache
    var $editor = $('#editor');
    var $output = $('.output');
    var $autorun = $('.editor-pane .toolbar label input');

    var doNotUpdateOutput = false;
    var h;
    var activeLine;
    var errorLine;
    var dirty = false;
    var loading;

    // Abort all AJAX events
    $.xhrPool = [];
    $.xhrPool.abortAll = function() {
        $(this).each(function(idx, jqXHR) {
            jqXHR.abort();
        });
        $.xhrPool.length = 0
    };

    $.ajaxSetup({
        beforeSend: function(jqXHR) {
            $.xhrPool.push(jqXHR);
        },
        complete: function(jqXHR) {
            var index = $.xhrPool.indexOf(jqXHR);
            if (index > -1) {
                $.xhrPool.splice(index, 1);
            }
        }
    });

    // Ace
    var editor = ace.edit('editor');
    editor.setTheme("ace/theme/monokai");
    editor.getSession().setMode("ace/mode/php");

    setTimeout(function() {
        var noDirtyChars = "\n|\t| ".split('|');
        editor.getSession().on('change', function(e) {
            if ((e.data.action === 'insertText' || e.data.action === 'removeText') && $.inArray(e.data.text, noDirtyChars) === -1) {
                setDirty(true);
                clearErrorLine();
            }
        });
    }, 5000);

    editor.getSession().selection.on('changeCursor', function(e) {
        var row = editor.selection.getCursor().row;
        if (activeLine !== row) {
            activeLine = row;
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
        $editor.css({height: (viewportHeight - 180) + 'px'});
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
            // showAlert('Something went wrong with your request', 'Please try again later');
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
            editor.setValue(atob(data.code));
            editor.gotoLine(0);
        }
        if (data.time) {
            $('span.time').text(data.time + 'ms');
        }
        createIframe($output, atob(data.output));
    };


    var clearErrorLine = function() {
        editor.getSession().removeMarker(errorLine);
    };


    var setErrorLine = function(line) {
        // debugger;
        var r = ace.require('ace/range').Range;
        theRange = new r(line - 1, 0, line, 0);
        errorLine = editor.getSession().addMarker(theRange, "ace_active_line warning", "background");
    };

    var runFromEditor = function() {
        setDirty(false);
        hideAlert();
        clearErrorLine();
        showLoading();
        var code = editor.getValue();
        var data = { code: code, hash: h };
        $.xhrPool.abortAll();
        $.getJSON('run.php', data, setOutput);
    };

    var runFromHash = function() {
        showLoading();
        var data = { hash: h };
        $.xhrPool.abortAll();
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
            editor.setValue("");
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
