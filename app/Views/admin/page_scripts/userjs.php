<script>
$(document).ready(function() {
    var table = $('#orderTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('admin/manage_enquiry/orderListAjax') ?>",
            type: "POST",
            dataSrc: "data"
        },
        columns: [
            { data: "slno", className: "text-start" },
            { data: "customer_name", className: "text-start" },
            { data: "created_at", className: "text-start" },
            {
                data: "enquiry_id",
                render: function(id) {
                    return `
                        <div class="text-start">
                            <a href="<?= base_url('admin/manage_enquiry/view_enquiry/') ?>${id}" 
                            title="View" style="color:rgba(37, 41, 43, 1);">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                        </div>
                    `;
                }
            },
            { data: "enquiry_id", visible: false }
        ],
        order: [[4, 'desc']],
        columnDefs: [{ searchable: false, orderable: false, targets: [0, 3] }],
        language: { infoFiltered: "" },
        scrollX: false,
        autoWidth: false
    });

    table.on('order.dt search.dt draw.dt', function () {
        table.column(0, { search: 'applied', order: 'applied' })
            .nodes()
            .each(function (cell, i) {
                var pageInfo = table.page.info();
                cell.innerHTML = pageInfo.start + i + 1;
            });
    });
});
</script>
