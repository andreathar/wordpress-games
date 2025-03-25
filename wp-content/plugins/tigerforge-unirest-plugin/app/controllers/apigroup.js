var URAPIGROUP = {

    dataModel: {

        db: {
            table: ko.observableArray([])
        },

    },

    onStart: function () {
        var ME = this;
        Q.ready(() => {

            ko.applyBindings(ME.dataModel);

            ME.showAPIGroupList();

        });
    },

    addNewAPIGroup: function () {
        var ME = this;

        JS.prompt("NEW APIs GROUP", "APIs Group name", (result) => {

            if (result.toLowerCase() == "unirest") {
                JS.alert("error", "NAME ERROR", "This name can't be used.");
                return;
            }

            UNIREST.loader();

            UNIREST.api("api_group_action", { action: "GROUP_ADD", name: result, extra: "" })
                .then((result) => {

                    ME.showAPIGroupList();
                    UNIREST.loader(false);

                })
                .catch((error) => { ME.catchError(error, "[002]") });

        }, "This name is not valid: use only lowercase letters and numbers, and the first character must be a letter. ");
    },

    showAPIGroupList: function () {
        var ME = this;

        UNIREST.loader();

        UNIREST.api("api_group_list")
            .then((result) => {

                ME.dataModel.db.table.removeAll();

                for (var i = 0; i < result.list.length; i++) {
                    ME.dataModel.db.table.push({ name: ko.observable(result.list[i].name), id: result.list[i].id });
                }
                UNIREST.loader(false);

            })
            .catch((error) => { ME.catchError(error, "[001]") });
    },

    APIGroupAction: function (e, action, URL = "") {
        var ME = this;
        var index = parseInt($(e).attr("rowID"));
        if (index < 0) return;

        switch (action) {
            case "OPEN":
                URL += "&name=" + index;
                JS.go(URL);
                break;

            case "RENAME":
                JS.prompt("RENAME GROUP", "New Group name", (result) => {

                    UNIREST.loader();

                    UNIREST.api("api_group_action", { action: "GROUP_RENAME", name: result, extra: { id: index } })
                        .then((result) => {

                            ME.showAPIGroupList();
                            UNIREST.loader(false);

                        })
                        .catch((error) => { ME.catchError(error, "[005]") });

                }, "This name is not valid: use only lowercase letters and numbers, and the first character must be a letter. ");
                break;

            case "DELETE":
                JS.ask("DELETE GROUP", "Do you want to delete this Group and its APIs?<br><br><i>The Group and all the APIs beloging to this Group will be deleted.", () => {
                    UNIREST.loader();
                    UNIREST.api("api_group_action", { action: "GROUP_DELETE", name: "", extra: { id: index } })
                        .then((result) => {

                            ME.showAPIGroupList();
                            UNIREST.loader(false);

                        })
                        .catch((error) => { ME.catchError(error, "[003]") });
                });
                break;

            case "EMPTY":
                JS.ask("EMPTY GROUP", "Do you want to delete the APIs of this Group?<br><br><i>All the APIs beloging to this Group will be deleted.", () => {
                    UNIREST.loader();
                    UNIREST.api("api_group_action", { action: "GROUP_EMPTY", name: "", extra: { id: index } })
                        .then((result) => {

                            ME.showAPIGroupList();
                            UNIREST.loader(false);

                        })
                        .catch((error) => { ME.catchError(error, "[003]") });
                });
                break;

            default:
                break;
        }


    },

    catchError: function (error, id) {
        UNIREST.loader(false);
        console.log(error);
        if (error.error == "GROUP_ALREADY_EXISTS") {
            JS.tWarn("EXISTING GROUP", "A Group with this name already exists.");
        }
        else {
            JS.tError("ERROR", "Error performing the operation " + id);
        }
    }

};