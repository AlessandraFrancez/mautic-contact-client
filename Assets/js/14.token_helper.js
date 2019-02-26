// Preload tokens and then run a callback when done */
Mautic.contactclientPreloadTokens = function (callback) {
    if (typeof window.JSONEditor.gettingTokens == 'undefined' || !window.JSONEditor.gettingTokens) {
        if (typeof window.JSONEditor.tokenCache === 'undefined') {
            window.JSONEditor.tokenCache = {};
        }
        if (typeof window.JSONEditor.tokenCacheTypes === 'undefined') {
            window.JSONEditor.tokenCacheTypes = {};
        }
        if (typeof window.JSONEditor.tokenCacheFormats === 'undefined') {
            window.JSONEditor.tokenCacheFormats = {};
        }
        var tokenSource = 'plugin:mauticContactClient:getTokens';
        if (typeof window.JSONEditor.tokenCache[tokenSource] != 'object' || !window.JSONEditor.tokenCache[tokenSource].length) {
            window.JSONEditor.gettingTokens = true;
            window.JSONEditor.tokenCache[tokenSource] = {};
            window.JSONEditor.tokenCacheTypes[tokenSource] = {};
            window.JSONEditor.tokenCacheFormats[tokenSource] = {};
            mQuery.ajax({
                url: mauticAjaxUrl,
                type: 'POST',
                data: {
                    action: tokenSource,
                    apiPayload: mQuery('#contactclient_api_payload:first').val(),
                    // filePayload: mQuery('#contactclient_file_payload:first').val()
                },
                cache: true,
                dataType: 'json',
                success: function (response) {
                    if (typeof response.tokens !== 'undefined') {
                        window.JSONEditor.tokenCache[tokenSource] = response.tokens;
                    }
                    if (typeof response.types !== 'undefined') {
                        window.JSONEditor.tokenCacheTypes[tokenSource] = response.types;
                    }
                    if (typeof response.formats !== 'undefined') {
                        window.JSONEditor.tokenCacheFormats[tokenSource] = response.formats;
                    }
                },
                error: function (request, textStatus, errorThrown) {
                    Mautic.processAjaxError(request, textStatus, errorThrown);
                },
                complete: function () {
                    if (typeof callback === 'function') {
                        callback();
                    }
                    window.JSONEditor.gettingTokens = false;
                }
            });
        }
        else {
            if (typeof callback === 'function') {
                callback();
            }
        }
    }
    else {
        setTimeout(function () {
            Mautic.contactclientPreloadTokens(callback);
        }, 200);
    }
};
// A date/time format helper modal to work in tandem with DateFormatHelper.php
// - deprecated
/* Mautic.contactclientTokenHelper = function (tokenSource, title, token, helper, type, field, selection) {
    // Check that we don't already have a modal running, recreate?
    mQuery('#tokenHelper').remove();

    var content = '';

    if (typeof window.JSONEditor.tokenCacheFormats[tokenSource] !== 'undefined') {
        var now = new Date();
        content += '<div class="form-group">';
        content += '    <label class="control-label" for="token-format">Formats Available:</label></br>';
        content += '    <select id="token-format" label="' + title + '" class="chosen-select">';
        var formats = window.JSONEditor.tokenCacheFormats[tokenSource];
        for (var key in formats) {
            if (formats.hasOwnProperty(key)) {
                var explain = key;
                if (key !== formats[key]) {
                    explain = formats[key] + ') (' + explain;
                }
                content += '        <option value="' + key + '|' + formats[key] + '">' + now.format(formats[key]) + ' (' + explain + ')</option>';
            }
        }
        content += '    </select>';
        content += '</div>';
        content += '<div class="form-group">';
        content += '    <label for="token-format-examples" class="hide">Examples:</label></br>';
        content += '    <ul id="token-format-examples" class="token-format-examples hide"></ul>';
        content += '</div>';
        content += '<div class="form-group">';
        content += '    <label for="token-output" class="hide">Token:</label></br>';
        content += '    <input id="token-output" type="text" class="token-output form-control hide" />';
        content += '</div>';
    }

    // @todo - If we have the token arrays we need display the modal.

    // @todo - On close do nothing.

    // @todo - If no field, do not have an apply button.

    // @todo - If there's a field, apply should update the date format of the

    // date field present and closest to the caret.
    mQuery('body')
        .append(
            '<div id="tokenHelper" class="modal modal-md hide fade in bg-white" style="left: auto !important;" role="dialog" tabindex="-1" aria-labelledby="' + title + '" aria-hidden="true">' +
            '    <div class="modal-header">' +
            '       <a class="close" data-dismiss="modal">×</a>' +
            '       <h4>' + title + '</h4>' +
            '    </div>' +
            '    <div class="modal-body modal-md" role="document">' +
            '        ' + content +
            '    </div>' +
            '    <div class="modal-footer">' +
            '       <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>' +
            '       <button type="button" class="btn btn-success btn-apply" data-dismiss="modal">Apply</button>' +
            '   </div>' +
            '</div>')
        .find('#tokenHelper:first')
        .removeClass('hide')
        .on('hidden.bs.modal', function () {
            mQuery(this).remove();
        })
        .on('show.bs.modal', function () {
            var $select = mQuery(this).find('.chosen-select');
            if ($select.length) {
                var $examples = mQuery(this).find('ul.token-format-examples:first'),
                    $exampleLabel = mQuery(this).find('label[for="token-format-examples"]:first'),
                    $tokenOutput = mQuery(this).find('input.token-output:first'),
                    $tokenOutputLabel = mQuery(this).find('label[for="token-output"]:first'),
                    tokenString = '';
                setTimeout(function () {
                    $select.change(function () {
                        var vals = $select.val().split('|'),
                            formatPHP = vals[0],
                            formatJS = vals[1],
                            examples = '',
                            now = new Date(),
                            day = now;
                        for (var d = 1; d < 10; d++) {
                            day.setDate(now.getDate() + d);
                            day.setHours(now.getHours() + d);
                            day.setMinutes(now.getMinutes() + d);
                            day.setSeconds(now.getSeconds() + d);
                            day.setMilliseconds(now.getMilliseconds() + d);
                            examples += '<li>' + day.format(formatJS) + '</li>';
                        }
                        $examples.html(examples)
                            .removeClass('hide');
                        tokenString = '{{ ' + token + ' | ' + helper + '.' + formatPHP + ' }}';
                        $tokenOutput.val(tokenString)
                            .removeClass('hide');
                        $tokenOutputLabel.removeClass('hide');
                        $exampleLabel.removeClass('hide');
                    }).chosen().trigger('change');
                }, 200);
            }
            mQuery(this).find('.btn-apply').click(function () {
                // @todo - insert/override value of the incomming $field w/ tokenString
                console.log(tokenString);
            });
        })
        .modal('show');
}; */