window.addEventListener('load', function () {
    window.objectcache.groups.init();
    window.objectcache.latency.init();
    window.objectcache.flushlog.init();
});

jQuery.extend(window.objectcache, {
    latency: {
        init: function () {
            this.fetchData();
            setInterval(this.fetchData, 10000);
        },

        fetchData: function () {
            jQuery
                .ajax({
                    url: objectcache.rest.url + 'objectcache/v1/latency',
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', objectcache.rest.nonce);
                    },
                })
                .done(function (data, status, xhr) {
                    objectcache.rest.nonce = xhr.getResponseHeader('X-WP-Nonce') ?? objectcache.rest.nonce;

                    var widget = document.querySelector('.objectcache\\:latency-widget');

                    var table = widget.querySelector('table');
                    table && widget.removeChild(table);

                    var error = widget.querySelector('.error');
                    error && widget.removeChild(error);

                    table = document.createElement('table');
                    widget.prepend(table);

                    var content = '';

                    var formatLatency = function (us) {
                        if (us < 500) return '<strong>' + us + '</strong> μs';
                        if (us < 1000) return '<strong class="warning">' + us + '</strong> μs';
                        return '<strong class="error">' + Math.round((us / 1000 + Number.EPSILON) * 100) / 100 + '</strong> ms';
                    };

                    data.forEach(function (item) {
                        content += '<tr>';
                        content += '  <td>' + item.url + '</td>';
                        content += '  <td>';
                        content += item.error ? '<span class="error">' + item.error + '</span>' : formatLatency(item.latency);
                        content += '  </td>';
                        content += '</tr>';
                    });

                    document.querySelector('.objectcache\\:latency-widget table').innerHTML = content;
                })
                .fail(function (error) {
                    var widget = document.querySelector('.objectcache\\:latency-widget');

                    var table = widget.querySelector('table');
                    table && widget.removeChild(table);

                    var container = widget.querySelector('.error');

                    if (! container) {
                        container = document.createElement('p');
                        container.classList.add('error');

                        widget.append(container);
                    }

                    if (error.responseJSON && error.responseJSON.message) {
                        container.textContent = error.responseJSON.message;
                    } else {
                        container.textContent = 'Request failed (' + error.status + ').';
                    }
                });
        },
    },

    groups: {
        init: function () {
            document.querySelector('.objectcache\\:groups-widget button')
                .addEventListener('click', window.objectcache.groups.fetchData);

            document.querySelector('.objectcache\\:groups-widget')
                .addEventListener('click', window.objectcache.groups.flushGroup);

            if (! ClipboardJS.isSupported()) {
                return;
            }

            var widget = document.querySelector('.objectcache\\:groups-widget');
            var copyButton = widget.querySelector('.button[data-clipboard-target]');
            var copyText = widget.querySelector('.button[data-clipboard-target] + span');
            var clipboard = new ClipboardJS(copyButton);

            clipboard.on('success', function (event) {
                event.clearSelection();
                copyButton.classList.add('hidden');
                copyText.classList.remove('hidden');

                setTimeout(function () {
                    copyText.classList.add('hidden');
                    copyButton.classList.remove('hidden');
                }, 3000);
            });

            clipboard.on('error', function (event) {
                event.clearSelection();

                window.alert('Sorry, something went wrong.');
            });
        },

        fetchData: function () {
            var widget = document.querySelector('.objectcache\\:groups-widget');

            var button = widget.querySelector('.button');
            button.blur();
            button.classList.add('disabled');
            button.textContent = button.dataset.loading;

            var copy = widget.querySelector('.button[data-clipboard-target]');
            copy.classList.add('hidden');

            var container = widget.querySelector('.table-container');
            container && widget.removeChild(container);

            var error = widget.querySelector('.error');
            error && widget.removeChild(error);

            var title = document.querySelector('#objectcache_groups .hndle');

            if (title) {
                if ('label' in title.dataset) {
                    title.textContent = title.dataset.label;
                } else {
                    title.dataset.label = title.textContent;
                }
            }

            jQuery
                .ajax({
                    url: objectcache.rest.url + 'objectcache/v1/groups',
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', objectcache.rest.nonce);
                    },
                })
                .done(function (data, status, xhr) {
                    objectcache.rest.nonce = xhr.getResponseHeader('X-WP-Nonce') ?? objectcache.rest.nonce;

                    var info = widget.querySelector('p:first-child');
                    info && widget.removeChild(info);

                    var container = document.createElement('div');
                    container.classList.add('table-container');
                    widget.prepend(container);

                    var table = document.createElement('table');
                    container.prepend(table);

                    var escapeHtml = function (text) {
                        var div = document.createElement('div');
                        div.innerText = text;

                        return div.innerHTML.replace(/"/g, '&quot;').replace(/'/g, '&apos;');
                    };

                    var content = '';

                    if (data.length) {
                        title.textContent = title.dataset.label + ' (' + data.length + ')';
                        title.dataset.count = data.length;

                        data.forEach(function (item) {
                            content += '<tr title="' + item.count + ' objects found in `' + escapeHtml(item.group) + '` group">';
                            content += '  <td data-group="' + item.group + '">';
                            content += '    <span class="group-name">' + escapeHtml(item.group) + '</span>';
                            content += '    <button class="objectcache:flush-group button-link">Flush</button>';
                            content += '  </td>';
                            content += '  <td>';
                            content += '    <strong>' + item.count + '</strong>';
                            content += '  </td>';
                            content += '</tr>';
                        });

                        ClipboardJS.isSupported() && copy.classList.remove('hidden');
                    } else {
                        content += '<tr>';
                        content += '  <td colspan="2">No cache groups found.</td>';
                        content += '</tr>';
                    }

                    table.innerHTML = content;
                })
                .fail(function (error) {
                    var container = widget.querySelector('.error');

                    if (! container) {
                        container = document.createElement('p');
                        container.classList.add('error');

                        widget.append(container);
                    }

                    if (error.responseJSON && error.responseJSON.message) {
                        container.textContent = error.responseJSON.message;
                    } else {
                        container.textContent = 'Request failed (' + error.status + ').';
                    }
                })
                .always(function () {
                    var button = widget.querySelector('.objectcache\\:groups-widget .button');
                    button.textContent = button.dataset.text;
                    button.classList.remove('disabled');
                });
        },

        flushGroup: function (event) {
            event.preventDefault();

            if (! event.target.classList.contains('objectcache:flush-group')) {
                return;
            }

            var table = event.target.closest('table');

            if (table.classList.contains('busy')) {
                return;
            }

            table.classList.add('busy');

            event.target.disabled = true;

            var groupLabel = event.target.previousElementSibling;

            groupLabel.classList.remove('error');
            groupLabel.textContent = 'Flushing...';

            jQuery
                .ajax({
                    type: 'DELETE',
                    url: objectcache.rest.url + 'objectcache/v1/groups',
                    data: {
                        group: event.target.parentElement.dataset.group,
                    },
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', objectcache.rest.nonce);
                    },
                })
                .done(function(data, status, xhr) {
                    objectcache.rest.nonce = xhr.getResponseHeader('X-WP-Nonce') ?? objectcache.rest.nonce;

                    var title = document.querySelector('#objectcache_groups .hndle');
                    title.dataset.count = title.dataset.count - 1;

                    title.textContent = title.dataset.label + ' (' + title.dataset.count + ')';

                    event.target.closest('tr').remove();
                })
                .fail(function (error) {
                    groupLabel.classList.add('error');

                    if (error.responseJSON && error.responseJSON.message) {
                        groupLabel.textContent = error.responseJSON.message;
                    } else {
                        groupLabel.textContent = 'Request failed (' + error.status + ').';
                    }

                    setTimeout(function() {
                        groupLabel.classList.remove('error');
                        groupLabel.textContent = groupLabel.parentElement.dataset.group;
                    }, 3000);
                })
                .always(function () {
                    table.classList.remove('busy');
                    event.target.disabled = false;
                });
        }
    },

    flushlog: {
        init: function () {
            var inputs = document.querySelectorAll('.objectcache\\:flushlog-widget input');

            if (inputs) {
                for (var i = 0; i < inputs.length; i++) {
                    inputs[i].addEventListener('click', window.objectcache.flushlog.save);
                }
            }
        },

        save: function (event) {
            event.target.disabled = true;

            jQuery
                .ajax({
                    type: 'POST',
                    url: objectcache.rest.url + 'objectcache/v1/options',
                    data: {
                        [event.target.name]: event.target.checked ? 1 : 0,
                    },
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', objectcache.rest.nonce);
                    },
                })
                .fail(function (error) {
                    if (error.responseJSON && error.responseJSON.message) {
                        window.alert(error.responseJSON.message);
                    } else {
                        window.alert('Request failed (' + error.status + ').');
                    }
                })
                .always(function () {
                    event.target.disabled = false;
                });
        },
    },
});
