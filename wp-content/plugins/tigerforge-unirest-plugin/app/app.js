var UNIREST = {

    loader: function (show = true) {
        if (show) {
            $("#unirest-loader").remove();
            HTML = "<div id='unirest-loader'><div class='background'></div><div class='content'><i class='fa-solid fa-circle-notch fa-spin fa-3x'></i></div></div>";
            $("#unirest").append(HTML);
        }
        else {
            $("#unirest-loader").remove();
        }
    },

    api: function (name, data = "") {

        return new Promise((resolve, reject) => {
            $.ajax({
                url: WP_ADMIN_AJAX_URL,
                type: 'POST',
                data: {
                    action: 'unirest_api_' + name,
                    data_to_send: (typeof data === 'object' && data !== null) ? JSON.stringify(data) : data
                },
                success: function (response) {
                    if (response.success) {
                        var json = JSON.parse(response.data);
                        if (json.result === "SUCCESS") resolve(json); else reject(json);
                    }
                    else {
                        reject(response);
                    }
                },
                error: function (xhr, status, error) {
                    reject(error);
                }
            });

        });

    },

    key: function (action, key, value) {

        return new Promise((resolve, reject) => {

            this.api("key", JSON.stringify({ action: action, key: key, value: value }))
                .then(response => {
                    resolve(response.data);
                })
                .catch(error => {
                    reject(error);
                });

        });

    },

    generateSecretKeys: function () {

        var characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        var charactersLength = characters.length;
        var key1 = "";
        var key2 = "";
        for (var i = 0; i < 32; i++) {
            key1 += characters[Math.floor(Math.random() * charactersLength)];
            key2 += characters[Math.floor(Math.random() * charactersLength)];
        }
        key2 = key2.substring(0, 16);

        return { key1: key1, key2: key2 };

    },

    isInstalled: function () {

        return new Promise((resolve, reject) => {

            this.key("EXISTS", "UniREST_Secret_Key1", "")
                .then(response => {
                    resolve(response);
                })
                .catch(error => {
                    reject(error);
                });

        });

    }

};

var Q = {

    ready: function (callback) {
        $(document).ready(function () {
            callback();
        });
    },

    show: function (id, display = "") {
        $("#" + id).show();
        if (display != "") $("#" + id).css("display", display);
    },

    showIfTrue: function (check, ifTrue, ifFalse) {
        if (check) this.show(ifTrue); else this.show(ifFalse);
    },

    hide: function (id) {
        $("#" + id).hide();
    },

    add: function (id, html) {
        $("#" + id).append(html);
    },

    visible: function (id, status) {
        if (status) $(id).show(); else $(id).hide();
    },

    css: function (id, styles) {
        for (let key in styles) {
            if (styles.hasOwnProperty(key)) {
                $(id).css(key, styles[key]);
            }
        }
    },

    html: function(id, html) {
        $("#" + id).html(html);
    },

    val: function(id, value) {
        $("#" + id).val(value);
    }

};

