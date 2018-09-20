<?php
/**
 * @package    Radical MultiField
 *
 * @author     Aleksey A. Morozov (AlekVolsk) <https://github.com/AlekVolsk>
 * @copyright  Copyright (C) 2018 Aleksey A. Morozov (AlekVolsk). All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://alekvolsk.pw
 *
 */

defined('_JEXEC') or die;

if (!$field->value)
{
	return;
}

$values = json_decode($field->value, JSON_OBJECT_AS_ARRAY);

$listtype = $this->getListTypeFromField($field);

// Add styles
$style = <<<HTML
/* Font Family
================================================== */

/* Setup
================================================== */

.container-audio { position: relative;
    max-width: none;
    width: 100%;
    background: #4a5175;
    color: #fff;
     }
.column { width:inherit; }


/* Typography / Links
================================================== */

p { color:#fff; display:block; font-size:.9rem; font-weight:400; margin:0 0 2px; }

a,a:visited { color:#8cc3e6; outline:0; text-decoration:underline; }
a:hover,a:focus { color:#bbdef5; }
p a,p a:visited { line-height:inherit; }


/* Misc.
================================================== */

.add-bottom { margin-bottom:2rem !important; }
.left { float:left; }
.right { float:right; }
.center { text-align:center; }
.hidden { display:none; }

.no-support {
margin:2rem auto;
text-align:center;
width:90%;
}


/* Audio Player Styles
================================================== */

audio {
display:none;
}

#audiowrap,
#plwrap {
margin:0 auto;
}

#tracks {
font-size:0;
position:relative;
text-align:center;
}

#nowPlay {
display:flex;
font-size:0;
}

#nowPlay span {
display:inline-block;
font-size:1.05rem;
vertical-align:top;
}

#nowPlay span#npAction {
padding:21px;
width:30%;
}

#nowPlay span#npTitle {
padding:21px;
text-align:right;
width:70%;
}

#plList li {
cursor:pointer;
display:block;
margin:0;
padding:21px 0;
}

#plList li:hover {
background-color:rgba(0,0,0,.1);
}

.plItem {
position:relative;
}

.plTitle {
left:50px;
overflow:hidden;
position:absolute;
right:65px;
text-overflow:ellipsis;
top:0;
white-space:nowrap;
}

.plNum {
padding-left:21px;
width:25px;
}

.plLength {
padding-left:21px;
position:absolute;
right:21px;
top:0;
}

.plSel,
.plSel:hover {
background-color:rgba(0,0,0,.1);
color:#fff;
cursor:default !important;
}

#tracks a {
border-radius:3px;
color:#fff;
cursor:pointer;
display:inline-block;
font-size:2rem;
height:35px;
line-height:.175;
margin:0 5px 30px;
padding:10px;
text-decoration:none;
transition:background .3s ease;
}

#tracks a:last-child {
margin-left:0;
}

#tracks a:hover,
#tracks a:active {
background-color:rgba(0,0,0,.1);
color:#fff;
}

#tracks a::-moz-focus-inner {
border:0;
padding:0;
}


/* Plyr Overrides
================================================== */

.plyr--audio .plyr__controls {
background-color:transparent;
border:none;
color:#fff;
padding:20px 20px 20px 13px;
width:100%;
}

.plyr--audio .plyr__controls button.tab-focus:focus,
.plyr--audio .plyr__controls button:hover,
.plyr__play-large {
background:rgba(0,0,0,.1);
}

.plyr__progress--played,
.plyr__volume--display {
color:rgba(0,0,0,.1);
}

.plyr--audio .plyr__progress--buffer,
.plyr--audio .plyr__volume--display {
background:rgba(0,0,0,.1);
}

.plyr--audio .plyr__progress--buffer {
color:rgba(0,0,0,.1);
}


/* Media Queries
================================================== */

@media only screen and (max-width:600px) {
    #nowPlay span#npAction { display:none; }
    #nowPlay span#npTitle { display:block; text-align:center; width:100%; }
}

HTML;
$document = JFactory::getDocument();
$document->addStyleDeclaration($style);
$document->addStyleSheet('//cdn.plyr.io/3.3.21/plyr.css');
$document->addScript('//cdnjs.cloudflare.com/ajax/libs/html5media/1.1.8/html5media.min.js');
$document->addScript('//cdnjs.cloudflare.com/ajax/libs/plyr/3.3.21/plyr.min.js');
?>

