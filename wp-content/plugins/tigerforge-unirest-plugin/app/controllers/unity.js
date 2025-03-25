var URUNITY = {

    editor: null,

    onStart: function () {
        var ME = this;
        Q.ready(() => {

            ME.editor = JS.codeEditor("ur-unity-script", "csharp", true);
            Q.show("ur-script-instructions");
            Q.hide("ur-copy-btn");

        });
    },

    generate: function (silence = false) {
        var ME = this;

        UNIREST.loader();
        UNIREST.api("unity_script")
            .then((result) => {

                //console.log(result.data);

                if (!silence) {
                    Q.hide("ur-script-instructions");
                    Q.show("ur-copy-btn", "flex");
                    ME.editor.session.setValue(result.script);
                }

                JS.tOk("UNITY API SCRIPT", "The Unity API system has been updated.");
                UNIREST.loader(false);

            })
            .catch((error) => { ME.catchError(error, "[001]") });
    },

    copy: function () {
        var ME = this;

        ME.editor.selectAll();

        var selectedText = ME.editor.getSelectedText();
        navigator.clipboard.writeText(selectedText).then(function () {
            JS.tOk("UNITY API SCRIPT COPIED", "Now paste this C# Script into your Unity project (UniRESTAPIScript.cs).")
        }, function (err) {
            JS.tError("COPY FAILED", "An error occurred trying copy the C# Script. Manually copy the code.")
        });

        ME.editor.clearSelection();
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

};