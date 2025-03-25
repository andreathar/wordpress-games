<div>

    <?php include_once "widget.urheader.php";?>

    <h1 class="wp-heading-inline">Database Manager</h1>
    <a href="#" onclick='URDB.openNewTableModal()' class="page-title-action">Add new Table</a>
    <hr class="wp-header-end">

    <table class="ur-table-list">

        <thead>
            <tr>
                <td>Name</th>
                <td></td>
            </tr>
        </thead>

        <tbody data-bind="foreach: db.table">
            <tr>
                <td>
                    <div data-bind="attr: {'urlistrow':$index}" onclick='URDB.dbListAction(this, "OPEN", "<?=UNIREST_TABLE_PAGE?>")'><i class="fa-solid fa-table"></i> <span data-bind="text: name"></span></div>
                </td>
                <td>
                    <span data-bind="attr: {'urlistrow':$index}" onclick='URDB.dbListAction(this, "EDIT")' style="cursor:pointer;"><i class="fa-solid fa-pen-to-square"></i> EDIT</span> &nbsp;&nbsp;&nbsp;&nbsp;
                    <span data-bind="attr: {'urlistrow':$index}" onclick='URDB.dbListAction(this, "EMPTY")' style="cursor:pointer;"><i class="fa-solid fa-table"></i> EMPTY</span> &nbsp;&nbsp;&nbsp;&nbsp;
                    <span data-bind="attr: {'urlistrow':$index}" onclick='URDB.dbListAction(this, "DELETE")' style="color:darkred;cursor:pointer;"><i class="fa-solid fa-trash"></i> DELETE</span>
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
     URDB.onStart();
</script>

<div id="ur-modal-newtable" class="ur-form" title="TABLE STRUCTURE">

    <div>
        <label><b>Table Name</b></label>
        <input style="width:80%;margin-top: 10px;background-color: #f8fcff;" type="text" data-bind="textInput: structure.name" validate/>
    </div>

    <div class="row-h" style="margin-top: 20px;font-weight: bold;margin-bottom: 8px;">
        <div style="width:14px"></div>
        <div class="w30">Column Name</div>
        <div class="w20">Data Type</div>
        <div class="w20">Data Size</div>
        <div class="w10">Allow NULL</div>
        <div></div>
    </div>

    <ul id="ur-columns-list" data-bind="foreach: structure.row" style="margin: 0;">
        <li>
            <div class="row-h" data-bind="attr: {'urntrow':$index}">
                <div data-bind="attr: {'urntmover':$index}" style="width:14px"><i class="fa-solid fa-bars"></i></div>
                <div class="w30"><input class="w90" type="text" data-bind="textInput: name" validate></div>
                <div class="w20">
                    <select class="w90" data-bind="value: type, attr: {'index':$index}" onchange="URDB.onTypeChange(this)">
                        <option value="INT">INT</option>
                        <option value="BIGINT">BIGINT</option>
                        <option value="FLOAT">FLOAT</option>
                        <option value="DOUBLE">DOUBLE</option>
                        <option value="DECIMAL">DECIMAL</option>
                        <option value="TEXT">TEXT</option>
                        <option value="VARCHAR">VARCHAR</option>
                        <option value="CHAR">CHAR</option>
                        <option value="DATETIME">DATETIME</option>
                        <option value="DATE">DATE</option>
                        <option value="TIME">TIME</option>
                        <option value="TIMESTAMP">TIMESTAMP</option>
                        <option value="YEAR">YEAR</option>
                        <option value="BLOB">BLOB</option>
                        <option value="TINYBLOB">TINYBLOB</option>
                        <option value="MEDIUMBLOB">MEDIUMBLOB</option>
                        <option value="LONGBLOB">LONGBLOB</option>
                        <option value="TINYINT">TINYINT</option>
                        <option value="SMALLINT">SMALLINT</option>
                        <option value="MEDIUMINT">MEDIUMINT</option>
                        <option value="BIT">BIT</option>
                    </select>
                </div>
                <div class="w20"><input class="w90" type="text" data-bind="textInput: size, enable: showSize"></div>
                <div class="w10"><input type="checkbox" data-bind="checked: canBeNull"> NULL</div>
                <div style="width: 10%;text-align: right;">
                    <div data-bind="attr: {'index':$index}" onclick="URDB.removeItem(this)" style="cursor:pointer;color:darkred;"><i class="fa-solid fa-trash"></i> DELETE</div>
                </div>
                <div class="cover" data-bind="visible: readonly"></div>
            </div>
        </li>
    </ul>

    <button class="ur-btn ur-btn-blue" style="margin-top:20px;" onclick="URDB.addNewColumn()">ADD COLUMN</button>

</div>