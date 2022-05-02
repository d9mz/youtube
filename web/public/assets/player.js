addEventListener('load', () => UTUE_PLAYER.init({
  imageDir: '../assets/images',
  seekSpeed: 5,
  seekSpeedFast: 10
}));

const UTUE_PLAYER = function () {
  'use strict';

  function init({imageDir, seekSpeed, seekSpeedFast}) {

    const controlButtons = {
      play: {
        case: 'data-playing',
        imageDefault: 'play',
        imageCaseMatch: 'pause'
      },
      fullscreen: {
        case: 'data-fullscreen',
        imageDefault: 'fullscreen',
        imageCaseMatch: 'fullscreen-exit'
      },
      expand: {
        case: 'data-expanded',
        imageDefault: 'expand',
        imageCaseMatch: 'shrink'
      }
    };

    function generatePlayerHTML(videoSrc) {
      return `
      <video play tabindex="-1" src="${videoSrc}"></video>
      <bar tabindex="-1">
        <controls>
          <controls-left>
            <button control play title="Play"></button>
            <button control volume title="Mute"></button>
            <volume-panel>
              <input volume type="range">
            </volume-panel>
            <progress-label></progress-label>
          </controls-left>
          <controls-right>
            <button control expand title="Expand"></button>
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
          background: #000;
          flex-direction: column;
          position: relative;
          width: 640px;
          height: 390px;
          font-family: sans-serif;
          overflow: hidden;
          user-select: none;
        }

        video {
          flex: 1;
          min-height: 0;
        }

        :host, bar, controls, controls-left, controls-right {
          display: flex;
        }

        bar {
          background: #fff url('${imageDir}/controls-bg.svg');
          flex-direction: column-reverse;
          width: 100%;
          height: 30px;
        }

        bar::after {
          content: '';
          background:
        ${(function () {
          let str = `
            url('${imageDir}/control-button.svg'),
            url('${imageDir}/control-button-hover.svg'),
            url('${imageDir}/control-button-active.svg'),
          `;

          for (const buttonName in controlButtons) {
            const button = controlButtons[buttonName];
            str += `
              url('${imageDir}/${button.imageDefault}.svg'),
              url('${imageDir}/${button.imageCaseMatch}.svg'),
              url('${imageDir}/${button.imageDefault}-hover.svg'),
              url('${imageDir}/${button.imageCaseMatch}-hover.svg'),
            `.replace();
          }

          return replaceWhitespace(str).slice(0, -2);
        })()}
        }

        controls {
          border: 1px solid #bfbfbf;
          justify-content: space-between;
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
          box-shadow: inset 0 0 2px 2px #bbbbbb66;
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

        controls-left button[control][volume] {
          box-shadow: 2px 0 2px #bbbbbbbb;
        }
        
        controls-right > button[control]:first-of-type {
          box-shadow: -1px 0 1px #bbbbbb88;
        }

        ${(function () { // generate control button css
          let str = '';

          for (const buttonName in controlButtons) {
            const button = controlButtons[buttonName];

            str += `
              :host([${button.case}]) button[control][${buttonName}]::after {
                background-image: url('${imageDir}/${button.imageCaseMatch}.svg');
              }
              
              :host([${button.case}]) button[control][${buttonName}]:hover::after {
                background-image: url('${imageDir}/${button.imageCaseMatch}-hover.svg');
              }
              
              :host(:not([${button.case}])) button[control][${buttonName}]::after {
                background-image: url('${imageDir}/${button.imageDefault}.svg');
              }
              
              :host(:not([${button.case}])) button[control][${buttonName}]:hover::after {
                background-image: url('${imageDir}/${button.imageDefault}-hover.svg');
              }
            `;
          }

          return str;
        })()}

        progress-label {
          margin: 0 1em;
          font-size: 11px;
          line-height: 1;
        }

        button[control][volume]::after {
          background-image: url('${imageDir}/volume0.svg');
        }
    
        volume-panel {
          margin-left: -65px;
          box-sizing: content-box;
          box-shadow: 2px 0 2px #bbbbbbbb;
          width: 64px;
          height: 25px;
          text-align: center;
          overflow: hidden;
          opacity: 0;
          transition: margin-left 0.5s cubic-bezier(0.33, 0, 0.67, 0.33), opacity 0s linear 0.5s;
        }
    
        button[control][volume]:hover ~ volume-panel,
        volume-panel:hover {
          margin-left: 0;
          opacity: 1;
          transition: margin-left 0.15s;
        }
    
        input[volume] {
          -webkit-appearance: none;
          background: linear-gradient(to right, #e60000 0%, #e60000 50%, transparent 50%, transparent 100%), url('${imageDir}/slider-track.svg');
          width: 53px;
          height: 4px;
        }
    
        input[volume]::-webkit-slider-thumb {
          -webkit-appearance: none;
          appearance: none;
          background-image: url('${imageDir}/slider-thumb.svg');
          width: 6px;
          height: 17px;
        }
    
        input[volume]::-moz-range-thumb {
          -moz-appearance: none;
          appearance: none;
          background-image: url('${imageDir}/slider-thumb.svg');
          border: none;
          border-radius: 0;
          width: 6px;
          height: 17px;
        }
      </style>
    `;
    }

    function replaceWhitespace(str) {
      return str.replace(/\s+/g, ' ');
    }

    function togglePlayback(video, playButton) {
      if (video.paused) {
        video.play();
        playButton.title = 'Pause';
      } else {
        video.pause();
        playButton.title = 'Play';
      }
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

    function updateVolume(video, volumeSlider) {
      var volume = volumeSlider.value;
      video.volume = volume / 100;
      volumeSlider.style.background = `linear-gradient(to right, #e60000 0%, #e60000 ${volume}%, transparent ${volume}%, transparent 100%), url('${imageDir}/slider-track.svg')`;
      localStorage.setItem('player-volume', volume);
    }

    function updateTime(video, progressLabel) {
      const underHour = (video.duration / 3600 < 1) * - 5;
      const currentTime = new Date(video.currentTime * 1000).toISOString().substring(11, 19).slice(underHour).replace(/^0/, '');
      const duration = new Date(video.duration * 1000).toISOString().substring(11, 19).slice(underHour).replace(/^0/, '');

      progressLabel.innerText =
        currentTime +
        ' / ' +
        duration;
    }

    Array.from(document.getElementsByClassName('utue-player')).forEach(container => {

      let root;

      try {
        root = container.attachShadow({ mode: 'closed' });
      } catch (error) {
        container.style = 'background: #110000; display: flex; width: 640px; height: 390px; align-items: center; justify-content: center;';
        container.innerHTML = '<div style="font: 28px sans-serif; text-align: center; color: #fff;">Your browser does not support the HTML5 player.</div>'
      }

      root.innerHTML = replaceWhitespace(`
      ${generatePlayerCSS()}
      ${generatePlayerHTML(container.dataset.src)}
    `);

      const video = root.querySelector('video');
      const playButton = root.querySelector('button[control][play]');
      const fullscreenButton = root.querySelector('button[control][fullscreen]');
      const volumeButton = root.querySelector('button[control][volume]');
      const volumeSlider = root.querySelector('input[volume]');
      const progressLabel = root.querySelector('progress-label');

      container.addEventListener('keydown', e => {

        // keyboard toggles
        switch (e.code) {

          // playback
          case 'Space': case 'KeyK':
            togglePlayback(video, playButton);
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

          // mute
          case 'KeyM':
            video.muted = !video.muted;
            break;

          // fullscreen
          case 'KeyF':
            toggleFullscreen(container, root);
        }

      });

      video.addEventListener('play', () => container.setAttribute('data-playing', ''));
      video.addEventListener('pause', () => container.removeAttribute('data-playing'));
      video.addEventListener('timeupdate', () => updateTime(video, progressLabel));
      video.addEventListener('canplay', () => updateTime(video, progressLabel));
      video.addEventListener('contextmenu', e => e.preventDefault());
      video.addEventListener('dblclick', () => toggleFullscreen(container, root));

      Array.from(root.querySelectorAll('[play]')).forEach(cur => {
        cur.addEventListener('click', () => togglePlayback(video, playButton));
      });

      volumeSlider.addEventListener('input', () => updateVolume(video, volumeSlider));
      fullscreenButton.addEventListener('click', () => toggleFullscreen(container, root));
      document.addEventListener('fullscreenchange', () => checkFullscreen(container, fullscreenButton));

      volumeSlider.value = parseInt(localStorage.getItem('player-volume'));
      updateVolume(video, volumeSlider);

    });
  }

  return {
    init: init
  };

}();
