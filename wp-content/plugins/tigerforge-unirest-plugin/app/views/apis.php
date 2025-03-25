<div>

    <?php include_once "widget.urheader.php";?>

    <h1 class="wp-heading-inline"><i class="ur-back-btn fa-regular fa-circle-left" onclick="history.back()"></i> API <span data-bind="text: group().name"></span>/...</h1>
    <a href="#" onclick='URAPI.createNewAPI()' class="page-title-action">Add new API</a>
    <hr class="wp-header-end">

    <table class="wp-list-table widefat fixed striped" style="margin-top:20px;">
        <thead>
            <tr>
                <th style="width: 40px;">Type</th>
                <th>Name</th>
                <th>Operations</th>
                <th style="width: 150px;"></th>
            </tr>
        </thead>
        <tbody data-bind="foreach: createdApis">
            <tr>
                
                <td style="vertical-align:middle;">
                    <span data-bind="visible: isSQL()" style="color: darkorange;">SQL</span>
                    <span data-bind="visible: isPHP()" style="color: purple;">PHP</span>
                </td>

                <td style="vertical-align:middle;">
                    <div data-bind="attr: { 'apiid': id }" onclick="URAPI.showAPI(this)" style="cursor:pointer;color: #2271b1;">
                        <span data-bind="text: $parent.group().name"></span><span>/</span><span style="font-weight:bold" data-bind="text: name"></span>
                        <br>
                        <span style="font-size:10px; color:#777;line-height: 10px;" data-bind="text: description"></span>
                    </div>
                </td>
                <td style="vertical-align:middle;">
                    <span data-bind="visible: isSQL(), text: operationsList"></span>
                    <span data-bind="visible: isPHP()">PHP Script execution</span>
                </td>
                <td style="vertical-align:middle;">
                    <span data-bind="attr: { 'apiid': id }" onclick="URAPI.APIaction(this, 'EDIT')" style="cursor:pointer;"><i class="fa-solid fa-pen-to-square"></i> EDIT</span> &nbsp;&nbsp;&nbsp;&nbsp;
                    <span data-bind="attr: { 'apiid': id }" onclick="URAPI.APIaction(this, 'DELETE')" style="color:darkred;cursor:pointer;"><i class="fa-solid fa-trash"></i> DELETE</span>
                </td>
            </tr>

            <tr api-form style="display:none;">
                <td colspan="4">...</td>
            </tr>

            <!-- TR per forzare la colorazione alternata delle righe -->
            <tr style="display:none;">
                <td colspan="4"></td>
            </tr>
        </tbody>
    </table>

</div>

<script>
     URAPI.onStart();
</script>


