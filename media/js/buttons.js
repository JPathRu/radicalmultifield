document.addEventListener('DOMContentLoaded' ,function () {

    window.RadicalMiltifieldContainerActive = '';

    let buttons_fast = document.querySelectorAll('.btn-fast-upload');
    let buttons_select = document.querySelectorAll('.btn-radicalmiltifield-select');

    for(let i=0;i<buttons_fast.length;i++) {
        buttons_fast[i].addEventListener('click', function (ev) {
            let filemanager = this.closest('.import-wrap').querySelector('.quantummanager'),
                index = parseInt(filemanager.getAttribute('data-index'));
            QuantummanagerLists[index].Qantumupload.selectFiles();
            RadicalMiltifieldContainerActive = QuantummanagerLists[index].element.closest('.control-group');
            ev.preventDefault();
        });
    }

    for(let i=0;i<buttons_select.length;i++) {
        buttons_select[i].addEventListener('click', function (ev) {
            let filemanager = this.closest('.import-wrap').querySelector('.quantummanager'),
                index = parseInt(filemanager.getAttribute('data-index'));
            RadicalMiltifieldContainerActive = QuantummanagerLists[index].element.closest('.control-group');
            ev.preventDefault();
        });
    }

    QuantumEventsDispatcher.add('uploadComplete', function (fm) {
        if (fm.Qantumupload.filesLists.length === 0) {
            return
        }
        let name = fm.Qantumupload.filesLists[0];
        let pathFile = fm.data.path;
        RadicalMiltifieldContainerActive = fm.element.closest('.control-group');
        RadicalMiltifieldImport.insert(pathFile, fm.Qantumupload.filesLists);
    });

});
