Mautic.contactclientPendingEventsTable = function () {

    if ("undefined" === mQuery.fn.dataTable) {
        mQuery.getScript(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/js/datatables.min.js', function () {
            mQuery.getScript(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/js/datetime-moment.js');
            mQuery.getCssOnce(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/css/datatables.min.css');
            mQuery.getCssOnce(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/css/dataTables.fontAwesome.css');
        });
    }
    var $tableTarget = mQuery('#pending-events-table');
    if ($tableTarget.length && !$tableTarget.hasClass('table-initialized')) {
        $tableTarget.addClass('table-initialized');
        // Make ajax call
        mQuery.ajax({
            url: mauticAjaxUrl,
            type: 'POST',
            data: {
                action: 'plugin:contactClient:pendingEvents',
                objectId: Mautic.getEntityId(),
            },
            cache: false,
            dataType: 'json',
            success: function (response) {
                console.log(response.columns);
                mQuery('#PendingEventsCount').text(response.total)
                mQuery('#clientPendingEvents-builder-overlay').hide();
                mQuery('#pending-events-table').DataTable(
                    {
                        columns: response.columns,
                        data: response.events,
                        autoFill: true,
                        language: {
                            emptyTable: 'No results found for this date range and filters.'
                        },
                        columnDefs: [
                            {
                                render: function (data, type, row) {
                                    return renderCampaignLink(row[0], row[1]);
                                },
                                targets: 1
                            },
                            {
                                render: function (data, type, row) {
                                    return renderEventName(row[2], row[3]);
                                },
                                targets: 3
                            },

                            {visible: false, targets: [0, 2]},
                            {width: '33%', targets: [1,3,4]},
                        ],

                    }
                );
            } // end ajax success
        }); // end ajax call
    } // end if tableTarget exists

    function renderCampaignLink (id, name) {
        return '<a href = "/s/contactclient/view/'+id+'">'+name+'</a>';
    }

    function renderEventName (id, name) {
        return name+' ('+id+' )';
    }
};


