document.addEventListener('DOMContentLoaded', function () {

    window.RadicalMiltifieldContainerActive = '';
    window.RadicalMiltifieldModalActive = '';
    let buttons_fast = document.querySelectorAll('.btn-radicalmiltifield-fast-upload');
    let buttons_select = document.querySelectorAll('.btn-radicalmiltifield-select');

    for (let i = 0; i < buttons_fast.length; i++) {
        buttons_fast[i].addEventListener('click', function (ev) {
            let filemanager = this.closest('.import-wrap').querySelector('.quantummanager'),
                index = parseInt(filemanager.getAttribute('data-index'));
            QuantummanagerLists[index].Qantumupload.selectFiles();
            RadicalMiltifieldContainerActive = QuantummanagerLists[index].element.closest('.control-group');
            ev.preventDefault();
        });
    }

    for (let i = 0; i < buttons_select.length; i++) {
        buttons_select[i].addEventListener('click', function (ev) {
            let filemanager = this.closest('.import-wrap').querySelector('.quantummanager'),
                index = parseInt(filemanager.getAttribute('data-index')),
                modal_id = this.closest('.import-wrap').getAttribute('data-modal-id');
            RadicalMiltifieldContainerActive = QuantummanagerLists[index].element.closest('.control-group');

            let modal = document.querySelector('#' + modal_id);
            if (!modal) {
                return;
            }

            Joomla.initialiseModal(modal, {isJoomla: true});

            modal.addEventListener('shown.bs.modal', (event) => {
                Joomla.Modal.setCurrent(event.target);
            });

            let currentModal = Joomla.Modal.getCurrent();
            if (currentModal) {
                currentModal.close();
            }

            RadicalMiltifieldModalActive = modal;

            document.getElementById(modal_id).open();

            ev.preventDefault();
        });
    }

    QuantumEventsDispatcher.add('uploadComplete', function (fm) {
        if (fm.Qantumupload.filesLists.length === 0) {
            return
        }
        let name = fm.Qantumupload.filesLists[0];

        QuantumUtils.ajaxGet(QuantumUtils.getFullUrl("index.php?option=com_quantummanager&task=quantumviewfiles.getParsePath&path=" + encodeURIComponent(fm.data.path) + '&scope=' + fm.data.scope + '&v=' + QuantumUtils.randomInteger(111111, 999999))).done(function (response) {
            response = JSON.parse(response);
            if (response.path !== undefined) {
                let pathFile = response.path;
                RadicalMiltifieldContainerActive = fm.element.closest('.control-group');
                RadicalMiltifieldImport.insert(pathFile, fm.Qantumupload.filesLists);
            }
        });

    });

});
