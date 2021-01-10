window.RadicalMiltifieldImport = {

    insert: function (path, files) {

        let container = window.RadicalMiltifieldContainerActive;
        let options = JSON.parse(container.querySelector('.radicalmultifield-import').getAttribute('data-options'));
        let namefield = options.namefield;
        let namefile = options.namefile;
        let subform = jQuery(container).find('.subform-repeatable');

        for(let i=0;i<files.length;i++) {
            let subformRepeatableGroup;

            if (/^div.subform-repeatable-group.*?$/.test(subform.attr('data-repeatable-element'))) {
                //subform.find('.group-add').click();
                subformRepeatableGroup = '.subform-repeatable-group:last-child input';
            }

            if (/^tr.subform-repeatable-group.*?$/.test(subform.attr('data-repeatable-element'))) {
                subform.find('thead .btn').click();
                subformRepeatableGroup = 'tbody tr:last-child input';
            }

            if (/^div.subform-repeatable-group.*?$/.test(subform.attr('data-repeatable-element'))) {
                subform.find('.btn-toolbar:first .btn').click();
                subformRepeatableGroup = '.subform-card:last input';
            }

            let inputAll = document.querySelectorAll(subformRepeatableGroup);

            for (let j=0;j<inputAll.length;j++) {
                let name = jQuery(inputAll[j]).attr('name');

                if(name === undefined || name === null) {
                    continue;
                }

                let fileName = files[i];
                let file = path + '/' + fileName;

                if (name.indexOf('[' + namefield + ']') > -1) {
                    jQuery(inputAll[j]).val(file);
                    QuantumUtils.triggerElementEvent('change', inputAll[j]);
                }

                if (name.indexOf('[' + namefile + ']') > -1) {
                    //jQuery(elt).val(fileName.split('.')[0].replace(/_[0-9]{0,}$/g, ''));
                    jQuery(inputAll[j]).val(fileName.split('.')[0]);
                }


            }
        }
    }

};
