Mautic.contactclientEventsDatatable = function () {
    var $sourceTarget = mQuery('#contactClientEventsTable');
    if ($sourceTarget.length && typeof tableData !== 'undefined') {
        mQuery('#contactClientEventsTable:first:not(.table-initialized)').addClass('table-initialized').each(function () {
            // dependent files loaded, now get the data and render
            var sortCol = (tableData.labels[1] ? 1 : 0);
            mQuery('#contactClientEventsTable').DataTable({
                language: {
                    emptyTable: 'No results found for this date range and filters.'
                },
                data: tableData.data,
                autoFill: true,
                columns: tableData.labels,
                //bSort : false,
                order: [sortCol, 'asc'],
                bLengthChange: true,
                dom: '<<lBf>rtip>',
                buttons: [
                    'excelHtml5',
                    'csvHtml5'
                ],
                footerCallback: function (row, data, start, end, display) {
                    if (data && data.length === 0 || typeof data[0] === 'undefined') {
                        mQuery('#contactClientEventsTable').hide();
                        return;
                    }
                    try {
                        // Add table footer if it doesnt exist
                        var container = mQuery('#contactClientEventsTable');
                        var columns = data[0].length;
                        if (mQuery('tr.pageTotal').length === 0) {
                            var footer = mQuery('<tfoot></tfoot>');
                            var tr = mQuery('<tr class=\'pageTotal\' style=\'font-weight: 600; background: #fafafa;\'></tr>');
                            var tr2 = mQuery('<tr class=\'grandTotal\' style=\'font-weight: 600; background: #fafafa;\'></tr>');
                            tr.append(mQuery('<td colspan=\'1\'>Page totals</td>'));
                            tr2.append(mQuery('<td colspan=\'1\'>Grand totals</td>'));
                            for (var i = 1; i < columns; i++) {
                                tr.append(mQuery('<td class=\'td-right\'></td>'));
                                tr2.append(mQuery('<td class=\'td-right\'></td>'));
                            }
                            footer.append(tr);
                            footer.append(tr2);
                            container.append(footer);
                        }

                        var api = this.api();

                        // Remove the formatting to get
                        // integer data for summation
                        var intVal = function (i) {
                            return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                        };

                        var total = mQuery('#' + container[0].id + ' thead th').length;
                        var footer1 = mQuery(container).find('tfoot tr:nth-child(1)');
                        var footer2 = mQuery(container).find('tfoot tr:nth-child(2)');
                        for (var i = 1; i < total; i++) {
                            var pageSum = api
                                .column(i, {page: 'current'})
                                .data()
                                .reduce(function (a, b) {
                                    return intVal(a) + intVal(b);
                                }, 0);
                            var sum = api
                                .column(i)
                                .data()
                                .reduce(function (a, b) {
                                    return intVal(a) + intVal(b);
                                }, 0);
                            footer1.find('td:nth-child(' + (i + 1) + ')').html(pageSum);
                            footer2.find('td:nth-child(' + (i + 1) + ')').html(sum);
                        }
                        mQuery('#global-builder-overlay').hide();

                    }
                    catch (e) {
                        console.log(e);
                    }
                } // FooterCallback
            });
            mQuery('#contactClientEventsTable_wrapper .dt-buttons').css({
                float: 'right',
                marginLeft: '10px'
            });
        });
    }
};