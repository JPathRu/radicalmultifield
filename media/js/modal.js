/**
 * @package    quantummanager
 * @author     Dmitry Tsymbal <cymbal@delo-design.ru>
 * @copyright  Copyright © 2019 Delo Design & NorrNext. All rights reserved.
 * @license    GNU General Public License version 3 or later; see license.txt
 * @link       https://www.norrnext.com
 */

document.addEventListener('DOMContentLoaded', function () {

    let buttonInsert = document.createElement('button');
    let buttonCancel = document.createElement('button');
    let pathFile;
    let altFile;

    buttonInsert.setAttribute('class', 'btn btn-primary');
    buttonInsert.setAttribute('type', 'button');
    buttonCancel.setAttribute('class', 'btn');
    buttonCancel.setAttribute('modal', 'modal');
    buttonCancel.setAttribute('data-dismiss', 'modal');
    buttonCancel.setAttribute('type', 'button');

    setTimeout(function () {
        for(let i=0;i<QuantummanagerLists.length;i++) {
            QuantummanagerLists[i].Quantumtoolbar.buttonAdd('insertFileEditor', 'center', 'file-actions', 'btn-insert btn-primary btn-hide', QuantumwindowLang.buttonInsert, 'quantummanager-icon-insert-inverse', {}, function (ev) {

                QuantumUtils.ajaxGet(QuantumUtils.getFullUrl("index.php?option=com_quantummanager&task=quantumviewfiles.getParsePath&path=" + encodeURIComponent(pathFile) + '&scope=' + QuantummanagerLists[i].data.scope + '&v=' + QuantumUtils.randomInteger(111111, 999999))).done(function (response) {
                    response = JSON.parse(response);
                    if(response.path !== undefined) {
                        let path = response.path.split('/');
                        let filesLists = [];
                        let objectAll = QuantummanagerLists[i].Quantumviewfiles.element.querySelectorAll('.field-list-files .object-select');
                        path.pop();
                        path = path.join('/');

                        for(let i=0;i<objectAll.length;i++) {
                            if (objectAll[i].querySelector('input').checked) {
                                filesLists.push(objectAll[i].getAttribute('data-fullname'));
                            }
                        }

                        window.parent.RadicalMiltifieldImport.insert(path, filesLists);
                        window.parent.RadicalMiltifieldModalActive.close();

                    }
                });

                ev.preventDefault();
            });
        }
    }, 300);

    QuantumEventsDispatcher.add('clickObject', function (fm) {
        let file = fm.Quantumviewfiles.objectSelect;

        if(file === undefined) {
            fm.Quantumtoolbar.buttonsList['insertFileEditor'].classList.add('btn-hide');
            return;
        }
    });

    QuantumEventsDispatcher.add('clickFile', function (fm) {
        let file = fm.Quantumviewfiles.file;

        if(file === undefined) {
            fm.Quantumtoolbar.buttonsList['insertFileEditor'].classList.add('btn-hide');
            return;
        }

        let name = file.querySelector('.file-name').innerHTML;
        let check = file.querySelector('.import-files-check-file');

        if(check.checked) {
            pathFile = fm.data.path + '/' + name;
            name.split('.').pop();
            altFile = name[0];
            fm.Quantumtoolbar.buttonsList['insertFileEditor'].classList.remove('btn-hide');
        } else {
            fm.Quantumtoolbar.buttonsList['insertFileEditor'].classList.add('btn-hide');
        }

    });

    QuantumEventsDispatcher.add('reloadPaths', function (fm) {
        if(
            fm.Quantumtoolbar.buttonsList['insertFileEditor'] !== null &&
            fm.Quantumtoolbar.buttonsList['insertFileEditor'] !== undefined
        ) {
            fm.Quantumtoolbar.buttonsList['insertFileEditor'].classList.add('btn-hide');
        }
    });

    QuantumEventsDispatcher.add('updatePath', function (fm) {
        if(
            fm.Quantumtoolbar.buttonsList['insertFileEditor'] !== null &&
            fm.Quantumtoolbar.buttonsList['insertFileEditor'] !== undefined
        ) {
            fm.Quantumtoolbar.buttonsList['insertFileEditor'].classList.add('btn-hide');
        }
    });

    QuantumEventsDispatcher.add('uploadComplete', function (fm) {

        if(fm.Qantumupload.filesLists.length === 0) {
            return;
        }

        let name = fm.Qantumupload.filesLists[0];
        pathFile = fm.data.path + '/' + fm.Qantumupload.filesLists[0];
        name.split('.').pop();
        altFile = name[0];
        fm.Quantumtoolbar.buttonsList['insertFileEditor'].classList.remove('btn-hide');

    });

});