var URSETTINGS = {

    onLoad: function () {

        Q.ready(() => {

            UNIREST.loader();

            UNIREST.api("systemconfig")
                .then(response => {
                    UNIREST.loader(false);
                    var s = response.settings;

                    Q.val("key1", s.key1);
                    Q.val("key2", s.key2);
                    Q.val("webglpath", s.webglpath || "");

                    Q.val("application-username", "");
                    Q.val("application-password", "");

                })
                .catch(error => {
                    UNIREST.loader(false);
                    console.error(error);
                });

        });

    },

    saveKeys: function() {
        JS.ask("SAVE KEYS", "You are going to set new Security Keys on your UniREST System. Proceed?<br><br><i style='color:darkred;'>Proceed only if you are sure of what you are doing!</i>", () => {
            
            var key1 = $("#key1").val();
            var key2 = $("#key2").val();

            if (key1.length != 32 || key2.length != 16) {
                JS.tError("SAVE KEYS", "The Security Keys must have the required length.");
                return;
            }

            UNIREST.loader();

            UNIREST.key("WRITE", "UniREST_Secret_Key1", key1)
            .then(response => {
               
                UNIREST.key("WRITE", "UniREST_Secret_Key2", key2)
                .then(response => {
                   
                    UNIREST.loader(false);
                    JS.alert("success", "SAVE KEYS", "Your Security Keys have been saved. You must regenerate a new <b>Unity API Script</b>.");
    
                })
                .catch(error => {
                    UNIREST.loader(false);
                    console.error(error);
                });

            })
            .catch(error => {
                UNIREST.loader(false);
                console.error(error);
            });

        });
    },

    newKeys: function() {
        JS.ask("REGENERATE KEYS", "You are going to generate new Security Keys on your UniREST System. Proceed?<br><br><i style='color:darkred;'>Proceed only if you are sure of what you are doing!</i>", () => {
            
            var newKeys = UNIREST.generateSecretKeys();

            $("#key1").val(newKeys.key1);
            $("#key2").val(newKeys.key2);

            var key1 = $("#key1").val();
            var key2 = $("#key2").val();

            if (key1.length != 32 || key2.length != 16) {
                JS.tError("SAVE KEYS", "The Security Keys must have the required length.");
                return;
            }

            UNIREST.loader();

            UNIREST.key("WRITE", "UniREST_Secret_Key1", key1)
            .then(response => {
               
                UNIREST.key("WRITE", "UniREST_Secret_Key2", key2)
                .then(response => {
                   
                    UNIREST.loader(false);
                    JS.alert("success", "SAVE KEYS", "Your new Security Keys have been saved. You must regenerate the <b>Unity API Script</b> too.");
    
                })
                .catch(error => {
                    UNIREST.loader(false);
                    console.error(error);
                });

            })
            .catch(error => {
                UNIREST.loader(false);
                console.error(error);
            });

        });
    },

    saveWebGL: function() {

        var webglpath = $("#webglpath").val();

        UNIREST.key("WRITE", "UniREST_WebGL_Path", webglpath)
        .then(response => {
           
            UNIREST.loader(false);
            JS.alert("success", "WEBGL PATH", "Your WebGL path has been saved.");

        })
        .catch(error => {
            UNIREST.loader(false);
            console.error(error);
        });

    },

    saveAppAccount: function() {

        var username = $("#application-username").val();
        var password = $("#application-password").val();
        var json = { username: username, password : password }

        UNIREST.key("WRITE_DECODED", "UniREST_Application_Account", JSON.stringify(json))
        .then(response => {
           
            UNIREST.loader(false);
            JS.alert("success", "APPLICATION ACCOUNT", "Your Application Account has been saved.<br><br>NOTE: <i>for security reasons, the credentials won't be displayed in the fields in the future</i>");

        })
        .catch(error => {
            UNIREST.loader(false);
            console.error(error);
        });

    }
}