var JS = {

    refresh: function () {
        location.reload();
    },

    go: function (URL) {
        window.location = URL;
    },

    arrayMove: function moveItemInArray(array, startIndex, newIndex) {
        if (newIndex >= array().length) {
            var k = newIndex - array().length + 1;
            while (k--) {
                array.push(undefined);
            }
        }
        array.splice(newIndex, 0, array.splice(startIndex, 1)[0]);
        return array;
    },

    ask: function (title, text, yes, icon = "question", no = null, yesLabel = "YES", noLabel = "NO") {

        Swal.fire({
            title: title,
            html: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: yesLabel,
            cancelButtonText: noLabel
        }).then((result) => {
            if (result.isConfirmed) {
                yes();
            } else {
                if (no != null) no();
            }
        });
    },

    tOk: function (title, message) {
        toastr.success(message, title);
    },

    tWarn: function (title, message) {
        toastr.warning(message, title);
    },

    tError: function (title, message) {
        toastr.error(message, title);
    },

    validateInputs: function (containerId, regexPattern, colorError, colorOK) {
        let isValid = true; // Variabile per tenere traccia dello stato di validità

        // Scansione del contenuto del tag con l'ID fornito
        $("#" + containerId).find('input[type="text"][validate]').each(function () {
            // Ottiene il valore corrente dell'input
            var inputValue = $(this).val();

            // Esegui la regex sul valore dell'input
            if (regexPattern.test(inputValue)) {
                // Se la regex è soddisfatta, imposta il bordo su colorOK
                $(this).css("border", "1px solid " + colorOK);
            } else {
                // Se la regex non è soddisfatta, imposta il bordo su colorError
                $(this).css("border", "1px solid " + colorError);
                isValid = false; // Se c'è almeno un errore, imposta isValid a false
            }
        });

        return isValid; // Restituisce true se tutte le regex sono state soddisfatte, altrimenti false
    },

    alert: function (icon, title, text, f = null) {
        Swal.fire({
            title: title,
            html: text,
            icon: icon
        }).then((result) => {
            if (f != null) f();
        });
    },

    prompt: function (title, text, ok, errorMessage = "", inputValue = "") {
        const { value: ipAddress } = Swal.fire({
            title: title,
            input: "text",
            inputLabel: text,
            inputValue,
            showCancelButton: true,
            inputValidator: (value) => {

                const regex = /^[a-z][a-z0-9]*$/;
                if (!value || !regex.test(value)) {
                    return errorMessage;
                }

                ok(value);
            }
        });
    },

    getURLParam: function(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    },

    isNull: function(data) {
        return data == null;
    },

    isUndef: function(data) {
        return typeof data === "undefined";
    },

    isNotValid: function(data) {
        return this.isNull(data) || this.isUndef(data);
    },

    isNullOrEmpty: function(data) {
        return this.isNull(data) || this.isUndef(data) || data == "";
    },

    codeEditor: function(id, language, readonly = false, theme = "one_dark", lineNumbers = true, highlightActiveLine = true) {
        var editor = ace.edit(id);
        editor.setTheme("ace/theme/" + theme);
        editor.session.setMode("ace/mode/" + language);
        editor.setReadOnly(readonly); 
        editor.setShowPrintMargin(false);
        if (!lineNumbers) editor.setOptions({showLineNumbers: false, showGutter: false});
        editor.setOptions({highlightActiveLine: highlightActiveLine});
        return editor;
    },

    fileSize: function(bytes) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        const formattedSize = parseFloat((bytes / Math.pow(k, i)).toFixed(2));
    
        return `${formattedSize} ${sizes[i]}`;
    }


};

ko.bindingHandlers.select2 = {
    init: function(element, valueAccessor, allBindings) {
        var options = ko.unwrap(valueAccessor()); // Ottieni l'osservabile o l'array di opzioni
        var selectedOptions = allBindings.get('selectedOptions'); // Usa direttamente 'allBindings'

        // Inizializza Select2
        $(element).select2({
            data: options // Imposta i dati
        });

        // Sincronizza le opzioni selezionate tra Knockout e Select2
        if (ko.isObservable(selectedOptions)) {
            // Quando cambia il valore selezionato in Select2, aggiorna Knockout
            $(element).on('change', function() {
                var value = $(element).val();
                selectedOptions(value); // Aggiorna l'osservabile Knockout
            });

            // Se l'osservabile cambia in Knockout, aggiorna Select2
            selectedOptions.subscribe(function(newValue) {
                $(element).val(newValue).trigger('change.select2');
            });
        }
    },
    update: function(element, valueAccessor) {
        var options = ko.unwrap(valueAccessor());

        // Aggiorna le opzioni di Select2 quando cambiano
        $(element).select2('destroy').select2({
            data: options
        });
    }
};




ko.observableArray.fn.swap = function (index1, index2) {
    this.valueWillMutate();

    var temp = this()[index1];
    this()[index1] = this()[index2];
    this()[index2] = temp;

    this.valueHasMutated();
}

ko.observableArray.fn.removeAt = function (index) {
    this.valueWillMutate();

    this().splice(index, 1);

    this.valueHasMutated();
}