<div class="container-audio">
	<div class="column add-bottom">
		<div id="mainwrap">
			<div id="nowPlay">
				<span id="npAction">Пауза...</span><span id="npTitle"></span>
			</div>
			<div id="audiowrap">
				<div id="audio0">
					<audio id="audio1" preload controls>Ваш браузер не поддерживает HTML5 аудио! 😢</audio>
				</div>
				<div id="tracks">
					<a id="btnPrev">&larr;</a><a id="btnNext">&rarr;</a>
				</div>
			</div>
			<div id="plwrap">
				<ul id="plList"></ul>
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">

    jQuery(function ($) {
        'use strict'
        var supportsAudio = !!document.createElement('audio').canPlayType;
        if (supportsAudio) {
            // initialize plyr
            var player = new Plyr('#audio1', {
                controls: [
                    'restart',
                    'play',
                    'progress',
                    'current-time',
                    'duration',
                    'mute',
                    'volume'
                ]
            });
            var tracks = [];

            <?php $i = 1; ?>
	        <?php foreach ($values as $key => $row): ?>
                tracks.push({
                    "track": <?= $i ?>,
                    "name": "<?= $row['name'] ?>",
                    "file": "<?= $row['file'] ?>"
                });
                <?php $i++; ?>
	        <?php endforeach; ?>
            // initialize playlist and controls
            var index = 0,
                playing = false,
                mediaPath = '/',
                extension = '',
                buildPlaylist = $(tracks).each(function(key, value) {
                    var trackNumber = value.track,
                        trackName = value.name;
                    if (trackNumber.toString().length === 1) {
                        trackNumber = '0' + trackNumber;
                    }
                    $('#plList').append('<li> \
                    <div class="plItem"> \
                        <span class="plNum">' + trackNumber + '.</span> \
                        <span class="plTitle">' + trackName + '</span> \
                    </div> \
                </li>');
                }),
                trackCount = tracks.length,
                npAction = $('#npAction'),
                npTitle = $('#npTitle'),
                audio = $('#audio1').on('play', function () {
                    playing = true;
                    npAction.text('Сейчас играет...');
                }).on('pause', function () {
                    playing = false;
                    npAction.text('Пауза...');
                }).on('ended', function () {
                    npAction.text('Пауза...');
                    if ((index + 1) < trackCount) {
                        index++;
                        loadTrack(index);
                        audio.play();
                    } else {
                        audio.pause();
                        index = 0;
                        loadTrack(index);
                    }
                }).get(0),
                btnPrev = $('#btnPrev').on('click', function () {
                    if ((index - 1) > -1) {
                        index--;
                        loadTrack(index);
                        if (playing) {
                            audio.play();
                        }
                    } else {
                        audio.pause();
                        index = 0;
                        loadTrack(index);
                    }
                }),
                btnNext = $('#btnNext').on('click', function () {
                    if ((index + 1) < trackCount) {
                        index++;
                        loadTrack(index);
                        if (playing) {
                            audio.play();
                        }
                    } else {
                        audio.pause();
                        index = 0;
                        loadTrack(index);
                    }
                }),
                li = $('#plList li').on('click', function () {
                    var id = parseInt($(this).index());
                    if (id !== index) {
                        playTrack(id);
                    }
                }),
                loadTrack = function (id) {
                    $('.plSel').removeClass('plSel');
                    $('#plList li:eq(' + id + ')').addClass('plSel');
                    npTitle.text(tracks[id].name);
                    index = id;
                    audio.src = mediaPath + tracks[id].file + extension;
                },
                playTrack = function (id) {
                    loadTrack(id);
                    audio.play();
                };
            //extension = audio.canPlayType('audio/mpeg') ? '.mp3' : audio.canPlayType('audio/ogg') ? '.ogg' : '';
            extension = '';
            loadTrack(index);
        } else {
            // boo hoo
            $('.column').addClass('hidden');
            var noSupport = $('#audio1').text();
            $('.container').append('<p class="no-support">' + noSupport + '</p>');
        }
    });

</script>
