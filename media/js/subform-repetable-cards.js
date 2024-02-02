document.addEventListener('DOMContentLoaded', function (ev) {

    initCards();

    document.addEventListener('subform-row-add', function (ev) {
        initCards();
    });

    function generateTile(el) {

        let tile = el.querySelector('.subform-card-tile');
        let text = '';
        let input = el.querySelector('input[type=text]');
        let inputImage = el.querySelector('.field-media-input');

        if (inputImage === undefined || inputImage === null) {
            if (el === undefined || el === null) {
                return;
            }

            let inputs = el.querySelectorAll('input');

            for (let i = 0; i < inputs.length; i++) {
                let val = inputs[i].value;

                //проверка на картинку
                if (val.test('.')) {
                    let arrayVal = val.split('.');
                    let exs = arrayVal.pop();
                    let exsImage = ['jpg', 'png', 'svg', 'jpeg', 'bmp', 'xcf', 'gif'];
                    if (exsImage.indexOf(exs.toLowerCase()) !== -1) {
                        let image = val;

                        if (image.charAt(0) !== '/') {
                            image = window.siteUrl + image;
                        }

                        tile.querySelector('.subform-card-tile-background').style.backgroundImage = 'url(' + image + ')';
                        tile.querySelector('.subform-card-tile-background').style.color = '#fff';
                        tile.querySelector('.subform-card-tile-background').style.backgroundSize = 'auto';
                    }
                }

                //проверка на ютуб
                if (val.test('youtu')) {
                    let id = youtubeParser(val);
                    let image = 'https://img.youtube.com/vi/' + id + '/hqdefault.jpg';

                    tile.querySelector('.subform-card-tile-background').style.backgroundImage = 'url(' + image + ')';
                    tile.querySelector('.subform-card-tile-background').style.color = '#fff';
                    tile.querySelector('.subform-card-tile-background').style.backgroundSize = 'auto';
                }
            }

            return;
        }

        if (input.length > 0) {
            text = input.value;
        }

        tile.querySelector('.subform-card-tile-title').innerHTML = capitalizeFirstLetter(text);

        if (inputImage !== undefined && inputImage !== null) {
            if (inputImage.value !== '') {

                let image = inputImage.value;

                if (image.charAt(0) !== '/') {
                    image = window.siteUrl + image;
                }

                tile.querySelector('.subform-card-tile-background').style.backgroundImage = 'url(' + image + ')';
                tile.querySelector('.subform-card-tile-background').style.color = '#fff';
                tile.querySelector('.subform-card-tile-background').style.backgroundSize = 'auto';
            }
        }
    }

    function initCards() {
        let cards = document.querySelectorAll('.subform-card');
        for (let i = 0; i < cards.length; i++) {
            initCard(cards[i]);
        }
    }

    function initCard(el) {

        if (el.getAttribute('data-init') === 'true') {
            return;
        }

        setTimeout(function () {
            generateTile(el);
        }, 200);

        el.querySelector('.subform-card-tile').addEventListener('click', function (e) {
            if (e.target.tagName === 'DIV') {
                let tile = e.target;
                let card = tile.closest('.subform-card');
                let grid = tile.closest('.subform-repeatable-cards');
                let content = card.querySelector('.subform-card-content');
                content.style.display = 'block';

                let grid_cards = grid.querySelectorAll('.subform-card-tile');

                for (let i = 0; i < grid_cards; i++) {
                    grid_cards[i].style.display = 'none';
                }
            }
        });

        el.querySelector('.button-subform-card-title-show').addEventListener('click', function (e) {
            let tile = e.target;
            let card = tile.closest('.subform-card');
            let grid = tile.closest('.subform-repeatable-cards');
            let content = card.querySelector('.subform-card-content');
            content.style.display = 'none';

            let grid_cards = grid.querySelectorAll('.subform-card-tile');

            for (let i = 0; i < grid_cards; i++) {
                grid_cards[i].style.display = 'block';
            }
        });

        el.setAttribute('data-init', 'true');

    }

    function hashCode(str) { // java String#hashCode
        let hash = 0;

        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        return hash;
    }

    function intToRGB(i) {
        let c = (i & 0x00FFFFFF)
            .toString(16)
            .toUpperCase();

        return "00000".substring(0, 6 - c.length) + c;
    }

    function randomInteger(min, max) {
        var rand = min - 0.5 + Math.random() * (max - min + 1)
        rand = Math.round(rand);
        return rand;
    }

    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    function youtubeParser(url) {
        var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
        var match = url.match(regExp);
        return (match && match[7].length == 11) ? match[7] : false;
    }

});
