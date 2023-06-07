window.RadicalMiltifieldImport = {

    insert: function (path, files) {

        let container = window.RadicalMiltifieldContainerActive;
        let options = JSON.parse(container.querySelector('.radicalmultifield-import').getAttribute('data-options'));
        let namefield = options.namefield;
        let namefile = options.namefile;
        let subform = container.querySelector('.subform-repeatable');

        for(let i=0;i<files.length;i++) {
            let subformRepeatableGroup;

            subform.querySelector('.group-add').click();
            subformRepeatableGroup = 'tbody tr:last-child input';

            let inputAll = document.querySelectorAll(subformRepeatableGroup);

            for (let j=0;j<inputAll.length;j++) {
                let name = inputAll[j].getAttribute('name');

                if(name === undefined || name === null) {
                    continue;
                }

                let fileName = files[i];
                let file = path + '/' + fileName;

                if (name.indexOf('[' + namefield + ']') > -1) {
                    inputAll[j].value = file;
                    QuantumUtils.triggerElementEvent('change', inputAll[j]);
                }

                if (name.indexOf('[' + namefile + ']') > -1) {
                    //jQuery(elt).val(fileName.split('.')[0].replace(/_[0-9]{0,}$/g, ''));
                    inputAll[j].value = fileName.split('.')[0];
                }


            }
        }
    }

};
