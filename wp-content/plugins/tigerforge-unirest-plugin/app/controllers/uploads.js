var URUPLOADS = {

    currentPath: [],
    data: null,

    onLoad: function () {

        Q.ready(() => {



        });

    },

    getUserData: function () {
        
        var user = $("#userInput").val();

        if (JS.isNullOrEmpty(user)) {
            JS.tError("USER", "You must provide a User's ID, Username or E-mail address.");
            return;
        }

        UNIREST.loader();

        UNIREST.api("uploads", { action: "LIST", user: user })
            .then(response => {
                UNIREST.loader(false);
                console.log(response);

                URUPLOADS.data = response.structure;
                URUPLOADS.currentPath = [];
                URUPLOADS.renderTable(URUPLOADS.data);
            })
            .catch(error => {
                UNIREST.loader(false);
                console.error(error);
                JS.tWarn("USER", "The given User doesn't exist.");
            });
    },

    renderTable: function(folderData) {
        var tbody = $("#fileTable tbody");
        tbody.empty();

        if (URUPLOADS.currentPath.length > 0) {
            var backRow = $("<tr>").addClass("tr-back");

            var backLink = $("<div>").addClass("btn-back").html("<i class='fa-solid fa-arrow-left'></i> Parent Folder").click(function() {
                URUPLOADS.goUp();
            });

            backRow.append($("<td>").append(backLink)).append($("<td>"));
            tbody.append(backRow);
        }

        $.each(folderData.folders, function(index, folder) {
            var folderRow = $("<tr>").addClass("tr-folder");
            var folderLink = $("<div>").addClass("bt-folder").html('<i class="fa-solid fa-folder"></i> ' + folder.folderName).click(function() {
                URUPLOADS.navigateToFolder(folder.folderName);
            });
            folderRow.append($("<td>").append(folderLink)).append($("<td>"));
            tbody.append(folderRow);
        });

        $.each(folderData.files, function(index, file) {
            var fileRow = $("<tr>");
            fileRow.append($("<td>").html('<i style="color: #2271b1;" class="fa-regular fa-file"></i> ' + file.fileName));
            fileRow.append($("<td>").text(JS.fileSize(file.size)));
            tbody.append(fileRow);
        });
    },

    navigateToFolder: function(folderName) {
        URUPLOADS.currentPath.push(folderName);
        var folderData = URUPLOADS.getCurrentFolderData();
        URUPLOADS.renderTable(folderData);
    },

    goUp: function() {
        URUPLOADS.currentPath.pop();
        var folderData = URUPLOADS.getCurrentFolderData();
        URUPLOADS.renderTable(folderData);
    },

    getCurrentFolderData: function() {
        var folderData = URUPLOADS.data;
        for (var i = 0; i < URUPLOADS.currentPath.length; i++) {
            var folderName = URUPLOADS.currentPath[i];
            var foundFolder = folderData.folders.find(function(folder) {
                return folder.folderName === folderName;
            });
            folderData = foundFolder;
        }
        return folderData;
    }

}