var URMAIN = {

    onLoad: function() {

        Q.ready(() => {

            UNIREST.loader();

            UNIREST.isInstalled()
            .then(response => {
                UNIREST.loader(false);
                if (response) {
                    Q.hide("ur-main-first-start");
                    Q.show("ur-main-welcome");

                    UNIREST.loader();

                    var alert = '<i style="color:red;" class="fa-solid fa-triangle-exclamation"></i>';

                    UNIREST.api("systemconfig")
                        .then(response => {
                            UNIREST.loader(false);
                            var s = response.settings;

                            var HTML = "";
                            HTML += "<div>" + (s.file_uploads == "enabled" ? "• You can upload files in your Server." : "• " + alert + " You can't upload files in your Server.") + "</div>";
                            HTML += "<div>• You can upload files with a maximum size of <b>" + s.upload_max_filesize + "</b> (<i>only " + s.max_file_uploads + " simultaneous files</i>).</div>";
                            HTML += "<div>• The maximum data size your APIs can handle is <b>" + s.post_max_size + "</b>.</div>";
                            HTML += "<div>• Your PHP memory limit is <b>" + s.memory_limit + "</b>.</div>";
                            Q.html("phpconfig", HTML);

                        })
                        .catch(error => {
                            UNIREST.loader(false);
                            console.error(error);
                        });

                } else {
                    Q.show("ur-main-first-start", "flex");
                }              
            })
            .catch(error => {
                UNIREST.loader(false);
                console.error(error);
            });

        });

    },

    systemCheck: function () {

        UNIREST.loader();

        UNIREST.api("systemcheck")
            .then(response => {
                UNIREST.loader(false);

                Q.hide("ur-main-first-start");
                Q.show("ur-main-system-check", "flex");

                this.showCheckResults(response);

                console.log(response);
            })
            .catch(error => {
                UNIREST.loader(false);
                console.error(error);
            });

    },

    showCheckResults: function (data) {

        var HTML = "<tr><td>[ICON]</td><td><b>[TITLE]</b><br><i>[DESC]</i></td></tr>";
        var iconOK = '<i class="fa-solid fa-circle-check fa-2x" style="color:green;"></i>';
        var iconKO = '<i class="fa-solid fa-triangle-exclamation fa-2x" style="color:red;"></i>';
        var result = "";
        var issues = false;

        if (data.php_version != "") {
            result += HTML.replace("[ICON]", iconOK).replace("[TITLE]", "PHP VERSION (" + data.php_version + ")").replace("[DESC]", "Your PHP version is higher than 7.4, which is secure.");
        } else {
            result += HTML.replace("[ICON]", iconKO).replace("[TITLE]", "PHP VERSION").replace("[DESC]", "Your PHP version is 7.4 or less, which is insecure. Please, upgrade your PHP installation.");
            issues = true;
        }

        if (data.getallheaders_available) {
            result += HTML.replace("[ICON]", iconOK).replace("[TITLE]", "GETALLHEADERS()").replace("[DESC]", "The getallheaders() PHP method is available.");
        } else {
            result += HTML.replace("[ICON]", iconKO).replace("[TITLE]", "GETALLHEADERS()").replace("[DESC]", "The getallheaders() PHP method is not available and this will make your APIs not working. Please, anable this method in your PHP installation.");
            issues = true;
        }

        if (data.permalinks_structure != "") {
            result += HTML.replace("[ICON]", iconOK).replace("[TITLE]", "PERMALINK (" + data.permalinks_structure + ")").replace("[DESC]", "The WordPress permalink is correctly set.");
        } else {
            result += HTML.replace("[ICON]", iconKO).replace("[TITLE]", "PERMALINK").replace("[DESC]", "The WordPress permalink is set to ''Plain Text'', which will make your APIs not working. Plase, change your permalink settings.");
            issues = true;
        }

        if (data.folder_creation_1 && data.folder_creation_2 && data.folder_deletion_1 && data.folder_deletion_2) {
            result += HTML.replace("[ICON]", iconOK).replace("[TITLE]", "FOLDER MANAGEMENT").replace("[DESC]", "Folders can be created and deleted without issues.");
        }
        else {
            result += HTML.replace("[ICON]", iconKO).replace("[TITLE]", "FOLDER MANAGEMENT").replace("[DESC]", "Folders can't be created or deleted. Check the CHMOD permissions of the ''uploads'' folder.");
            issues = true;
        }

        if (data.file_creation && data.file_deletion) {
            result += HTML.replace("[ICON]", iconOK).replace("[TITLE]", "FILE MANAGEMENT").replace("[DESC]", "Files can be created and deleted without issues.");
        } else {
            result += HTML.replace("[ICON]", iconKO).replace("[TITLE]", "FILE MANAGEMENT").replace("[DESC]", "Files can't be created or deleted. Check the CHMOD permissions of the ''uploads'' folder.");
            issues = true;
        }

        if (data.database) {
            result += HTML.replace("[ICON]", iconOK).replace("[TITLE]", "DATABASE MANAGEMENT").replace("[DESC]", "Your Database is accessible without issues.");
        } else {
            result += HTML.replace("[ICON]", iconKO).replace("[TITLE]", "DATABASE MANAGEMENT").replace("[DESC]", "Your Database is not accessible. Check if there is some restriction on accessing your Database.");
            issues = true;
        }

        Q.add("ur-main-check-results", result);

        Q.showIfTrue(issues, "ur-main-check-ko", "ur-main-check-ok");

    },

    install: function () {

        UNIREST.loader();

        UNIREST.api("install")
        .then((response) => {
            console.log(response);
            UNIREST.loader(false);
            JS.refresh();
        })
        .catch((error) => {
            console.log(error);
            UNIREST.loader(false);
        });

    },

    goTo: function(i) {
        if (i == 1) JS.go("admin.php?page=unirest-plugin-database");
        if (i == 2) JS.go("admin.php?page=unirest-plugin-apigroup");
        if (i == 3) JS.go("admin.php?page=unirest-plugin-uploads");
        if (i == 4) JS.go("admin.php?page=unirest-plugin-unity");
    },

    unirestRefresh: function() {
        URUNITY.generate(true);
    }

}