/**
 * @package    quantummanager
 * @author     Dmitry Tsymbal <cymbal@delo-design.ru>
 * @copyright  Copyright © 2019 Delo Design & NorrNext. All rights reserved.
 * @license    GNU General Public License version 3 or later; see license.txt
 * @link       https://www.norrnext.com
 */

document.addEventListener('DOMContentLoaded', function () {

    setTimeout(function () {
        for (let i = 0; i < QuantummanagerLists.length; i++) {

            window.parent.RadicalMiltifieldModalActive.querySelector('.button-insert').addEventListener('click', function (ev) {
                QuantumUtils.ajaxGet(QuantumUtils.getFullUrl("index.php?option=com_quantummanager&task=quantumviewfiles.getParsePath&path=" + encodeURIComponent(QuantummanagerLists[i].data.path) + '&scope=' + QuantummanagerLists[i].data.scope + '&v=' + QuantumUtils.randomInteger(111111, 999999))).done(function (response) {
                    response = JSON.parse(response);
                    if (response.path !== undefined) {
                        let path = response.path;
                        let filesLists = [];
                        let objectAll = QuantummanagerLists[i].Quantumviewfiles.element.querySelectorAll('.field-list-files .object-select');

                        for (let i = 0; i < objectAll.length; i++) {
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

});