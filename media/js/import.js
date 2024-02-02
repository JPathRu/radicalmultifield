window.RadicalMiltifieldImport = {

    insert: function (path, files) {

        let container = window.RadicalMiltifieldContainerActive;
        let options = JSON.parse(container.querySelector('.radicalmultifield-import').getAttribute('data-options'));
        let namefield = options.namefield;
        let namefile = options.namefile;
        let subform = container.querySelector('.subform-repeatable');

        for (let i = 0; i < files.length; i++) {
            subform.querySelector('.group-add').click();
            let inputAll = document.querySelectorAll('.subform-repeatable-group:last-child input');

            for (let j = 0; j < inputAll.length; j++) {
                let name = inputAll[j].getAttribute('name');

                if (name === undefined || name === null) {
                    continue;
                }

                let fileName = files[i];
                let file = path + '/' + fileName;

                if (name.indexOf('[' + namefield + ']') > -1) {
                    let media_field = inputAll[j].closest('joomla-field-media')

                    if (media_field !== null && media_field !== undefined) {
                        inputAll[j].value = file;
                        QuantumUtils.triggerElementEvent('change', inputAll[j]);
                        media_field.updatePreview(file);
                    } else {
                        inputAll[j].value = file;
                        QuantumUtils.triggerElementEvent('change', inputAll[j]);
                    }

                }

                if (name.indexOf('[' + namefile + ']') > -1) {
                    inputAll[j].value = fileName.split('.')[0];
                    QuantumUtils.triggerElementEvent('change', inputAll[j]);
                }

            }
        }
    }

};
