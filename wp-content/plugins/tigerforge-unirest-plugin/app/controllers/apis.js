var URAPI = {

    VM: null,

    curremtGroupID: 0,
    curremtAPIID: 0,
    currentAPIjson: null,

    currentAPIForm: null,

    editMode: false,
    editor: null,

    cqReadEditor: null,
    cqWriteEditor: null,
    cqUpdateEditor: null,
    cqDeleteEditor: null,

    db: {},

    ApiModel: function (data) {
        var self = this;

        self.id = ko.observable(data.id || null);
        self.name = ko.observable(data.name || '');
        self.type = ko.observable(data.type || '');
        self.tableName = ko.observable(data.info.tableName || '');
        self.description = ko.observable(data.info.description || '');
        self.isSQL = ko.observable(data.info.isSQL || false);
        self.isPHP = ko.observable(data.info.isPHP || false);
        self.canRead = ko.observable(data.info.canRead || false);
        self.canWrite = ko.observable(data.info.canWrite || false);
        self.canUpdate = ko.observable(data.info.canUpdate || false);
        self.canDelete = ko.observable(data.info.canDelete || false);
        self.readLogicalOperator = ko.observable(data.info.read_logical_operator || 'AND');
        self.updateLogicalOperator = ko.observable(data.info.update_logical_operator || 'AND');
        self.deleteLogicalOperator = ko.observable(data.info.delete_logical_operator || 'AND');
        self.updateCanWrite = ko.observable(data.info.update_can_write || false);
        self.readIsExistCheck = ko.observable(data.info.read_is_existcheck || false);

        self.tableColumns = ko.observableArray([]);
        self.previousTableName = null;

        // Funzione per confermare il cambio di tabella
        self.confirmTableChange = function (newTableName) {
            if (self.previousTableName != null) {
                JS.ask("TABLE CHANGE", "Changing your table may require the reconfiguration of your API. Do you want to procede?", () => {
                    self.previousTableName = newTableName;
                    self.loadTableColumns(newTableName.tableName());
                }, "warning", () => {
                    self.tableName(self.previousTableName);
                    console.log(self.previousTableName)
                });
            } else {
                self.previousTableName = newTableName;
                self.loadTableColumns(newTableName.tableName());
            }
        };

        // Carica le colonne per la tabella selezionata
        self.loadTableColumns = function (tableName) {

            if (JS.isNotValid(tableName)) {
                self.tableColumns([]);
                self.resetModel();
            }
            else {
                self.tableColumns(URAPI.db[tableName]);
            }
        };
        if (self.tableName() != "") self.loadTableColumns(self.tableName());

        self.read_custom_query = ko.observable(data.info.read_custom_query || "");
        self.write_custom_query = ko.observable(data.info.write_custom_query || "");
        self.update_custom_query = ko.observable(data.info.update_custom_query || "");
        self.delete_custom_query = ko.observable(data.info.delete_custom_query || "");

        self.read_columns = ko.observableArray(data.info.read_columns || []);
        self.write_columns = ko.observableArray(data.info.write_columns || []);
        self.update_columns = ko.observableArray(data.info.update_columns || []);

        self.readConditions = ko.observableArray(data.info.readConditions || []);
        self.updateConditions = ko.observableArray(data.info.updateConditions || []);
        self.deleteConditions = ko.observableArray(data.info.deleteConditions || []);

        self.operationsList = ko.computed(function () {
            if (self.isPHP()) return '';
            let operations = [];
            if (self.canRead()) operations.push('Read');
            if (self.canWrite()) operations.push('Write');
            if (self.canUpdate()) operations.push('Update');
            if (self.canDelete()) operations.push('Delete');
            return operations.join(', ');
        });

        self.resetModel = function () {
            self.tableName('');
            self.canRead(false);
            self.canWrite(false);
            self.canUpdate(false);
            self.canDelete(false);

            self.read_custom_query('');
            self.write_custom_query('');
            self.update_custom_query('');
            self.delete_custom_query('');

            self.readLogicalOperator('AND');
            self.updateLogicalOperator('AND');
            self.deleteLogicalOperator('AND');
        };

        self.toJSON = function () {
            return ko.toJS({
                id: self.id(),
                name: self.name(),
                type: self.type(),
                tableName: self.tableName(),
                description: self.description(),
                isSQL: self.isSQL(),
                isPHP: self.isPHP(),
                canRead: self.canRead(),
                canWrite: self.canWrite(),
                canUpdate: self.canUpdate(),
                canDelete: self.canDelete(),
                read_custom_query: self.read_custom_query(),
                write_custom_query: self.write_custom_query(),
                update_custom_query: self.update_custom_query(),
                delete_custom_query: self.delete_custom_query(),
                php_script: self.php_script(),
                tableColumns: self.tableColumns(),
                read_columns: self.read_columns(),
                write_columns: self.write_columns(),
                update_columns: self.update_columns(),
                readConditions: self.readConditions(),
                updateConditions: self.updateConditions(),
                deleteConditions: self.deleteConditions(),
                read_logical_operator: self.readLogicalOperator(),
                update_logical_operator: self.updateLogicalOperator(),
                delete_logical_operator: self.deleteLogicalOperator(),
                update_can_write: self.updateCanWrite(),
                read_is_existcheck: self.readIsExistCheck(),
            });
        };

        self.php_script = ko.observable(data.info.php_script || "<?php\n\n// Your PHP code here!\n");

    },

    AppViewModel: function () {
        var self = this;

        self.group = ko.observable({});
        self.createdApis = ko.observableArray([]);
        self.tables = ko.observableArray([]);

        self.api = ko.observable(new URAPI.ApiModel({
            id: 1,
            name: 'testAPI',
            type: 'SQL',
            info: {
                tableName: '',
                description: 'Test description',
                isSQL: true,
                isPHP: false,
                canRead: false,
                canWrite: false,
                canUpdate: false,
                canDelete: false
            }
        }));

    },

    onStart: function () {

        var ME = this;

        Q.ready(() => {

            ME.apiModal();

            ME.VM = new ME.AppViewModel();
            ko.applyBindings(ME.VM);

            ME.initializeComponents();

            ME.showAPIsList();

        });

    },

    save: function () {

        var ME = this;

        var jsonData = ME.VM.api().toJSON();
        console.log(jsonData);

        var isTableSelected = jsonData.tableName && jsonData.tableName !== '';
        var hasAtLeastOnePermission = jsonData.canRead || jsonData.canWrite || jsonData.canUpdate || jsonData.canDelete;

        if (jsonData.isSQL && (!isTableSelected || !hasAtLeastOnePermission)) {
            JS.alert("warning", "SAVE API", "Before saving, you have to select a Table and at least one Operation.");
            return;
        }

        if (jsonData.isPHP && jsonData.php_script.toLowerCase().trim().indexOf("<?php") != 0) {
            JS.alert("warning", "'&lt;?php' TAG MISSING", "Your PHP Script must start with the <b>&lt;?php</b> tag.");
            return;
        }

        UNIREST.loader();
        UNIREST.api("apis_action", { action: "API_SAVE", name: ME.curremtGroupID, extra: jsonData })
            .then((result) => {

                console.log("SAVE", result);

                JS.ask(
                    "API SAVED", 
                    "Your API has been saved.<br><br><i>You may have to update the Unity API system to activate your changes. You can do it now.</i>", 
                    () => {},
                    "success",
                    () => {
                        URUNITY.generate(true);
                    },
                    "OK", "UPDATE UNITY API SYSTEM"
                );

                ME.cloneJsonData();

                UNIREST.loader(false);

            })
            .catch((error) => { ME.catchError(error, "[004]") });

    },

    jsonDataChanged: function () {
        var ME = this;

        if (JS.isNotValid(ME.VM) || JS.isNotValid(ME.currentAPIjson)) return false;

        var jsonData = ME.VM.api().toJSON();

        return JSON.stringify(jsonData) != ME.currentAPIjson;
    },

    cloneJsonData: function () {
        var ME = this;

        ME.currentAPIjson = JSON.stringify(ME.VM.api().toJSON());
    },

    showAPI: function (e) {
        var ME = this;

        if (ME.jsonDataChanged()) {
            JS.ask("SAVE API", "The API configuration has been modified. Do you want to save it?", () => {
                ME.save();
                ME.loadAPI(e);
            }, "warning", () => {
                ME.loadAPI(e);
            });
        }
        else {
            ME.loadAPI(e);
        }
    },

    loadAPI: function (e) {
        var ME = this;

        var apiID = parseInt($(e).attr("apiid"));

        if (apiID == ME.curremtAPIID) return;
        ME.curremtAPIID = apiID;

        if (ME.editor != null) ME.editor.destroy();
        if (ME.cqReadEditor != null) ME.cqReadEditor.destroy();
        if (ME.cqWriteEditor != null) ME.cqWriteEditor.destroy();
        if (ME.cqUpdateEditor != null) ME.cqUpdateEditor.destroy();
        if (ME.cqDeleteEditor != null) ME.cqDeleteEditor.destroy();

        UNIREST.loader();
        UNIREST.api("apis_action", { action: "API_GET", name: apiID, extra: "" })
            .then((result) => {

                console.log("API", result);

                result.data.info = JSON.parse(result.data.info);

                ME.updateConditions(result.data.info.readConditions);
                ME.updateConditions(result.data.info.updateConditions);
                ME.updateConditions(result.data.info.deleteConditions);

                const api = new ME.ApiModel(result.data);
                ME.VM.api(api);

                var tr = $(e).closest("tr");
                ME.currentAPIForm = tr.next('[api-form]');
                ME.apiForm("SHOW");

                var td = ME.currentAPIForm.find("td");
                td.html("");
                $('#ur-api-container').show().appendTo(td);

                ME.cloneJsonData();

                UNIREST.loader(false);

                if (result.data.info.isPHP) {
                    ME.editor = JS.codeEditor("ur-php-script", "php");
                    ME.editor.setValue(api.php_script(), -1);
                    ME.editor.session.on('change', function () { api.php_script(ME.editor.getValue()); });
                } 
                else {
                    ME.cqReadEditor = JS.codeEditor("ur-cq-read", "mysql", false, "cloud9_day", false, false);
                    ME.cqReadEditor.setValue(api.read_custom_query(), -1);
                    ME.cqReadEditor.session.on('change', function () { api.read_custom_query(ME.cqReadEditor.getValue()); });
                    
                    ME.cqWriteEditor = JS.codeEditor("ur-cq-write", "mysql", false, "cloud9_day", false, false);
                    ME.cqWriteEditor.setValue(api.write_custom_query(), -1);
                    ME.cqWriteEditor.session.on('change', function () { api.write_custom_query(ME.cqWriteEditor.getValue()); });

                    ME.cqUpdateEditor = JS.codeEditor("ur-cq-update", "mysql", false, "cloud9_day", false, false);
                    ME.cqUpdateEditor.setValue(api.update_custom_query(), -1);
                    ME.cqUpdateEditor.session.on('change', function () { api.update_custom_query(ME.cqUpdateEditor.getValue()); });

                    ME.cqDeleteEditor = JS.codeEditor("ur-cq-delete", "mysql", false, "cloud9_day", false, false);
                    ME.cqDeleteEditor.setValue(api.delete_custom_query(), -1);
                    ME.cqDeleteEditor.session.on('change', function () { api.delete_custom_query(ME.cqDeleteEditor.getValue()); });
                }

            })
            .catch((error) => { ME.catchError(error, "[002]") });
    },

    updateConditions: function (condition) {
        if (JS.isNotValid(condition)) return [];
        for (var i = 0; i < condition.length; i++) if (!condition[i].showValueInput) condition[i].value = "";
    },

    apiForm: function (action) {
        if (action == "HIDE_ALL") {
            $('[api-form]').hide();
        } else if (action == "SHOW") {
            $('[api-form]').hide();
            this.currentAPIForm.show();
        } else if (action == "MODAL_OPEN") {
            $("#ur-modal-newapi").dialog("open");
        } else if (action == "MODAL_CLOSE") {
            $("#ur-modal-newapi").dialog("close");
        }
    },

    apiModal: function () {
        var ME = this;

        $("#ur-modal-newapi").dialog({
            classes: {
                "ui-dialog": "ur-newtable"
            },
            autoOpen: false,
            resizable: false,
            height: "auto",
            width: 800,
            modal: true,
            buttons: {
                "SAVE": function () {

                    var name = $("#tfurapi-name").val().trim();
                    var description = $("#tfurapi-description").val().trim();
                    var type = $("input[name='tfurapi-type']:checked").val();

                    var namePattern = /^[a-z][a-z0-9]*$/;
                    var isNameValid = JS.validateInputs("ur-modal-newapi", namePattern, "red", "green");

                    if (description.length > 250) description = description.substring(0, 250) + "...";

                    if (isNameValid) {

                        UNIREST.loader();

                        UNIREST.api("apis_action", {
                            action: (ME.editMode) ? "API_UPDATE" : "API_CREATE",
                            name: ME.curremtAPIID,
                            extra: {
                                name: name,
                                description: description,
                                type: type,
                                groupId: ME.curremtGroupID
                            }
                        })
                            .then((result) => {
                                console.log(result);

                                UNIREST.loader(false);
                                $(this).dialog("close");
                                JS.refresh();
                            })
                            .catch((error) => { ME.catchError(error, "[004]") });
                    } else {
                        JS.tWarn("API NAME INVALID", "Use only lowercase letters and numbers, and the firt char must be a letter.");
                    }

                },
                Cancel: function () {
                    $(this).dialog("close");
                }
            }
        });

    },

    showAPIsList: function () {
        var ME = this;

        UNIREST.loader();

        ME.curremtGroupID = JS.getURLParam("name");

        UNIREST.api("apis_action", { action: "GRUP_INFO", name: ME.curremtGroupID, extra: "" })
            .then((result) => {

                ME.VM.group(result.data);

                UNIREST.api("apis_action", { action: "APIS_LIST", name: ME.curremtGroupID, extra: "" })
                    .then((result) => {

                        for (var i = 0; i < result.data.length; i++)
                            if (result.data[i].info == null)
                                result.data[i].info = {
                                    tableName: "",
                                    description: "",
                                    isSQL: false,
                                    isPHP: false,
                                    canRead: false,
                                    canWrite: false,
                                    canUpdate: false,
                                    canDelete: false,
                                };
                            else
                                result.data[i].info = JSON.parse(result.data[i].info);

                        console.log("API LIST", result);

                        ME.db = result.db;

                        var tableNames = Object.keys(URAPI.db);
                        tableNames.unshift("[CUSTOM QUERIES ONLY]");
                        ME.VM.tables(tableNames);

                        const apiList = result.data.map(api => new ME.ApiModel(api));
                        ME.VM.createdApis(apiList);

                        UNIREST.loader(false);

                    })
                    .catch((error) => { ME.catchError(error, "[002]") });

            })
            .catch((error) => { ME.catchError(error, "[001]") });
    },

    createNewAPI: function () {
        var ME = this;

        if (ME.jsonDataChanged()) {
            JS.ask("SAVE API", "The API configuration has been modified. Do you want to save it?", () => {
                ME.save();
                ME.newAPI();
            }, "warning", () => {
                ME.newAPI();
            });
        }
        else {
            ME.newAPI();
        }

    },

    newAPI: function () {
        var ME = this;

        ME.curremtAPIID = 0;
        ME.editMode = false;
        ME.apiForm("MODAL_OPEN");

        $("#tfurapi-name").val("");
        $("#tfurapi-description").val("");
        $('input[name="tfurapi-type"][value="SQL"]').prop('checked', true);
    },

    APIaction: function (e, action) {
        var ME = this;
        var index = parseInt($(e).attr("apiid"));
        if (index < 0 || JS.isNotValid(index)) return;

        switch (action) {
            case "EDIT":
                ME.editMode = true;
                ME.curremtAPIID = index;
                this.apiForm("MODAL_OPEN");

                var api = ko.utils.arrayFirst(ME.VM.createdApis(), function (api) {
                    return api.id() == index;
                });

                $("#tfurapi-name").val(api.name());
                $("#tfurapi-description").val(api.description());
                $('input[name="tfurapi-type"][value="' + api.type() + '"]').prop('checked', true);
                break;

            case "DELETE":
                JS.ask("DELETE API", "Do you want to delete this API?", () => {
                    UNIREST.loader();
                    UNIREST.api("apis_action", { action: "API_DELETE", name: "", extra: { id: index, groupId: ME.curremtGroupID } })
                        .then((result) => {

                            JS.refresh();
                            UNIREST.loader(false);

                        })
                        .catch((error) => { ME.catchError(error, "[003]") });
                });
                break;

            default:
                break;
        }
    },

    initializeComponents: function () {

        ko.components.register('column-select', {
            viewModel: function (params) {
                var self = this;
                self.tableColumns = params.tableColumns; // Array di colonne della tabella
                self.selectedColumns = params.selectedColumns; // Colonne selezionate

                setTimeout(() => { $('.ur-select-multi').select2(); }, 150);
            },
            template: `
        <div class="columns">
            <label>Columns</label>
            <div>Columns to be involved in the operation.</div>
            <select class="ur-select-multi" multiple="multiple" data-bind="select2: tableColumns, selectedOptions: selectedColumns"></select>
        </div>
    `
        });

        ko.components.register('condition-builder', {
            viewModel: function (params) {
                var self = this;

                self.conditions = params.conditions || ko.observableArray([]);
                self.logicalOperator = params.logicalOperator || ko.observable('AND');  // Riceve l'operatore logico dal model

                // Funzione di supporto per normalizzare le condizioni
                self.normalizeCondition = function (condition) {
                    // Se 'operator' non è un observable, lo converte in uno
                    condition.operator = ko.isObservable(condition.operator)
                        ? condition.operator
                        : ko.observable(condition.operator);

                    // Se 'value' non è un observable, lo converte in uno
                    condition.value = ko.isObservable(condition.value)
                        ? condition.value
                        : ko.observable(condition.value);

                    // Se 'column' non è un observable, lo converte in uno
                    condition.column = ko.isObservable(condition.column)
                        ? condition.column
                        : ko.observable(condition.column);

                    // Definisce 'showValueInput' come un observable computed, o mantiene il valore se già presente
                    condition.showValueInput = ko.isObservable(condition.showValueInput)
                        ? condition.showValueInput
                        : ko.computed(function () {
                            return ["IN", "NOTIN", "LIKE", "NOTLIKE"].indexOf(condition.operator()) >= 0;
                        });

                    condition.unityLabel = ko.computed(function () {
                        return condition.column() ? "'" + condition.column() + "' value from Unity" : "Value from Unity";
                    });

                    return condition;
                };

                // Funzione per aggiungere una nuova condizione
                self.addCondition = function () {
                    var newCondition = {
                        column: ko.observable(),
                        operator: ko.observable(),
                        value: ko.observable(),
                    };

                    // Normalizza la nuova condizione per includere 'showValueInput'
                    self.conditions.push(self.normalizeCondition(newCondition));
                };

                // Se stai caricando condizioni predefinite dal JSON, assicurati che siano normalizzate
                params.conditions && params.conditions().forEach(function (condition, index) {
                    self.conditions()[index] = self.normalizeCondition(condition);
                });

                // Funzione per rimuovere una condizione
                self.removeCondition = function (condition) {
                    self.conditions.remove(condition);
                };

                // Mostra l'operatore logico (AND/OR) solo dalla seconda condizione in poi
                self.getLogicalOperator = function (index) {
                    return index > 0 ? self.logicalOperator() : ''; // Mostra AND/OR solo per condizioni successive alla prima
                };

                self.tableColumns = params.tableColumns;
            },
            template: `
                <div class="conditions">
                    <label>Condition</label>
                    <div class="info">The condition this operation must meet. Multiple conditions can be linked with the AND or OR operator.</div>
                    <div>
                        <input type="radio" data-bind="checked: logicalOperator" value="AND"> AND&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" data-bind="checked: logicalOperator" value="OR"> OR
                    </div>
        
                    <!-- Pulsante per aggiungere una nuova condizione -->
                    <button class="bt-add" data-bind="click: addCondition"><i class="fa-solid fa-plus"></i> Add Condition</button>
        
                    <!-- Elenco delle condizioni -->
                    <div data-bind="foreach: conditions">
                        <div class="row">
                            <!-- Mostra AND/OR prima della seconda condizione -->
                            <input data-bind="value: $parent.getLogicalOperator($index()), attr: { class: 'lbl lbl-' + $index() }"></input>
                            
                            <!-- Select per la colonna -->
                            <select data-bind="options: $parent.tableColumns, value: column, attr: { class: 'cond-' + $index() }"></select>
                            
                            <!-- Select per l'operatore -->
                            <select data-bind="value: operator">
                                <option value="=">=</option>
                                <option value="!=">!=</option>
                                <option value=">">&gt;</option>
                                <option value="<">&lt;</option>
                                <option value=">=">&gt;=</option>
                                <option value="<=">&lt;=</option>
                                <option value="IN">IN</option>
                                <option value="NOTIN">NOT IN</option>
                                <option value="LIKE">LIKE</option>
                                <option value="NOTLIKE">NOT LIKE</option>
                            </select>
                            
                            <!-- Input per il valore, visibile solo per certi operatori -->
                            <input class="values" type="text" data-bind="value: value, visible: showValueInput" placeholder="Enter one value or values separated by comma" />
                            <input class="values" type="text" data-bind="value: value, visible: !showValueInput(), attr: { placeholder: unityLabel }" disabled />  
                            
                            <!-- Pulsante per rimuovere la condizione -->
                            <button class="bt-delete" data-bind="click: $parent.removeCondition"><i class="fa-solid fa-xmark"></i> Delete</button>
                        </div>
                    </div>
                </div>
            `
        });


    },

    catchError: function (error, id) {
        UNIREST.loader(false);
        console.log(error);
        if (error.error == "API_ALREADY_EXISTS") {
            JS.tWarn("EXISTING API", "An API with this name already exists.");
        }
        else {
            JS.tError("ERROR", "Error performing the operation " + id);
        }
    },

    onchange: function () {
        $('.ur-select-multi').select2();
    },

    help: function(i) {

        var custom = "• The Table name must start with the prefix <b>tfur_</b>;<br>";
        custom += "• The name of the Table and of the Columns must be <b>enclosed</b> in the angle brackets <b>&lt;</b> and <b>&gt;</b>;<br>";
        custom += "• The value from Unity must start with the <b>colon character</b>. Fixed values can be written as they are.<br><br><b>EXAMPLE</b></br>"

        var end = "<br><br><i>Have a look at the online Manual for more details.</i>";

        if (i == 1) {
            JS.alert("question", "RECORD EXISTENCE CHECK", "When checked, the Read operation uses the defined Condition to detect whether <b>one or more records exist</b>.<br><br>Unity will receive:<br>• <b>true</b> or <b>false</b>, depending on the result of the check;<br>• the <b>number of found records</b>.");
        } else if (i == 2) {
            JS.alert("question", "ALLOW WRITE", "When checked, the Update operation uses the defined Condition to determine <b>if a record exists</b>.<br><br>• The record <b>exists</b>: it will be updated with the new provided values.<br>• The record <b>does not</b> exist: a new record will be written with the provided values.");
        } else if (i == 31) {
            JS.alert("question", "CUSTOM QUERY SYNTAX", custom + "SELECT * FROM &lt;tfur_users&gt; WHERE &lt;i&gt;=:id" + end);
        } else if (i == 32) {
            JS.alert("question", "CUSTOM QUERY SYNTAX", custom + "INSERT INTO &lt;tfur_users&gt; (&lt;name&gt;, &lt;age&gt;) VALUES (:name, :age)" + end);
        } else if (i == 33) {
            JS.alert("question", "CUSTOM QUERY SYNTAX", custom + "UPDATE &lt;tfur_users&gt; SET &lt;name&gt;=:name WHERE &lt;id&gt;=:id" + end);
        } else if (i == 34) {
            JS.alert("question", "CUSTOM QUERY SYNTAX", custom + "DELETE FROM &lt;tfur_users&gt; WHERE &lt;age&gt;&lt;=:age" + end);
        }

    },

};