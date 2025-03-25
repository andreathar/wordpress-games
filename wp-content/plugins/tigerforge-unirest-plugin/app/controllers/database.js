var URDB = {

    dataModel: {

        structure: {
            name: ko.observable(""),
            row: ko.observableArray([])
        },

        db: {
            table: ko.observableArray([])
        },

    },

    clone: {},

    isEditMode: false,

    onStart: function () {
        var ME = this;
        Q.ready(() => {

            $("#ur-modal-newtable").dialog({
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

                        if (JS.validateInputs("ur-modal-newtable", /^[A-Za-z][A-Za-z0-9_]*$/, "red", "#8c8f94")) {

                            JS.ask(
                                (ME.isEditMode) ? "UPDATE TABLE" : "CREATE NEW TABLE",
                                (ME.isEditMode) ? "Do you want to update this Table structure?<br><br><i>If this table already contains data, changing the structure may delete or alter some data.</i>" : "Do you want to create this new Table?"
                                , () => {

                                    UNIREST.loader();
                                    var json = ko.toJSON(ME.dataModel.structure);

                                    if (ME.isEditMode) {

                                        var comparison = ME.compareStructure(ME.clone, json);
                                        console.log(comparison);
                                        UNIREST.api("db_table_structure_update", JSON.stringify(comparison))
                                            .then((result) => {

                                                console.log(result);
                                                ME.showTableList();
                                                JS.tOk("TABLE UPDATED", "Your table's structure has been updated.");

                                            })
                                            .catch((error) => { UNIREST.loader(false); console.log("error"); JS.tError("ERROR", "Error performing the operation [E001]."); });

                                    }
                                    else {
                                        UNIREST.api("db_table_create", json)
                                            .then((result) => {

                                                console.log(result);
                                                ME.showTableList();
                                                JS.tOk("TABLE CREATED", "Your table has been created.");

                                            })
                                            .catch((error) => { UNIREST.loader(false); console.log("error"); JS.tError("ERROR", "Error performing the operation [E002]."); });
                                    }

                                    $(this).dialog("close");

                                });

                        } else {
                            JS.tWarn("INVALID NAMES", "Use only letters, numbers and the underscore symbol. The first char must be a letter.");
                        }
                    },
                    Cancel: function () {
                        $(this).dialog("close");
                    }
                }
            });

            ko.applyBindings(this.dataModel);

            $("#ur-columns-list").sortable({
                items: "li:not(:first-child)",

                update: function (event, ui) {
                    var startIndex = ui.item.data('startIndex');
                    var newIndex = ui.item.index();

                    var movedItem = ME.dataModel.structure.row()[startIndex];
                    ME.dataModel.structure.row()[startIndex] = null;
                    var l = ME.dataModel.structure.row().length;
                    var newArray = [];

                    for (var i = 0; i < l; i++) {
                        if (ME.dataModel.structure.row()[i] != null) {
                            if (i == newIndex) {
                                if (startIndex < newIndex) {
                                    newArray.push(ME.dataModel.structure.row()[i]);
                                    newArray.push(movedItem);
                                } else {
                                    newArray.push(movedItem);
                                    newArray.push(ME.dataModel.structure.row()[i]);
                                }

                            }
                            else {
                                newArray.push(ME.dataModel.structure.row()[i]);
                            }
                        }
                    }

                    ME.dataModel.structure.row.removeAll();

                    l = newArray.length;
                    for (var i = 0; i < l; i++) {
                        ME.dataModel.structure.row.push({
                            name: ko.observable(newArray[i].name()),
                            type: ko.observable(newArray[i].type()),
                            size: ko.observable(newArray[i].size()),
                            canBeNull: ko.observable(newArray[i].canBeNull()),
                            readonly: ko.observable(newArray[i].readonly()),
                            showSize: ko.observable(newArray[i].showSize()),
                            status: ko.observable(newArray[i].status())
                        });
                    }

                },
                start: function (event, ui) {
                    ui.item.data('startIndex', ui.item.index());
                }
            });

            $("[urntmover='0'").css("opacity", "0");

            ME.showTableList();

        });

    },

    openNewTableModal: function () {

        this.isEditMode = false;
        this.resetFirtColumn();
        $("#ur-modal-newtable").dialog("open");

    },

    addNewColumn: function () {

        this.dataModel.structure.row.push({
            name: ko.observable(""),
            type: ko.observable("TEXT"),
            size: ko.observable(11),
            canBeNull: ko.observable(true),
            readonly: ko.observable(false),
            showSize: ko.observable(false),
            status: ko.observable("NEW")
        });

    },

    onTypeChange: function (e) {
        var value = $(e).val();
        var index = parseInt($(e).attr("index"));

        this.dataModel.structure.row()[index].showSize((value == "VARCHAR"));
    },

    removeItem: function (e) {
        var ME = this;

        var index = parseInt($(e).attr("index"));
        if (index <= 0) return;

        var name = ME.dataModel.structure.row()[index].name();

        JS.ask("REMOVE COLUMN", "Do you want to remove the <b>" + name + "</b> column?", () => {

            ME.dataModel.structure.row()[index] = null;
            var l = ME.dataModel.structure.row().length;
            var newArray = [];

            for (var i = 0; i < l; i++) {
                if (ME.dataModel.structure.row()[i] != null) {
                    newArray.push(ME.dataModel.structure.row()[i]);
                }
            }

            ME.dataModel.structure.row.removeAll();

            l = newArray.length;
            for (var i = 0; i < l; i++) {
                ME.dataModel.structure.row.push({
                    name: ko.observable(newArray[i].name()),
                    type: ko.observable(newArray[i].type()),
                    size: ko.observable(newArray[i].size()),
                    canBeNull: ko.observable(newArray[i].canBeNull()),
                    readonly: ko.observable(newArray[i].readonly()),
                    showSize: ko.observable(newArray[i].showSize()),
                    status: ko.observable(newArray[i].status())
                });
            }

        });
    },

    showTableList: function () {
        var ME = this;

        UNIREST.loader();

        UNIREST.api("db_table_list")
            .then((result) => {

                ME.dataModel.db.table.removeAll();

                for (var i = 0; i < result.list.length; i++) {
                    ME.dataModel.db.table.push({ name: ko.observable(result.list[i].substr(5)) });
                }
                UNIREST.loader(false);

            })
            .catch((error) => { UNIREST.loader(false); console.log("error"); JS.tError("ERROR", "Error performing the operation [E003]."); });

    },

    dbListAction: function (e, action, URL = "") {
        var ME = this;
        var index = parseInt($(e).attr("urlistrow"));
        if (index < 0) return;

        switch (action) {
            case "OPEN":

                URL += "&name=" + ME.dataModel.db.table()[index].name();
                JS.go(URL);
                break;

            case "EDIT":

                UNIREST.loader();
                ME.isEditMode = true;

                UNIREST.api("db_table_structure", ME.dataModel.db.table()[index].name())
                    .then((result) => {

                        ME.dataModel.structure.name(ME.dataModel.db.table()[index].name());
                        ME.resetFirtColumn();

                        for (var i = 1; i < result.list.length; i++) {

                            var column = result.list[i];

                            var name = column.Field;
                            var type = column.Type.toUpperCase();
                            var size = 0;
                            var canBeNull = (column.Null === "YES");
                            var showSize = false;

                            if (column.Type.startsWith("varchar")) {
                                type = "VARCHAR";
                                var sizeMatch = column.Type.match(/\((\d+)\)/);
                                if (sizeMatch) {
                                    size = parseInt(sizeMatch[1]);
                                }
                                showSize = true;
                            }

                            ME.dataModel.structure.row.push({
                                name: ko.observable(name),
                                type: ko.observable(type),
                                size: ko.observable(size),
                                canBeNull: ko.observable(canBeNull),
                                readonly: ko.observable(false),
                                showSize: ko.observable(showSize),
                                status: ko.observable(name),
                            });
                        }

                        $("#ur-modal-newtable").dialog("open");

                        ME.clone = ko.toJSON(ME.dataModel.structure);

                        UNIREST.loader(false);

                    })
                    .catch((error) => { UNIREST.loader(false); console.log(error); JS.tError("ERROR", "Error performing the operation [E004]."); });

                break;

            case "EMPTY":

                JS.ask("EMPTY TABLE", "Do you want to empty this table?<br><br><i>All the table's records will be deleted.</i>", () => {
                    UNIREST.api("db_table_actions", JSON.stringify({ action: "EMPTY", table: ME.dataModel.db.table()[index].name(), extra: "" }))
                        .then((result) => {

                            console.log(result);
                            ME.showTableList();
                            JS.tOk("TABLE EMPTIED", "Your table has been emptied.");

                        })
                        .catch((error) => { UNIREST.loader(false); console.log("error"); JS.tError("ERROR", "Error performing the operation [E005]."); });
                });

                break;

            case "DELETE":

                JS.ask("DELETE TABLE", "Do you want to delete this table?<br><br><i>The table and all its records will be deleted.</i>", () => {
                    UNIREST.api("db_table_actions", JSON.stringify({ action: "DELETE", table: ME.dataModel.db.table()[index].name(), extra: "" }))
                        .then((result) => {

                            console.log(result);
                            ME.showTableList();
                            JS.tOk("TABLE DELETED", "Your table has been deleted.");

                        })
                        .catch((error) => { UNIREST.loader(false); console.log("error"); JS.tError("ERROR", "Error performing the operation [E005]."); });
                });

                break;

            default:
                break;
        }
    },

    resetFirtColumn: function () {

        this.dataModel.structure.row.removeAll();
        this.dataModel.structure.row.push({
            name: ko.observable("id"),
            type: ko.observable("BIGINT"),
            size: ko.observable(20),
            canBeNull: ko.observable(false),
            readonly: ko.observable(true),
            showSize: ko.observable(false),
            status: ko.observable("ID")
        });

    },

    compareStructure: function (original, updated) {

        original = JSON.parse(original);
        updated = JSON.parse(updated);

        let result = {
            name: "",         // Nome della tabella, se cambiato
            table: "",        // Nome originale della tabella
            newcolumns: [],   // Colonne nuove
            delcolumns: [],   // Colonne rimosse
            modcolumns: [],   // Colonne modificate
            columnNames: []   // Elenco dei nomi delle colonne presenti nella tabella aggiornata
        };

        result.table = original.name;

        // 1. Controlla se il nome della tabella è cambiato
        if (original.name !== updated.name) {
            result.name = updated.name;
        }

        // 2. Crea una mappa per le colonne originali
        let originalColumns = original.row.map(column => ({
            name: column.name,
            type: column.type,
            size: column.size,
            canBeNull: column.canBeNull,
            readonly: column.readonly,
            showSize: column.showSize,
            status: column.status
        }));

        let updatedColumns = updated.row.map(column => ({
            name: column.name,
            type: column.type,
            size: column.size,
            canBeNull: column.canBeNull,
            readonly: column.readonly,
            showSize: column.showSize,
            status: column.status
        }));

        // 3. Trova nuove colonne nell'array aggiornato (status === "NEW")
        updatedColumns.forEach(updatedColumn => {
            result.columnNames.push(updatedColumn);  // Aggiungi il nome della colonna all'elenco di columnNames
            if (updatedColumn.status === "NEW") {
                result.newcolumns.push(updatedColumn);
            } else if (updatedColumn.status === "ID") {
                // Colonna ID (prima colonna), non può essere modificata o rimossa
                return;
            } else {
                // Confronta colonne esistenti utilizzando il parametro 'status' come identificatore univoco
                let matchingColumn = originalColumns.find(originalColumn => originalColumn.status === updatedColumn.status);
                if (matchingColumn) {
                    // Verifica se ci sono modifiche nelle colonne esistenti
                    let isModified = false;
                    for (let key in updatedColumn) {
                        // Se un valore diverso è stato trovato (eccetto lo 'status'), la colonna è modificata
                        if (updatedColumn[key] !== matchingColumn[key] && key !== 'status') {
                            isModified = true;
                            break;
                        }
                    }
                    if (isModified) {
                        result.modcolumns.push(updatedColumn);
                    }
                }
            }
        });

        // 4. Trova colonne rimosse (colonne nell'originale che non esistono nell'aggiornato, basate su 'status')
        originalColumns.forEach(originalColumn => {
            if (originalColumn.status !== "ID") {
                let matchingColumn = updatedColumns.find(updatedColumn => updatedColumn.status === originalColumn.status);
                if (!matchingColumn) {
                    result.delcolumns.push(originalColumn);  // Aggiungi la colonna eliminata a delcolumns
                }
            }
        });

        return result;
    }



};