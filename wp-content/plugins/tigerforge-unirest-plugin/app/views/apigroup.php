<div>

    <?php include_once "widget.urheader.php";?>

    <h1 class="wp-heading-inline">API Manager</h1>
    <a href="#" onclick='URAPIGROUP.addNewAPIGroup()' class="page-title-action">Add new API Group</a>
    <hr class="wp-header-end">

    <table id="ur-apigroup-list" class="ur-table-list">

        <thead>
            <tr>
                <td>Group name</th>
                <td></td>
            </tr>
        </thead>

        <tbody data-bind="foreach: db.table">
            <tr>
                <td>
                    <div data-bind="attr: {'rowID':id, 'rowIndex':$index}" onclick='URAPIGROUP.APIGroupAction(this, "OPEN", "<?=UNIREST_API_PAGE?>")'><i class="fa-solid fa-layer-group"></i> <span data-bind="text: name"></span></div>
                </td>
                <td>
                    <span data-bind="attr: {'rowID':id}" onclick='URAPIGROUP.APIGroupAction(this, "RENAME")' style="cursor:pointer;"><i class="fa-solid fa-pen-to-square"></i> RENAME</span> &nbsp;&nbsp;&nbsp;&nbsp;
                    <span data-bind="attr: {'rowID':id}" onclick='URAPIGROUP.APIGroupAction(this, "EMPTY")' style="cursor:pointer;"><i class="fa-solid fa-layer-group"></i> EMPTY</span> &nbsp;&nbsp;&nbsp;&nbsp;
                    <span data-bind="attr: {'rowID':id}" onclick='URAPIGROUP.APIGroupAction(this, "DELETE")' style="color:darkred;cursor:pointer;"><i class="fa-solid fa-trash"></i> DELETE</span>
                </td>
            </tr>
        </tbody>

        <tfoot>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </tfoot>

    </table>

</div>

<script>
     URAPIGROUP.onStart();
</script>