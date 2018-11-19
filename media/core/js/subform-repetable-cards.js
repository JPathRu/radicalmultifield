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
            console.log(jQuery(el).attr('data-i'));
            i = parseInt(jQuery(el).attr('data-i'));
        }

        let tile = jQuery(el).find('.subform-card-tile');
        let text = '';
        let input = jQuery(el).find('input[type=text]');
        let inputImage = jQuery(el).find('.field-media-input');

        if(input.length > 0) {
            text = input.val();
        }

        if(text === '') {
            text = 'Элемент ' + i;
        }

        tile.find('.subform-card-tile-title').html(capitalizeFirstLetter(text));

        if(inputImage.length > 0) {
            if(inputImage.val() !== '') {
                let image = inputImage.val();

                if(image.charAt(0) !== '/') {
                    image = '/' + image;
                }

                tile.find('.subform-card-tile-background').css('background', 'linear-gradient(0deg,rgba(0,0,0,0.6),rgba(0,0,0,0.7)),url(' + image + ') no-repeat 0 0 fixed');
                tile.find('.subform-card-tile-background').css('color', '#fff');
            }
        }
    }


    jQuery('body').on('mousemove', '.subform-card-tile-background', function (e) {

        console.log(e.pageX);

        let x = jQuery(this).attr('data-x');
        let y = jQuery(this).attr('data-y');

        if(x === undefined) {
            x = 0;
        }

        if(y === undefined) {
            y = 0;
        }

        jQuery(this).attr('data-x', x);
        jQuery(this).attr('data-y', y);

        jQuery(this).css('background-position', x + 'px ' + y + 'px');
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
        generateTile(card);
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


});