addEventListener('load', () => UTUE_PLAYER.init({
  imageDir: '../assets/images',
  autoplay: 0
}));

const UTUE_PLAYER = function () {
  'use strict';

  function init({
    imageDir,
    seekSpeed = 5,
    seekSpeedFast = 10,
    volumeChangeSpeed = 10,
    autoplay = null
  }) {

    function generatePlayerHTML(videoSrc) {
      return `
      <video play preload="metadata" tabindex="-1" src="${encodeURI(videoSrc)}"></video>
      <largeicon buffering></largeicon>
      <bar tabindex="-1">
        <input seekbar type="range" value="0" step="0.01" tabindex="-1" title="Seek">
        <controls>
          <controls-left>
            <button control play title="Play"></button>
            <button control volume title="Mute"></button>
            <volume-panel>
              <input volume type="range" title="Volume">
            </volume-panel>
            <progress-label></progress-label>
          </controls-left>
          <controls-right>
            <button control theater title="Expand"></button>
            <button control fullscreen title="Full screen"></button>
          </controls-right>
        </controls>
      </bar>
    `;
    }

    function generatePlayerCSS() {
      return `
      <style>
        * {
          padding: 0;
          border: 0;
          margin: 0;
        }

        :host, *:focus {
          outline: none;
        }

        :host {
          background-color: #000;
          flex-direction: column;
          position: relative;
          width: 640px;
          height: 390px;
          font-family: sans-serif;
          overflow: hidden;
          user-select: none;

          --ease: cubic-bezier(0.33, 0, 0.67, 0.33);
        }

        :host(:not([data-mousemove])) {
          cursor: none;
        }

        largeicon[buffering] {
          background: url('${imageDir}/buffering.svg');
          -webkit-filter: drop-shadow(0 0 4px #000);
          -moz-filter: drop-shadow(0 0 4px #000);
          filter: drop-shadow(0 0 4px #000);
          position: absolute;
          top: 50%;
          left: 50%;
          width: 32px;
          height: 32px;
          transform: translate(-50%, -50%);
          transition: opacity 0.2s;
          opacity: 0;
        }

        :host([data-buffering]) largeicon[buffering] {
          opacity: 1;
        }

        ::-webkit-slider-thumb, ::-moz-range-thumb {
          cursor: pointer;
        }

        video {
          display: block;
          width: 100%;
          height: calc(100% - 29px);
        }

        bar, controls, controls-left, controls-right {
          display: flex;
        }

        bar {
          background-color: #ebebeb;
          background-image: url('${imageDir}/bar-bg.svg');
          background-position: center bottom -4px;
          background-repeat-y: no-repeat;
          flex-direction: column;
          position: absolute;
          bottom: 0;
          width: 100%;
        }

        input[seekbar] {
          -webkit-appearance: none;
          margin-bottom: 1px;
          width: 100%;
          height: 3px;
          -webkit-filter: contrast(200%);
          -moz-filter: contrast(200%);
          filter: contrast(200%);
          transition: margin 0.4s var(--ease), height 0.4s var(--ease), filter 0.4s var(--ease);
        }

        :host([data-mousemove]) input[seekbar] {
          margin-bottom: 4px;
          height: 12px;
          -webkit-filter: none;
          -moz-filter: none;
          filter: none;
          transition: margin 0.1s, height 0.1s, filter 0.1s;
        }

        bar::after {
          content: '';
          background:
        ${(() => { // preload button images
          const buttonImages = [
            'control-button',
            'play',
            'pause',
            'theater',
            'theater-exit',
            'fullscreen',
            'fullscreen-exit',
            'volume0',
            'volume1',
            'volume2',
            'volume3'
          ];

          let str = '';

          buttonImages.forEach(cur => {
            str += `
              url('${imageDir}/${cur}.svg'),
              url('${imageDir}/${cur}-hover.svg'),
            `;
          });

          str += `
              url('${imageDir}/seekbar-thumb.svg'),
              url('${imageDir}/seekbar-thumb-active.svg'),
            `;

          return replaceWhitespace(str).slice(0, -2);
        })()};
          width: 1000px;
          height: 1000px;
          position: absolute;
          opacity: 0;
          z-index: -9999;
        }

        input[seekbar]::-webkit-slider-thumb { /* can't select both sliders at once using a comma (yeah i have no clue why either) */
          -webkit-appearance: none;
          appearance: none;
          background-image: url('${imageDir}/seekbar-thumb.svg');
          background-position: center bottom;
          background-size: contain;
          background-repeat: no-repeat;
          -webkit-filter: drop-shadow(0 0 1px #808080);
          filter: drop-shadow(0 0 1px #808080);
          width: 16px;
          height: 0;
          transition: height 0.4s var(--ease);
        }

        input[seekbar]::-moz-range-thumb {
          -moz-appearance: none;
          appearance: none;
          background-color: transparent;
          background-image: url('${imageDir}/seekbar-thumb.svg');
          background-position: center bottom;
          background-size: contain;
          background-repeat: no-repeat;
          -moz-filter: drop-shadow(0 0 1px #808080);
          filter: drop-shadow(0 0 1px #808080);
          border: none;
          border-radius: 0;
          width: 16px;
          height: 0;
          transition: height 0.4s var(--ease);
        }

        input[seekbar]::-webkit-slider-thumb:active {
          background-image: url('${imageDir}/seekbar-thumb-active.svg');
        }

        input[seekbar]::-moz-range-thumb:active {
          background-image: url('${imageDir}/seekbar-thumb-active.svg');
        }

        :host([data-mousemove]) input[seekbar]::-webkit-slider-thumb {
          height: 16px;
          transition: height 0.1s;
        }

        :host([data-mousemove]) input[seekbar]::-moz-range-thumb {
          height: 16px;
          transition: height 0.1s;
        }

        controls {
          border: 1px solid #bfbfbf;
          border-top: none;
          box-sizing: border-box;
          align-items: end;
          justify-content: space-between;
          filter: opacity(50%);
          transition: filter 0.4s var(--ease);
        }

        :host([data-mousemove]) controls {
          filter: none;
          transition: filter 0.1s;
        }

        controls-left, controls-right {
          align-items: center;
        }

        controls-left > :not(progress-label) {
          border-right: 1px solid #bfbfbf;
        }

        controls-right > button[control] {
          border-left: 1px solid #bfbfbf;
        }

        button:focus {
          outline: none;
        }

        button[control] {
          background-image: url('${imageDir}/control-button.svg');
          background-position: bottom right;
          background-size: cover;
          position: relative;
          width: 30px;
          height: 25px;
          z-index: 1;
        }

        button[control]:hover {
          background-image: url('${imageDir}/control-button-hover.svg');
        }
        
        button[control]:active {
          background-image: url('${imageDir}/control-button-active.svg');
        }

        button[control]::after {
          content: '';
          background-position: center;
          background-repeat: no-repeat;
          position: absolute;
          left: 0;
          top: 0;
          width: 30px;
          height: 25px;
        }

        button[control]:active::after {
          box-shadow: inset 0 0 2px 2px #d9d9d9;
        }

        controls-left button[control]:last-of-type {
          box-shadow: 2px 0 2px #bfbfbf;
        }
        
        controls-right > button[control]:first-of-type {
          box-shadow: -1px 0 1px #bfbfbf;
        }

        ${(() => { // generate control button css
          const controlButton = {
            play: [
              {
                attr: '[data-playing]',
                image: 'pause'
              },
              {
                attr: ':not([data-playing])',
                image: 'play'
              },
            ],
            theater: [
              {
                attr: '[data-theater]',
                image: 'theater-exit'
              },
              {
                attr: ':not([data-theater])',
                image: 'theater'
              }
            ],
            fullscreen: [
              {
                attr: '[data-fullscreen]',
                image: 'fullscreen-exit'
              },
              {
                attr: ':not([data-fullscreen])',
                image: 'fullscreen'
              }
            ],
            volume: [
              {
                attr: '[data-volume-sector="0"]',
                image: 'volume0'
              },
              {
                attr: '[data-volume-sector="1"]',
                image: 'volume1'
              },
              {
                attr: '[data-volume-sector="2"]',
                image: 'volume2'
              },
              {
                attr: '[data-volume-sector="3"]',
                image: 'volume3'
              },
              {
                attr: '[data-muted]',
                image: 'volume0'
              }
            ]
          };

          let str = '';

          for (const buttonName in controlButton) { // lol
            const button = controlButton[buttonName];

            button.forEach(cur => {
              str += `
                :host(${cur.attr}) button[control][${buttonName}]::after {
                  background-image: url('${imageDir}/${cur.image}.svg');
                }

                :host(${cur.attr}) button[control][${buttonName}]:hover::after {
                  background-image: url('${imageDir}/${cur.image}-hover.svg');
                }
              `;
            });
          }

          return str;
        })()}

        progress-label {
          margin: 0 1em;
          font-size: 11px;
          line-height: 1;
        }

        volume-panel {
          margin-left: -65px;
          box-sizing: content-box;
          box-shadow: 2px 0 2px #bbbbbbbb;
          display: flex;
          align-items: center;
          justify-content: center;
          width: 64px;
          height: 25px;
          text-align: center;
          overflow: hidden;
          opacity: 0;
          z-index: -1;
          transition: margin-left 0.5s var(--ease), opacity 0s linear 0.5s, z-index 0s linear 0.5s;
        }
    
        button[control][volume]:hover ~ volume-panel,
        volume-panel:hover {
          margin-left: 0;
          opacity: 1;
          z-index: 0;
          transition: margin-left 0.15s;
        }
    
        input[volume] {
          -webkit-appearance: none;
          width: 53px;
          height: 4px;
        }
    
        input[volume]::-webkit-slider-thumb {
          -webkit-appearance: none;
          appearance: none;
          background-image: url('${imageDir}/slider-thumb.svg');
          -webkit-filter: drop-shadow(0 0 1px #bfbfbf);
          filter: drop-shadow(0 0 1px #bfbfbf);
          width: 6px;
          height: 17px;
        }
    
        input[volume]::-moz-range-thumb {
          -moz-appearance: none;
          appearance: none;
          background-image: url('${imageDir}/slider-thumb.svg');
          border: none;
          border-radius: 0;
          -moz-filter: drop-shadow(0 0 1px #bfbfbf);
          filter: drop-shadow(0 0 1px #bfbfbf);
          width: 6px;
          height: 17px;
        }
      </style>
    `;
    }

    function replaceWhitespace(str) {
      return str.replace(/\s+/g, ' ');
    }

    function sliderPreventDefault(e) {
      if (e.code === 'ArrowLeft' || e.code === 'ArrowRight') {
        e.preventDefault();
      }
    }

    function togglePlayback(container, video, playButton) {
      if (video.paused) {
        video.play();
        playButton.title = 'Pause';
      } else {
        video.pause();
        playButton.title = 'Play';
      }
      expandSeekbar(container);
    }

    function toggleFullscreen(container) {
      if (document.fullscreenElement === container) {
        document.exitFullscreen();
      } else {
        container.requestFullscreen();
      }
    }

    function checkFullscreen(container, fullscreenButton) {
      if (document.fullscreenElement === container) {
        container.setAttribute('data-fullscreen', '');
        fullscreenButton.title = 'Exit full screen';
      } else {
        container.removeAttribute('data-fullscreen');
        fullscreenButton.title = 'Full screen';
      }
    }

    function toggleTheater(container, theaterButton) {
      if (container.hasAttribute('data-theater')) {
        container.removeAttribute('data-theater');
        theaterButton.title = 'Expand';
      } else {
        container.setAttribute('data-theater', '');
        theaterButton.title = 'Exit full screen';
        theaterButton.title = 'Shrink';
      }
    }

    function toggleMute(container, video, volumeButton, volumeSlider) {
      video.muted = !video.muted;
      localStorage.setItem('player-muted', video.muted);
      if (video.muted) {
        container.setAttribute('data-muted', '');
        volumeButton.title = 'Unmute';
        volumeSlider.value = 0;
      } else {
        container.removeAttribute('data-muted');
        volumeButton.title = 'Mute';
        volumeSlider.value = video.volume * 100;
      }
      updateVolumeSector(container, video);
      updateVolumeTrack(volumeSlider);
    }

    function setVolume(container, video, volumeButton, volumeSlider) {
      const volume = volumeSlider.value / 100;
      video.volume = volume;
      volumeSlider.title = `Volume (${volumeSlider.value}%)`;
      if (video.muted) toggleMute(container, video, volumeButton, volumeSlider);
      updateVolumeSector(container, video);
      updateVolumeTrack(volumeSlider);
      localStorage.setItem('player-volume', volumeSlider.value);
    }

    function updateVolumeSector(container, video) {
      const volume = video.volume * 100;
      container.setAttribute('data-volume-sector',
        (volume == 0 || container.hasAttribute('data-muted')) ? 0 :
          (volume > 0 && volume <= 33) ? 1 :
            (volume > 33 && volume <= 66) ? 2 :
              3
      );
    }

    function decorateTrack(input, bg, entries) {
      const entriesStr = entries.reduce((prev, cur) => prev += `linear-gradient(to ${cur.direction}, transparent ${cur.start}%, ${cur.color} ${cur.start}%, ${cur.color} ${cur.end}%, transparent ${cur.end}%), `, '');
      input.style.background = entriesStr + bg;
    }

    function updateSeekbarTrack(seekbar, video) {
      const buffered = video.buffered;
      let bars = [
        {
          start: 60,
          end: 100,
          color: '#00000015',
          direction: 'bottom'
        },
        {
          start: 0,
          end: parseFloat(seekbar.value) - (seekbar.value - 50) / 100,
          color: '#b63f3f',
          direction: 'right'
        }
      ];

      for (let i = 0; i < buffered.length; i++) {
        bars.push({
          start: buffered.start(i) / video.duration * 100,
          end: buffered.end(i) / video.duration * 100,
          color: '#b63f3f44',
          direction: 'right'
        });
      }

      decorateTrack(seekbar, '#8c9195', bars);
    }

    function updateVolumeTrack(volumeSlider) {
      decorateTrack(
        volumeSlider, `url('${imageDir}/slider-track.svg')`,
        [{
          start: 0,
          end: volumeSlider.value,
          color: '#e60000',
          direction: 'right'
        }]
      );
    }

    function updateTime(video, seekbar, progressLabel) {
      const currentTimeUnderHour = (video.currentTime / 3600 < 1) * - 5;
      const durationUnderHour = (video.duration / 3600 < 1) * - 5;
      const currentTime = new Date(video.currentTime * 1000).toISOString().substring(11, 19).slice(currentTimeUnderHour).replace(/^0/, '');
      const duration = new Date(video.duration * 1000).toISOString().substring(11, 19).slice(durationUnderHour).replace(/^0/, '');

      seekbar.value = video.currentTime / video.duration * 100;
      updateSeekbarTrack(seekbar, video);

      progressLabel.innerText =
        currentTime +
        ' / ' +
        duration;
    }

    const expandSeekbar = (() => {
      let mouseInactive;

      return (container) => {
        clearTimeout(mouseInactive);
        container.setAttribute('data-mousemove', '');
        mouseInactive = setTimeout(() => container.removeAttribute('data-mousemove'), 2000);
      };

    })();

    // why isn't there a method for removing shadow DOMs
    Array.from(document.getElementsByClassName('utue-player')).forEach(container => {
      for (const cur in container.dataset) {
        if (cur === 'src') continue;
        delete container.dataset[cur];
      }
      container.outerHTML = container.outerHTML;
    });

    Array.from(document.getElementsByClassName('utue-player')).forEach((container, containerId) => {

      let root;

      // create shadow DOM root if supported

      try {
        root = container.attachShadow({ mode: 'open' });
      } catch (error) {
        container.style = 'background-color: #110000; display: flex; width: 640px; height: 390px; align-items: center; justify-content: center;';
        container.innerHTML = '<div style="font: 24px sans-serif; text-align: center; line-height: 1.5em; color: #fff;">Due to skill issues, your browser does not support the HTML5 player.<br><br>😈</div>';
      }

      // insert player markup

      root.innerHTML = replaceWhitespace(`
        ${generatePlayerCSS()}
        ${generatePlayerHTML(container.dataset.src)}
      `);

      // video elements

      const video = root.querySelector('video');
      const seekbar = root.querySelector('input[seekbar]');
      const playButton = root.querySelector('button[control][play]');
      const fullscreenButton = root.querySelector('button[control][fullscreen]');
      const theaterButton = root.querySelector('button[control][theater]');
      const volumeButton = root.querySelector('button[control][volume]');
      const volumeSlider = root.querySelector('input[volume]');
      const progressLabel = root.querySelector('progress-label');

      // timers and event listeners

      (() => {
        const checkInterval = 60;
        let iteration = 0;
        let lastPos = video.currentTime;

        function loop() {

          if (!(iteration % checkInterval)) {
            if (lastPos === video.currentTime && !video.paused) {
              container.setAttribute('data-buffering', '');
            } else {
              container.removeAttribute('data-buffering');
            }
            lastPos = video.currentTime;
          }

          iteration++;
          requestAnimationFrame(loop);
        }

        requestAnimationFrame(loop);
      })();

      video.addEventListener('pause', () => container.removeAttribute('data-playing'));

      container.addEventListener('mousemove', () => expandSeekbar(container));

      volumeButton.addEventListener('click', () => toggleMute(container, video, volumeButton, volumeSlider));
      volumeSlider.addEventListener('input', () => setVolume(container, video, volumeButton, volumeSlider));
      volumeSlider.addEventListener('keydown', e => sliderPreventDefault(e));

      fullscreenButton.addEventListener('click', () => toggleFullscreen(container, fullscreenButton));
      document.addEventListener('fullscreenchange', () => checkFullscreen(container, fullscreenButton));

      theaterButton.addEventListener('click', () => toggleTheater(container, theaterButton));

      video.addEventListener('canplay', function initialLoad() {

        updateTime(video, seekbar, progressLabel)

        // hotkeys

        container.addEventListener('keydown', e => {
          switch (e.code) {

            // playback
            case 'Space': case 'KeyK':
              togglePlayback(container, video, playButton);
              e.preventDefault();
              break;

            // seek
            case 'ArrowRight':
              video.currentTime += seekSpeed;
              break;
            case 'ArrowLeft':
              video.currentTime -= seekSpeed;
              break;
            case 'KeyL':
              video.currentTime += seekSpeedFast;
              break;
            case 'KeyJ':
              video.currentTime -= seekSpeedFast;
              break;

            // volume
            case 'ArrowUp':
              volumeSlider.value = parseInt(volumeSlider.value) + volumeChangeSpeed;
              setVolume(container, video, volumeButton, volumeSlider)
              break;
            case 'ArrowDown':
              volumeSlider.value -= volumeChangeSpeed;
              setVolume(container, video, volumeButton, volumeSlider)
              break;

            // mute
            case 'KeyM':
              toggleMute(container, video, volumeButton, volumeSlider);
              break;

            // fullscreen
            case 'KeyF':
              toggleFullscreen(container, root);
              break;

            // theater
            case 'KeyT':
              toggleTheater(container, theaterButton);
          }
        });

        // other event listeners

        video.addEventListener('timeupdate', () => {
          container.setAttribute('data-playing', '');
          updateTime(video, seekbar, progressLabel);
        });
        video.addEventListener('progress', () => updateSeekbarTrack(seekbar, video));
        video.addEventListener('contextmenu', e => e.preventDefault());
        video.addEventListener('dblclick', () => toggleFullscreen(container, fullscreenButton));

        seekbar.addEventListener('input', () => {
          video.currentTime = video.duration * seekbar.value / 100;
          updateSeekbarTrack(seekbar, video);
        });
        seekbar.addEventListener('keydown', e => sliderPreventDefault(e));

        Array.from(root.querySelectorAll('[play]')).forEach(cur => {
          cur.addEventListener('click', () => togglePlayback(container, video, playButton));
        });

        video.removeEventListener('canplay', initialLoad);

      });

      // retrieve previous volume and mute settings from localStorage

      volumeSlider.value = (localStorage.getItem('player-volume') != null) ? localStorage.getItem('player-volume') : 50;
      setVolume(container, video, volumeButton, volumeSlider);
      if (localStorage.getItem('player-muted') === 'true') toggleMute(container, video, volumeButton, volumeSlider);

      // autoplay player if option enabled and container id matches

      if (autoplay == containerId) {
        video.play();
        video.focus();
      }

      console.log('UTue player initialized!');

    });
  }

  // public

  return {

    init: init

  };

}();