<div id="ur-api-container" style="display:none;">
    <div id="ur-api-form" data-bind="with: api">

        <div class="title" data-bind="text: name"></div>

        <div id="ur-ui-sql" data-bind="visible: isSQL">

            <div class="header">

                <div>
                    <div class="label">
                        <div>Table</div>
                        <div>The table this API has to work with.</div>
                    </div>
                    <div>
                        <select id="tableSelect"
                                data-bind="value: tableName,
                                        options: $parent.tables,
                                        optionsText: function(table) { return table; },
                                        optionsCaption: 'Select a table',
                                        event: { change: confirmTableChange }">
                        </select>
                    </div>
                </div>

                <div>
                    <div class="label">
                        <div>Operations</div>
                        <div>The operations this API can perform with the selected Table.</div>
                    </div>
                    <div>
                        <div class="operations" data-bind="visible: tableName">
                            <label><input type="checkbox" data-bind="checked: canRead" onchange="URAPI.onchange();"> Can <b>Read</b></label>
                            <label><input type="checkbox" data-bind="checked: canWrite" onchange="URAPI.onchange();"> Can <b>Write</b></label>
                            <label><input type="checkbox" data-bind="checked: canUpdate" onchange="URAPI.onchange();"> Can <b>Update</b></label>
                            <label><input type="checkbox" data-bind="checked: canDelete" onchange="URAPI.onchange();"> Can <b>Delete</b></label>
                        </div>
                    </div>
                </div>

            </div>

            <div class="box ln-read" id="ur-ui-sql-read" data-bind="visible: canRead">
                <div class="h3 read"><i class="fa-regular fa-eye"></i> Read</div>
                <div class="allow-write">
                    <input type="checkbox" data-bind="checked: readIsExistCheck"> Work as records existence check <a href="#" onclick="URAPI.help(1)"><i class="fa-regular fa-circle-question"></i></a>
                </div>
                <div class="config">
                    <column-select params="tableColumns: tableColumns, selectedColumns: read_columns"></column-select>
                    <condition-builder params="tableColumns: tableColumns, conditions: readConditions, logicalOperator: readLogicalOperator"></condition-builder>
                </div>
                <div class="custom-query">
                    <label for="customQueryRead">Custom Read Query</label>
                    <div class="desc">Type your custom Read SQL-query following the required format. <a href="#" onclick="URAPI.help(31)"><i class="fa-regular fa-circle-question"></i></a></div>
                    <!-- <textarea id="customQueryRead" data-bind="value: read_custom_query" placeholder="Enter read query"></textarea> -->
                     <div class="cq-editor">
                        <div id="ur-cq-read" style="font-size:16px;width:100%;height:50px"></div>
                    </div>
                </div>
            </div>

            <div class="box ln-write" id="ur-ui-sql-write" data-bind="visible: canWrite">
                <div class="h3 write"><i class="fa-solid fa-pen"></i> Write</div>
                <div class="config">
                    <column-select params="tableColumns: tableColumns, selectedColumns: write_columns"></column-select>
                </div>
                <div class="custom-query">
                    <label for="customQueryWrite">Custom Write Query</label>
                    <div class="desc">Type your custom Write SQL-query following the required format. <a href="#" onclick="URAPI.help(32)"><i class="fa-regular fa-circle-question"></i></a></div>
                    <!-- <textarea id="customQueryWrite" data-bind="value: write_custom_query" placeholder="Enter write query"></textarea> -->
                    <div class="cq-editor">
                        <div id="ur-cq-write" style="font-size:16px;width:100%;height:50px"></div>
                    </div>
                </div>
            </div>

            <div class="box ln-update" id="ur-ui-sql-update" data-bind="visible: canUpdate">
                <div class="h3 update"><i class="fa-solid fa-arrows-rotate"></i> Update</div>
                <div class="allow-write">
                    <input type="checkbox" data-bind="checked: updateCanWrite"> Allow Write on Update <a href="#" onclick="URAPI.help(2)"><i class="fa-regular fa-circle-question"></i></a>
                </div>
                <div class="config">
                    <column-select params="tableColumns: tableColumns, selectedColumns: update_columns"></column-select>
                    <condition-builder params="tableColumns: tableColumns, conditions: updateConditions, logicalOperator: updateLogicalOperator"></condition-builder>
                </div>
                <div class="custom-query">
                    <label for="customQueryUpdate">Custom Update Query</label>
                    <div class="desc">Type your custom Update SQL-query following the required format. <a href="#" onclick="URAPI.help(33)"><i class="fa-regular fa-circle-question"></i></a></div>
                    <!-- <textarea id="customQueryUpdate" data-bind="value: update_custom_query" placeholder="Enter update query"></textarea> -->
                    <div class="cq-editor">
                        <div id="ur-cq-update" style="font-size:16px;width:100%;height:50px"></div>
                    </div>
                </div>
            </div>

            <div class="box ln-delete" id="ur-ui-sql-delete" data-bind="visible: canDelete">
                <div class="h3 delete"><i class="fa-solid fa-trash"></i> Delete</div>
                <div class="config">
                    <condition-builder params="tableColumns: tableColumns, conditions: deleteConditions, logicalOperator: deleteLogicalOperator"></condition-builder>
                </div>
                <div class="custom-query">
                    <label for="customQueryDelete">Custom Delete Query</label>
                    <div class="desc">Type your custom Delete SQL-query following the required format. <a href="#" onclick="URAPI.help(34)"><i class="fa-regular fa-circle-question"></i></a></div>
                    <!-- <textarea id="customQueryDelete" data-bind="value: delete_custom_query" placeholder="Enter delete query"></textarea> -->
                    <div class="cq-editor">
                        <div id="ur-cq-delete" style="font-size:16px;width:100%;height:50px"></div>
                    </div>
                </div>
            </div>

        </div>

        <div id="ur-ui-php" data-bind="visible: isPHP">
            <div class="box ln-update" id="ur-ui-sql-update">
                <div class="h3 update" style="width:140px;"><i class="fa-brands fa-php"></i> SCRIPT</div>
                <div id="ur-php-script" style="height:500px;"></div>
                <div id="ur-php-infos">
                    <div>INSTRUCTIONS</div>
                    <div style="margin-bottom: 4px;"><i>Have a look at the online manual for details.</i></div>
                    <div>• Your PHP Script must start with the <b>&lt;?php</b> tag.</div>
                    <div>• Use <b>$UniREST->data</b> to read data (<i>associative array</i>) received from Unity.</div>
                    <div>• Use <b>$UniREST->sendReply()</b> method to send data to Unity.</div>
                    <div>• Use <b>$UniREST->sendError()</b> method to send an error message to Unity.</div>
                </div>
            </div>
        </div>

        <div class="save">
            <button class="ur-btn ur-btn-blue" style="width: 100px;font-weight: bold;" onclick="URAPI.save()">SAVE</button>
        </div>

    </div>
</div>




<div id="ur-modal-newapi" class="ur-form ur-wp-settings" title="API" style="display:none;">
    <table>
        <!-- Prima riga: Nome -->
        <tr>
            <td>
                <span>NAME</span><br>
                <span>The name of this API.</span>
            </td>
            <td>
                <input validate type="text" id="tfurapi-name" /><br>
                <span>The name must contain lowercase letters and numbers only. The firt char must be a letter.</span>
            </td>
        </tr>

        <!-- Seconda riga: Descrizione -->
        <tr>
            <td>
                <span>DESCRIPTION</span><br>
                <span>A brief description about this API.</span>
            </td>
            <td>
                <input type="text" id="tfurapi-description" maxlength="250"/><br>
                <span>The text can't contain more than 250 characters.</span>
            </td>
        </tr>

        <!-- Terza riga: Tipo (SQL o PHP) -->
        <tr>
            <td>
                <span>TYPE</span><br>
                <span>The type of this API.</span>
            </td>
            <td>
                <input type="radio" id="tfurapi-type-sql" name="tfurapi-type" value="SQL" checked/>
                <span><b>SQL</b></span><br>
                <span>This API will operate in your Database.</span>
                <br><br>
                <input type="radio" id="tfurapi-type-php" name="tfurapi-type" value="PHP" />
                <span><b>PHP</b></span><br>
                <span>This API will execute a custom PHP Script.</span>
            </td>
        </tr>
    </table>
</div>

