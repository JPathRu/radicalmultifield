jQuery(function(){

    let listfullpath = [];
    let historyDirectories = [];
    let results;
    let modal;
    let modalName;
    let filesListWrap;
    let importfieldpath;
    let importfield;
    let maxsize;
    let exs;
    let namefield;
    let namefile;
    let active;
    let upload;
    let flagSwitchingSearch = false;
    let subform;
    let path;
    let activeLists = [];
    let dropAreaAll = document.querySelectorAll(".drop-area");
    let inputFileAll = document.querySelectorAll(".fileElem");
    let dropArea;
    let inputPath;
    let inputFile;
    let errorsWrap;
    let errorsHtml;
    let lastTypeViewFiles = 'list-table';
    let uploadProgress = [];
    let countFiles = 0;
    let progressBar;
    let uploadI = [];
    let speedUpload = false;
    let speedUploadComplete = false;
    vex.defaultOptions.className = 'vex-theme-plain';
    vex.dialog.buttons.YES.text = 'Ок';
    vex.dialog.buttons.NO.text = 'Нет';
    vex.dialog.buttons.NO.text = 'Отмена';
    let btnback = Joomla.JText._('PLG_RADICAL_MULTI_FIELD_BUTTON_BACK');
    let btnup = Joomla.JText._('PLG_RADICAL_MULTI_FIELD_BUTTON_UP');
    let btngrid = Joomla.JText._('PLG_RADICAL_MULTI_FIELD_BUTTON_GRID');
    let btnlist = Joomla.JText._('PLG_RADICAL_MULTI_FIELD_BUTTON_LIST');
    let btnselectall = Joomla.JText._('PLG_RADICAL_MULTI_FIELD_BUTTON_SELECTALL');

    jQuery('.button-open-modal').on('click', function() {
        modalName = jQuery(this).attr('href');
        modal = jQuery(modalName);
        filesListWrap = document.querySelector(modalName + ' .field-list-files');
        importfieldpath = jQuery(this).attr('data-importfieldpath');
        importfield = jQuery(this).attr('data-importfield');
        maxsize = parseFloat(jQuery(this).attr('data-maxsize'));
        exs = jQuery(this).attr('data-exs').split(',');
        namefield = jQuery(this).attr('data-namefield');
        namefile = jQuery(this).attr('data-namefile');
        active = modal.find('.level-0 > li');
        activeLists = [];
        subform = jQuery(this).closest('.control-group').find('.subform-repeatable');
        progressBar = document.querySelector(modalName + " .progress-bar");
        dropArea = document.querySelector(modalName + " .drop-area");
        inputPath = document.querySelector(modalName + " .pathElem");
        inputFile = document.querySelector(modalName + " .fileElem");
        errorsWrap = document.querySelector(modalName + " .upload-errors");
        reloadListfullpath();
        openDirectoryAndActive(active, 'root');
        modal.removeClass('modal-speed-upload');
        progressBar.style.display = "none";

        if(localStorage !== undefined) {
            let openLastDir = localStorage.getItem('radicalmultifieldLastDir');
            if(openLastDir !== null) {
                for (let i=0;i<=listfullpath.length;i++) {
                    if(listfullpath[i].p === openLastDir) {
                        openDirectoryAndActive(listfullpath[i].el, listfullpath[i].p);
                        break;
                    }
                }
            }

            let currLastTypeViewFiles = localStorage.getItem('lastTypeViewFiles');
            if(currLastTypeViewFiles !== null) {
                lastTypeViewFiles = currLastTypeViewFiles;
            }

        }

        if(speedUpload) {
            //окно не показываем
            speedUpload = false;
            speedUploadComplete = false;
            modal.hide();
            modal.addClass('modal-speed-upload');
            return false;
        } else {
            modal.removeClass('modal-speed-upload');
        }

    });


    jQuery('.modal-import-file').on('click', '.av-folderlist-tree', function() {
        jQuery(this).parent().toggleClass('open');
    });


    jQuery('.modal-import-file').on('click', '.av-folderlist-label', function() {
        let self = jQuery(this);
        let modal = self.closest('.modal-import-file');
        let listfiles = modal.find('.field-list-files .list');
        let list = self.closest('.av-folderlist');
        active = self.closest('.av-folderlist-dir');
        list.find('.av-folderlist-label').removeClass('selected');
        jQuery(this).addClass('selected');
        listfiles.html('');
        jQuery.get(siteUrl + "/administrator/index.php?option=com_ajax&plugin=radicalmultifield&group=fields&format=json&type=get_files&directory=" + encodeURIComponent(self.attr('path')) +
            "&importfieldpath=" + encodeURIComponent(importfieldpath) +
            "&importfield=" + encodeURIComponent(importfield)
        ).done(function (response) {
            path = self.attr('path');
            self.closest('.field-wrapper').find('.import-directory').val(path);

            if(inputPath.value !== '') {
                if(historyDirectories.length>0) {
                    if(historyDirectories[historyDirectories.length - 1] !== inputPath.value) {
                        historyDirectories.push(inputPath.value);
                    }
                } else {
                    historyDirectories.push(inputPath.value);
                }
            }
            inputPath.value = path;


            let htmlfilesAndDirectories = '<div class="files-header"><div><label><input type="checkbox" class="import-files-check-all"> ' + btnselectall + '</label></div><div><button class="button-prev"><span>' + btnback + '</span></button><button class="button-up"><span>' + btnup + '</span></button><div class="button-dropdown"><button class="button-directory-trash"><span>Удалить директорию</span></button><div class="dropdown-content">Удалить: <b>" + path+ "</b>?<div><button><span>Удалить</span></button></div></div></div><button class="button-grid"><span>' + btngrid + '</span></button><button class="button-table"><span>' + btnlist + '</span></button></div></div>';
            let files = response.data[0].files;
            let directories = response.data[0].directories;

            for(let i = 0;i<directories.length;i++) {
                htmlfilesAndDirectories += "<div class='directory-item'><div class='directory'><div class='directory-icon'><span></span></div><div class='directory-name'>" + directories[i] + "</div></div></div>" ;
            }

            for(let i = 0;i<files.length;i++) {
                let type = files[i].split('.');
                let findCheck = (activeLists.indexOf(files[i]) !== -1);
                htmlfilesAndDirectories += "<div class='file-item'><input type=\"checkbox\" class=\"import-files-check-file\" " + (findCheck ? 'checked="checked"' : '') + "><div class='file'><div class='file-exs av-folderlist icon-file-" + type.pop() + "'><div class='av-folderlist-label'></div></div><div class='file-name'>" + files[i] + "</div></div></div>" ;
            }

            htmlfilesAndDirectories += "</div>";

            if(files.length === 0 && directories === 0) {
                htmlfilesAndDirectories = "<div class='empty'><div>Файлов нет.</div></div>"
            }

            listfiles.html(htmlfilesAndDirectories);
            reloadViewFiles();

            let filesAll = document.querySelectorAll(modalName + ' .field-list-files .file-item');
            let directoriesAll = document.querySelectorAll(modalName + ' .field-list-files .directory-item');
            let buttonBack = document.querySelector(modalName + ' .button-prev');
            let buttonUp = document.querySelector(modalName + ' .button-up');
            let buttonDelete = document.querySelector(modalName + ' .button-directory-trash');

            if(errorsHtml === '') {

                for(let i=0;i<filesAll.length;i++) {
                    let input = filesAll[i].querySelector('.import-files-check-file');
                    if(input.checked) {
                        filesListWrap.scrollTop = filesAll[i].getBoundingClientRect().top - 120;
                        break;
                    }
                }

            } else {
                filesListWrap.scrollTop = 0;
            }

            if(localStorage !== undefined) {
                localStorage.setItem('radicalmultifieldLastDir', path);
            }

            buttonBack.addEventListener('click', function (ev) {
                if(historyDirectories.length > 0) {
                    let directory = historyDirectories[historyDirectories.length - 1];
                    for (let j=0;j<=listfullpath.length;j++) {
                        if(listfullpath[j].p === directory) {
                            openDirectoryAndActive(listfullpath[j].el, listfullpath[j].p);
                            historyDirectories.splice(historyDirectories.length - 2, 2);
                            break;
                        }
                    }
                }
                ev.preventDefault();
            });

            buttonUp.addEventListener('click', function (ev) {
                let currDirectories = inputPath.value.split('/');
                if(currDirectories.length > 1) {
                    currDirectories.pop();
                    let directory = currDirectories.join('/');
                    for (let j=0;j<=listfullpath.length;j++) {
                        if(listfullpath[j].p === directory) {
                            openDirectoryAndActive(listfullpath[j].el, listfullpath[j].p);
                            break;
                        }
                    }
                }
                ev.preventDefault();
            });

            buttonDelete.addEventListener('click', function (ev) {
                ev.preventDefault();
            });

            for(let i=0;i<filesAll.length;i++) {
                filesAll[i].addEventListener('click', function () {
                    let tmpInput = this.closest('.file-item').querySelector('.import-files-check-file');
                    tmpInput.checked = !tmpInput.checked;
                    tmpInput.click();
                });
            }

            for(let i=0;i<directoriesAll.length;i++) {
                directoriesAll[i].addEventListener('click', function () {
                    let directory = this.querySelector('.directory-name').innerHTML;
                    directory = path + '/' + directory;
                    for (let j=0;j<=listfullpath.length;j++) {
                        if(listfullpath[j].p === directory) {
                            openDirectoryAndActive(listfullpath[j].el, listfullpath[j].p);
                            break;
                        }
                    }
                });
            }

            setTimeout(function () {

                if(speedUploadComplete && modal.hasClass('modal-speed-upload')) {
                    speedUpload = false;
                    speedUploadComplete = false;
                    jQuery(modalName + ' .button-import-start').click();
                }

            }, 200);

        });
    });

    jQuery('.modal-import-file').on('dblclick', '.av-folderlist-label', function() {
        jQuery(this).prev().click();
    });

    jQuery('.av-modal-actions .search input').on('keyup', function(e) {

        let search = jQuery(this).val();
        let listhtml = '';
        results = jQuery(this).parent().find('.results');
        for (let i = 0; i < listfullpath.length; i++) {
            if (listfullpath[i].p.toLowerCase().indexOf(search) > -1) {
                listhtml += "<div data-i='" + i + "'>" + listfullpath[i].p + "</div>"
            }
        }

        if(listfullpath.length === 0) {
            listhtml += "<div>Результатов нет</div>"
        }

        results.html(listhtml);
        results.show();
    });

    jQuery('.av-modal-actions .search .results').on('click', 'div', function() {
        jQuery('.av-modal-actions .search input').val('');
        results.hide();
        openDirectoryAndActive(listfullpath[parseInt(jQuery(this).attr('data-i'))].el, listfullpath[parseInt(jQuery(this).attr('data-i'))].p);
        return false;
    });

    jQuery('.modal-import-file .create-directory').on('click', function() {

        if(active === undefined) {
            vex.dialog.alert({
                unsafeMessage: '<b>Выберите директорию для создания.</b>'
            });
            return false;
        }

        modal.hide();
        vex.dialog.prompt({
            message: 'Введите название директории',
            placeholder: '',
            callback: function (value) {
                let newName = value;
                if (newName) {
                    jQuery.get(siteUrl + "/administrator/index.php?option=com_ajax&plugin=radicalmultifield&group=fields&format=raw&type=create_directory&name=" +
                        encodeURIComponent(newName) +
                        "&path=" + encodeURIComponent(active.find('.av-folderlist-label').attr('path')) +
                        "&importfieldpath=" + encodeURIComponent(importfieldpath) +
                        "&importfield=" + encodeURIComponent(importfield))
                        .done(function (response) {
                            jQuery(modalName + ' .av-folderlist').remove();
                            jQuery(modalName + ' .av-modal').append(response);
                            reloadListfullpath();
                            changeActiveFromPath(active.find('.av-folderlist-label').attr('path') + '/' + newName);
                            openDirectoryAndActive(active, active.find('.av-folderlist-label').attr('path'));
                            modal.show();
                        });
                } else {
                    modal.show();
                }
            }
        });

        return false;
    });

    jQuery('.modal-import-file .tree-reload').on('click', function() {
        let lastActive = active.find('.av-folderlist-label').attr('path');
        let bufferScrollTop = jQuery('.av-folderlist').scrollTop();
        jQuery.get(siteUrl + "/administrator/index.php?option=com_ajax&plugin=radicalmultifield&group=fields&format=raw&type=get_directories" +
            "&importfieldpath=root" +
            "&importfield=" + encodeURIComponent(importfield)
        ).done(function (response) {
            jQuery('.av-modal ul').remove();
            jQuery('.av-modal').append(response);
            reloadListfullpath();
            if(lastActive !== null) {
                for (let i=0;i<=listfullpath.length;i++) {
                    if(listfullpath[i].p === lastActive) {
                        openDirectoryAndActive(listfullpath[i].el, listfullpath[i].p);
                        break;
                    }
                }
            }
            jQuery('.av-folderlist').scrollTop(bufferScrollTop);
        });
        return false;
    });


    jQuery('.modal-import-file').on('click', '.import-files-check-all', function() {
        if(jQuery(this).attr("checked")) {
            jQuery('.modal-import-file .field-list-files input').each(function (i, el) {
                if(!jQuery(el).attr("checked")) {
                    jQuery(el).prop('checked', true);
                    let currFilename = jQuery(this).closest('.file-item').find('.file-name').html();
                    let currI = activeLists.indexOf(currFilename);

                    if(currI === -1) {
                        activeLists.push(currFilename);
                        //jQuery(this).closest('tr').addClass('active');
                    }

                }
            });
        } else {
            activeLists = [];
            jQuery('.modal-import-file .field-list-files input').each(function (i, el) {
                if(jQuery(el).attr("checked")) {
                    jQuery(el).prop('checked', false);
                    //jQuery(this).closest('tr').removeClass('active');
                }
            });
        }
    });



    jQuery('.modal-import-file').on('click', '.import-files-check-file', function() {
        let currFilename = jQuery(this).closest('tr').find('.file-name').html();
        let currI = activeLists.indexOf(currFilename);

        if(jQuery(this).attr("checked")) {
            if(currI === -1) {
                activeLists.push(currFilename);
                //jQuery(this).closest('tr').addClass('active');
            }
        } else {
            if(currI !== -1) {
                activeLists.splice(currI, 1);
                //jQuery(this).closest('tr').removeClass('active');
            }
        }
    });

    jQuery('.modal-import-file').on('click', '.upload-errors-close', function () {
        jQuery(this).parent().hide();
        return false;
    });


    jQuery('.import-wrap').on('click', '.speed-upload', function () {
        let modalTmp = jQuery(this).closest('.import-wrap');
        speedUpload = true;
        modalTmp.find('.button-open-modal').click();
        modalTmp.find('.fileElem').click();
        return false;
    });

    function reloadListfullpath() {
        listfullpath = [];
        jQuery(modalName + ' .av-folderlist-dir').each(function (i, el) {

            listfullpath.push({
                el: jQuery(el),
                p: jQuery(el).find('.av-folderlist-label').attr('path')
            });

            if(jQuery(el).find('.av-folderlist-label').attr('path') === active.find('.av-folderlist-label').attr('path')) {
                active = jQuery(el);
            }

        });
    }

    function changeActiveFromPath(path) {
        for(let i=0;i<listfullpath.length;i++) {
            if(listfullpath[i].p === path) {
                active = listfullpath[i].el;
            }
        }
    }

    function reloadViewFiles() {
        let buttonGrid = document.querySelector(modalName + ' .field-list-files .button-grid');
        let buttonTable = document.querySelector(modalName + ' .field-list-files .button-table');

        if(buttonGrid === null && buttonTable === null) {
            return;
        }

        buttonGrid.addEventListener('click', function (ev) {
            if(localStorage !== null) {
                localStorage.setItem('lastTypeViewFiles', 'list-grid');
            }

            lastTypeViewFiles = 'list-grid';
            reloadTypeViewFiles();
            ev.preventDefault();
        });

        buttonTable.addEventListener('click', function (ev) {
            if(localStorage !== null) {
                localStorage.setItem('lastTypeViewFiles', 'list-table');
            }

            lastTypeViewFiles = 'list-table';
            reloadTypeViewFiles();
            ev.preventDefault();
        });

        reloadTypeViewFiles();
    }

    function reloadTypeViewFiles() {
        let filesAll = document.querySelectorAll(modalName + ' .field-list-files .file-item');
        let viewFiles = document.querySelector(modalName + ' .field-list-files .list');

        if(lastTypeViewFiles === 'list-grid') {
            viewFiles.setAttribute('class', 'list list-grid');
        }

        if(lastTypeViewFiles === 'list-table') {
            viewFiles.setAttribute('class', 'list list-table');
        }

        for(let i=0;i<filesAll.length;i++) {

            if(lastTypeViewFiles === 'list-grid') {
                let fileName = filesAll[i].querySelector('.file-name').innerHTML;
                let fileExs = filesAll[i].querySelector('.file-exs');
                let exs = fileName.split('.')[1];
                let exsImage = ['jpg', 'png', 'svg', 'jpeg', 'bmp', 'xcf', 'gif'];
                if(exsImage.indexOf(exs) !== -1) {
                    let file = "/" + path.replace('root', importfieldpath) + '/' + fileName;
                    fileExs.style.backgroundImage = "url(" + file + ")";
                } else {
                    let exsAvailable = ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'mp3', 'ogg', 'flac', 'pdf', 'zip', 'txt'];
                    if(exsAvailable.indexOf(exs) !== -1) {
                        let file = "/media/plg_fields_radicalmultifield/img/icons/" + exs + ".svg";
                        fileExs.style.backgroundImage = "url(" + file + ")";
                        fileExs.classList.add('file-icons');
                    } else {
                        let file = "/media/plg_fields_radicalmultifield/img/icons/other.svg";
                        fileExs.style.backgroundImage = "url(" + file + ")";
                        fileExs.classList.add('file-icons');
                    }
                }

            }

            if(lastTypeViewFiles === 'list-table') {
                let fileExs = filesAll[i].querySelector('.file-exs');
                fileExs.style.backgroundImage = "";
            }

        }
    }

    function openDirectoryAndActive(el, p) {
        let tmpLi = el;
        let maxI = 1000;
        let currentI = 0;
        while(true) {

            if(currentI > maxI) {
                break;
            }

            currentI++;
            if(!tmpLi.hasClass('av-folderlist-dir')) {
                tmpLi = tmpLi.parent();
                continue;
            }

            if(!tmpLi.hasClass('open')) {
                tmpLi.addClass('open');
                tmpLi = tmpLi.parent();
            } else {
                break;
            }
        }
        el.find('.av-folderlist-label[path="' + p + '"]').click();
    }


    ;['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        for (let i = 0; i < dropAreaAll.length; i++) {
            dropAreaAll[i].addEventListener(eventName, preventDefaults, false);
        }
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    // Highlight drop area when item is dragged over it
    ;['dragenter', 'dragover'].forEach(eventName => {
        for (let i = 0; i < dropAreaAll.length; i++) {
            dropAreaAll[i].addEventListener(eventName, highlight, false);
        }
    });

    ;['dragleave', 'drop'].forEach(eventName => {
        for (let i = 0; i < dropAreaAll.length; i++) {
            dropAreaAll[i].addEventListener(eventName, unhighlight, false);
        }
    });

    for (let i = 0; i < dropAreaAll.length; i++) {
        dropAreaAll[i].addEventListener('drop', handleDrop, false);
    }


    for (let i = 0; i < inputFileAll.length; i++) {
        inputFileAll[i].addEventListener('change', function () {
            handleFiles(this.files);
        }, false);
    }


    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight(e) {
        dropArea.classList.add('highlight');
    }

    function unhighlight(e) {
        dropArea.classList.remove('active');
    }

    function handleDrop(e) {
        let dt = e.dataTransfer;
        let files = dt.files;

        handleFiles(files)
    }


    function initializeProgress(numFiles) {
        progressBar.style.display = "block";
        progressBar.value = 0;
        uploadProgress = [];
        countFiles = numFiles;

        for (let i = numFiles; i > 0; i--) {
            uploadProgress.push(0);
        }
    }

    function updateProgress(fileNumber, percent) {
        uploadProgress[fileNumber] = percent;
        let total = uploadProgress.reduce((tot, curr) => tot + curr, 0) / uploadProgress.length;
        progressBar.value = total;
    }

    function handleFiles(files) {
        if(active === undefined) {
            vex.dialog.alert({
                unsafeMessage: '<b>Выберите каталог для загрузки</b>'
            });
        }
        files = [...files];
        initializeProgress(files.length);
        errorsHtml = '';
        uploadI = [];
        files.forEach(uploadFile);
    }


    function uploadFile(file, i) {

        errorsWrap.style.display = "none";

        if((file.size  / 1024 / 1024) > maxsize) {

            vex.dialog.alert({
                unsafeMessage: '<b>Файл ' + file.name + ' не должен превышать ' + maxsize + ' мегабайта.</b>'
            });

            countFiles--;

            if(countFiles === 0) {
                progressBar.style.display = "none";
            }

            return false;
        }

        let currExs = file.name.split('.');

        if(currExs.length === 1) {

            vex.dialog.alert({
                unsafeMessage: '<b>Файл ' + file.name + ' должен иметь расширение.</b>'
            });

            countFiles--;

            if(countFiles === 0) {
                modal.removeClass('modal-speed-upload');
                progressBar.style.display = "none";
            }

            return false;
        }

        if (exs.indexOf(currExs.pop().toLowerCase()) === -1) {

            vex.dialog.alert({
                unsafeMessage: '<b>Файл ' + file.name + ' должен иметь расширения: ' + exs.join(',') + '.</b>'
            });

            countFiles--;

            if(countFiles === 0) {
                modal.removeClass('modal-speed-upload');
                progressBar.style.display = "none";
            }

            return false;
        }


        let url = siteUrl + "/administrator/index.php?option=com_ajax&plugin=radicalmultifield&group=fields&format=json&type=upload_file"
            + "&importfieldpath=" + encodeURIComponent(importfieldpath)
            + "&importfield=" + encodeURIComponent(importfield);
        let xhr = new XMLHttpRequest();
        let formData = new FormData();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.addEventListener("progress", function (e) {
            updateProgress(i, (e.loaded * 100.0 / e.total) || 100);
        });

        xhr.addEventListener('readystatechange', function (e) {
            if (xhr.readyState == 4 && xhr.status == 200) {
                let response = JSON.parse(xhr.response);

                if(response.data[0].name !== undefined) {
                    activeLists.push(response.data[0].name);
                }

                if(response.data[0].error !== undefined) {
                    errorsHtml += '<div>' + file.name + ': ' + response.data[0].error + '</div>';
                }

                updateProgress(i, 100);

                uploadI.push((i + 1));

                if(countFiles === uploadI.length) {
                    speedUploadComplete = true;
                    active.find('.av-folderlist-label[path="' + inputPath.value + '"]').click();
                    jQuery('.modal-import-file .tree-reload').click();
                    progressBar.style.display = "none";

                    if(errorsHtml !== '') {
                        errorsWrap.querySelector('div').innerHTML =  errorsHtml;
                        errorsWrap.style.display = "block";
                    }

                }

            }
            else if (xhr.readyState == 4 && xhr.status != 200) {

                uploadI.push((i + 1));

                if(countFiles === uploadI.length) {
                    speedUploadComplete = true;
                    active.find('.av-folderlist-label[path="' + inputPath.value + '"]').click();
                    jQuery('.modal-import-file .tree-reload').click();
                    progressBar.style.display = "none";

                    if(errorsHtml !== '') {
                        errorsWrap.innerHTML =  errorsHtml;
                        errorsWrap.style.display = "block";
                    }

                }

            }


        });

        formData.append('path', inputPath.value);
        formData.append('file', file);
        xhr.send(formData);
    }

    jQuery('.button-import-start').on('click', function () {

        modal.removeClass('modal-speed-upload');

        if(activeLists.length === 0) {

            vex.dialog.alert({
                unsafeMessage: '<b>Выберите файлы из списка</b>',
            });

            return false;
        }

        jQuery(modalName + ' .field-list-files .file-item').each(function (i, el) {

            if(!jQuery(el).find('.import-files-check-file').attr('checked')) {
                return;
            }

            let subformRepeatableGroup;

            if(/^div.subform-repeatable-group.*?$/.test(subform.attr('data-repeatable-element'))) {
                //subform.find('.group-add').click();
                subformRepeatableGroup = '.subform-repeatable-group:last-child input';
            }

            if(/^tr.subform-repeatable-group.*?$/.test(subform.attr('data-repeatable-element'))) {
                subform.find('thead .btn').click();
                subformRepeatableGroup = 'tbody tr:last-child input';
            }

            if(/^div.subform-repeatable-group.*?$/.test(subform.attr('data-repeatable-element'))) {
                subform.find('.btn-toolbar:first .btn').click();
                subformRepeatableGroup = '.subform-card:last input';
            }

            subform.find(subformRepeatableGroup).each(function (k, elt) {
                let name = jQuery(elt).attr('name');
                let fileName = jQuery(el).find('.file-name').html();
                let file = importfieldpath + path.replace('root', '') + '/' + fileName;

                if(name.indexOf('[' + namefield + ']') > -1) {
                    jQuery(elt).val(file);
                }

                if(name.indexOf('[' + namefile + ']') > -1) {
                    //jQuery(elt).val(fileName.split('.')[0].replace(/_[0-9]{0,}$/g, ''));
                    jQuery(elt).val(fileName.split('.')[0]);
                }
            });
        });

        jQuery(modalName).modal('hide');

        return false;
    });


});
