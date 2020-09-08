document.addEventListener('DOMContentLoaded' ,function () {

    QuantumEventsDispatcher.add('uploadComplete', function (fm) {
        if (fm.Qantumupload.filesLists.length === 0) {
            return
        }
        let name = fm.Qantumupload.filesLists[0];
        let pathFile = fm.data.path;
        let container = fm.element.closest('.control-group');
        RadicalMiltifieldImport.insert(container, 'image', 'alt', pathFile, fm.Qantumupload.filesLists);
    });

});
