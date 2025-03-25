var URTABLE = {

    VM: null,

    tableName: "",

    editMode: { active: false, tr: null, originalHTML: "" },

    insertMode: { active: false, tr: null },

    onStart: function () {

        var ME = this;

        function TableViewModel() {
            var self = this;

            self.records = ko.observableArray([]); // Array per memorizzare i record
            self.columns = ko.observableArray([]); // Array per memorizzare le colonne
            self.pageNumbers = ko.observableArray([]); // Array per memorizzare le colonne
            self.recordsPerPage = ko.observable(50); // Numero di record per pagina
            self.currentPage = ko.observable(1); // Pagina corrente
            self.totalPages = ko.observable(0); // Numero totale di pagine
            self.recordsCount = ko.observable(0); // Variabile che contiene il numero totale di record
            self.fetching = ko.observable(false);
            self.selectedPage = ko.observable(1);

            self.totalPages = ko.computed(function () {
                var total = Math.ceil(self.recordsCount() / self.recordsPerPage());
                return (total <= 0) ? 1 : total;
            });

            self.changePage = function () {
                var page = $("#jumpToPage").val();
                self.currentPage(page); // Aggiorna la pagina corrente
                self.selectedPage(page);
                self.fetchRecords(); // Ricarica i record per la pagina selezionata
            };

            self.getTableInfo = function () {

                UNIREST.loader(true);

                UNIREST.api("db_table_actions", { action: "COUNT", table: ME.tableName, extra: "" })
                    .then((response) => {
                        self.columns(response.columns);
                        self.recordsCount(response.count);
                        var pages = Math.ceil(self.recordsCount() / self.recordsPerPage());
                        for (var i = 1; i <= pages; i++) self.pageNumbers.push(i);

                        // Carica i record (se ci sono)
                        self.fetchRecords();
                    })
                    .catch((error) => { UNIREST.loader(false); console.log(error); JS.tError("ERROR", "Error performing the operation [E002]."); });

            }

            // Funzione per ottenere i record dalla API
            self.fetchRecords = function () {

                self.fetching(true);
                UNIREST.loader(true);
                self.recordsPerPage($("#recordsPerPage").val());

                UNIREST.api("db_table_actions", { action: "RECORDS", table: ME.tableName, extra: self.recordsPerPage() + ";" + self.currentPage() })
                    .then((response) => {

                        self.records(response.records);
                        self.selectedPage(self.currentPage());
                        self.fetching(false);
                        UNIREST.loader(false);

                        if (response.records.length == 0) {
                            let emptyRecord = [];
                            self.columns().forEach(column => {
                                emptyRecord.push("");
                            });
                            self.records([emptyRecord]);
                        }
                    })
                    .catch((error) => { UNIREST.loader(false); console.log(error); JS.tError("ERROR", "Error performing the operation [E001]."); });
            };

            // Funzione per eliminare un record
            self.deleteRecord = function (record) {
                if (confirm("Sei sicuro di voler eliminare questo record?")) {
                    $.ajax({
                        url: '/api/deleteRecord', // Sostituire con l'URL corretto della API
                        type: 'POST',
                        data: { id: record.id },
                        success: function () {
                            self.records.remove(record); // Rimuovi il record dalla tabella
                            self.fetchRecords(); // Aggiorna i record
                        },
                        error: function (xhr, status, error) {
                            console.error("Errore durante l'eliminazione del record", error);
                        }
                    });
                }
            };

            // Funzione per aggiornare un record
            self.updateRecord = function (record) {
                $.ajax({
                    url: '/api/updateRecord', // Sostituire con l'URL corretto della API
                    type: 'POST',
                    data: ko.toJSON(record), // Converti il record in formato JSON
                    contentType: 'application/json',
                    success: function () {
                        alert("Record aggiornato con successo");
                    },
                    error: function (xhr, status, error) {
                        console.error("Errore durante l'aggiornamento del record", error);
                    }
                });
            };

            // Navigazione pagine
            self.nextPage = function () {
                if (self.currentPage() < self.totalPages()) {
                    self.currentPage(self.currentPage() + 1);
                    self.fetchRecords();
                }
            };

            self.prevPage = function () {
                if (self.currentPage() > 1) {
                    self.currentPage(self.currentPage() - 1);
                    self.fetchRecords();
                }
            };

            // Carica i record all'inizio
            self.getTableInfo();
        }

        Q.ready(() => {

            ME.VM = new TableViewModel();
            ko.applyBindings(ME.VM);

        });
    },

    action: function (e, actionType) {

        var ME = this;
        ME.modesCheck();

        var tr = $(e).closest('tr');
        var firstTd = tr.find('td').first();
        var recordID = parseInt(firstTd.text());

        if (actionType === "EDIT") {

            ME.editMode = { active: true, tr: tr, originalHTML: tr.html() };

            tr.find('td[field]').each(function (index, td) {
                if (index > 0) {
                    var tdElement = $(td);
                    var fieldValue = tdElement.attr("title"); // Il valore completo si trova in "title" (in text potrebbe esserci quello troncato da '...')
                    var colName = ME.VM.columns()[index].name;
                    if (fieldValue != "[...]") tdElement.html('<input field="' + colName + '" type="text" value="' + fieldValue + '" />');
                }
            });

            var lastTd = tr.find('td').last();
            lastTd.html(`
                <span id='urrecordsave' style="cursor:pointer;color:green;"><i class="fa-regular fa-floppy-disk"></i> SAVE</span> &nbsp;&nbsp;&nbsp;&nbsp;
                <span id='urrecordcancel' style="cursor:pointer;"><i class="fa-solid fa-xmark"></i> CANCEL</span>
            `);

            $("#urrecordcancel").click(() => {
                ME.editMode.tr.html(ME.editMode.originalHTML);
                ME.editMode = { active: false, tr: null, originalHTML: "" };
            });

            $("#urrecordsave").click(() => {
                var data = {};

                tr.find('input[field]').each(function () {
                    var fieldName = $(this).attr('field');
                    var fieldValue = $(this).val();
                    data[fieldName] = (fieldValue == "[NULL]") ? null : fieldValue;
                });

                UNIREST.loader(true);
                UNIREST.api("db_table_actions", { action: "UPDATE_RECORD", table: ME.tableName, extra: { id: recordID, data: data } })
                    .then((response) => {
                        JS.refresh();
                    })
                    .catch((error) => { UNIREST.loader(false); console.log(error); JS.tWarn("NO UPDATE", "The record hasn't be updated."); });
            });

        } else if (actionType === "DELETE") {

            JS.ask("DELETE RECORD", "Do you want to delete this record?", () => {

                UNIREST.loader(true);
                UNIREST.api("db_table_actions", { action: "DELETE_RECORD", table: ME.tableName, extra: recordID })
                    .then((response) => {
                        JS.refresh();
                    })
                    .catch((error) => { UNIREST.loader(false); console.log(error); JS.tError("ERROR", "Error performing the operation [E004]."); });

            });
        }
    },

    insertRecord: function () {

        var ME = this;
        ME.modesCheck();

        var firstTr = $("#urtablerecords tbody tr:first");

        ME.insertMode.tr = firstTr.clone();
        ME.insertMode.active = true;
        $("#urtablerecords tbody").prepend(ME.insertMode.tr);

        ME.insertMode.tr.find('td[field]').each(function (index) {
            var tdContent = $(this).text();

            if (tdContent === "[...]") {
                $(this).text("[...]");
            }
            else if (index === 0) {
                $(this).empty();
            }
            else {
                var columnName = ME.VM.columns()[index].name;
                $(this).html('<input type="text" field="' + columnName + '" value="" />');
            }
        });

        ME.insertMode.tr.find('td:last').html('<span style="cursor:pointer;color:green;" id="urrecordwrite"><i class="fa-regular fa-floppy-disk"></i> SAVE</span>  &nbsp;&nbsp;&nbsp;&nbsp;<span style="cursor:pointer;" id="urrecordwritecancel"><i class="fa-solid fa-xmark"></i> CANCEL</span>');

        $("#urrecordwritecancel").click(() => {
            ME.insertMode.tr.remove();
            ME.insertMode = { active: false, tr: null };
        });

        $("#urrecordwrite").click(() => {
            var data = {};

            ME.insertMode.tr.find('input[field]').each(function () {
                var fieldName = $(this).attr('field');
                var fieldValue = $(this).val();
                data[fieldName] = fieldValue;
            });

            UNIREST.loader(true);
                UNIREST.api("db_table_actions", { action: "WRITE_RECORD", table: ME.tableName, extra: data })
                    .then((response) => {
                        JS.refresh();
                    })
                    .catch((error) => { UNIREST.loader(false); console.log(error); JS.tWarn("NO WRITE", "The record hasn't be created."); });

        });
    },

    modesCheck: function () {

        if (this.editMode.active) $("#urrecordcancel").trigger("click");
        if (this.insertMode.active) $("#urrecordwritecancel").trigger("click");

    }

};