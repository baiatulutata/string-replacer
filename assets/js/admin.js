jQuery(document).ready(function($) {
    const table = $('#replacements-table').DataTable({
        paging: false,
        searching: true,
        ordering: false,
        columnDefs: [{
            targets: [0, 1],
            render: function (data, type, row, meta) {
                if (type === 'filter' || type === 'sort') {
                    const el = $('<div>').html(data).find('input');
                    return el.length ? el.val() : '';
                }
                return data;
            }
        }]
    });

    $('#add-row').on('click', function() {
        const uniqueKey = Date.now();
        const rowNode = table.row.add([
            `<input type="text" name="STRIRE_replacements_array[${uniqueKey}][from]" />`,
            `<input type="text" name="STRIRE_replacements_array[${uniqueKey}][to]" />`,
            `<button type="button" class="button remove-row">Remove</button>`
        ]).draw().node();

        $(rowNode).addClass('dynamic-row');
    });

    $('#replacements-table tbody').on('click', '.remove-row', function() {
        table.row($(this).closest('tr')).remove().draw();
    });
});
