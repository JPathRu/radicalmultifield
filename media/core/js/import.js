window.RadicalMiltifieldImport = {

    insert: function (container, namefield, namefile, path, files) {

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

            subform.find(subformRepeatableGroup).each(function (k, elt) {
                let name = jQuery(elt).attr('name');
                let fileName = files[i];
                let file = path + '/' + fileName;

                if (name.indexOf('[' + namefield + ']') > -1) {
                    jQuery(elt).val(file);
                }

                if (name.indexOf('[' + namefile + ']') > -1) {
                    //jQuery(elt).val(fileName.split('.')[0].replace(/_[0-9]{0,}$/g, ''));
                    jQuery(elt).val(fileName.split('.')[0]);
                }
            });
        }
    }

};
