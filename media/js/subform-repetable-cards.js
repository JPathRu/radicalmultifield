jQuery(document).ready(function(){


    if(jQuery('.subform-repeatable-cards').find('.subform-card').length > 0) {
        jQuery('.subform-repeatable-cards .subform-card').each(function (i, el) {
            jQuery(el).attr('data-i', i);
            generateTile(el, i);
        });
    }


    jQuery(document).on('subform-row-add', function(event, row) {
        jQuery(row).attr('data-i', jQuery('.subform-repeatable-cards').find('.subform-card').length);
        setTimeout(function () {
            generateTile(row, jQuery('.subform-repeatable-cards').find('.subform-card').length);
        }, 200);
    });


    function generateTile(el, i) {


        if(i === null || i === undefined) {
            i = parseInt(jQuery(el).attr('data-i'));
        }

        let tile = jQuery(el).find('.subform-card-tile');
        let text = '';
        let input = jQuery(el).find('input[type=text]');
        let inputImage = jQuery(el).find('.field-media-input');

        if(inputImage.length === 0) {
            if(el === undefined || el === null) {
                return;
            }

            let inputs = el.querySelectorAll('input');

            for(let i=0;i<inputs.length;i++) {
                let val = inputs[i].value;

                //проверка на картинку
                if(val.test('.')) {
                    let arrayVal = val.split('.');
                    let exs = arrayVal.pop();
                    let exsImage = ['jpg', 'png', 'svg', 'jpeg', 'bmp', 'xcf', 'gif'];
                    if(exsImage.indexOf(exs.toLowerCase()) !== -1) {
                        let image = val;

                        if(image.charAt(0) !== '/') {
                            image = siteUrl + image;
                        }

                        tile.find('.subform-card-tile-background').css('background-image', 'url(' + image + ')');
                        tile.find('.subform-card-tile-background').css('color', '#fff');
                        tile.find('.subform-card-tile-background').css('background-size', 'auto');
                    }
                }

                //проверка на ютуб
                if(val.test('youtu')) {
                    let id = youtubeParser(val);
                    let image = 'https://img.youtube.com/vi/' + id + '/hqdefault.jpg';

                    tile.find('.subform-card-tile-background').css('background-image', 'url(' + image + ')');
                    tile.find('.subform-card-tile-background').css('color', '#fff');
                    tile.find('.subform-card-tile-background').css('background-size', 'auto');
                }
            }

            return;
        }


        if(input.length > 0) {
            text = input.val();
        }

        tile.find('.subform-card-tile-title').html(capitalizeFirstLetter(text));

        if(inputImage.length > 0) {
            if(inputImage.val() !== '') {
                let image = inputImage.val();

                if(image.charAt(0) !== '/') {
                    image = siteUrl + image;
                }

                tile.find('.subform-card-tile-background').css('background-image', 'url(' + image + ')');
                tile.find('.subform-card-tile-background').css('color', '#fff');
                tile.find('.subform-card-tile-background').css('background-size', 'auto');
            }
        }
    }


    jQuery('body').on('mousemove', '.subform-card-tile-background', function (e) {

        let x = parseFloat(jQuery(this).attr('data-x'));
        let y = parseFloat(jQuery(this).attr('data-y'));
        let oldX = parseFloat(jQuery(this).attr('data-oldX'));
        let oldY = parseFloat(jQuery(this).attr('data-oldY'));

        if(x === undefined || isNaN(x)) {
            x = 50;
        }

        if(y === undefined || isNaN(y)) {
            y = 50;
        }

        if(oldX === undefined || isNaN(oldX)) {
            oldX = e.pageX;
        }

        if(oldY === undefined || isNaN(oldY)) {
            oldY = e.pageY;
        }

        x = x + ((e.pageX - oldX)/5);
        y = y + ((e.pageY - oldY)/5);

        oldX = e.pageX;
        oldY = e.pageY;

        jQuery(this).attr('data-x', x);
        jQuery(this).attr('data-y', y);
        jQuery(this).attr('data-oldX', oldX);
        jQuery(this).attr('data-oldY', oldY);

        jQuery(this).css('background-position', x + '% ' + y + '%');
    });

    jQuery('body').on('click', '.subform-card-tile', function (e) {
        if(e.target.tagName === 'DIV') {
            let tile = jQuery(this);
            let card = jQuery(this).closest('.subform-card');
            let grid = jQuery(this).closest('.subform-repeatable-cards');
            let content = card.find('.subform-card-content');
            content.show();
            grid.find('.subform-card-tile').each(function (i, el) {
                jQuery(el).css('display', 'none');
            });
        }
    });


    jQuery('body').on('click', '.button-subform-card-title-show', function (e) {
        let tile = jQuery(this);
        let card = jQuery(this).closest('.subform-card');
        let grid = jQuery(this).closest('.subform-repeatable-cards');
        let content = card.find('.subform-card-content');
        content.hide();
        generateTile(this.closest('.subform-card'));
        grid.find('.subform-card-tile').each(function (i, el) {
            jQuery(el).css('display', 'block');
        });
    });


    function hashCode(str) { // java String#hashCode
        var hash = 0;
        for (var i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        return hash;
    }


    function intToRGB(i){
        var c = (i & 0x00FFFFFF)
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

    function youtubeParser(url){
        var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
        var match = url.match(regExp);
        return (match&&match[7].length==11)? match[7] : false;
    }

});
