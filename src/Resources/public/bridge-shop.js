// page init
// function bindReady() {
//     initBridgeOpenClose();
// }

// open-close init
// function initBridgeOpenClose() {
//     document.querySelectorAll('.bridge-payment-open-close').forEach(function (elem) {
//         new BridgeOpenClose({
//             holder: elem,
//             activeClass: 'bridge-payment-active',
//             opener: '.bridge-payment-opener',
//             closer: '.close',
//             slider: '.bridge-payment-slide',
//             hiddenClass: 'bridge-payment-js-hidden',
//             animSpeed: 400
//         });
//     });
// }

/*
 * Open/Close module
 */
// class BridgeOpenClose {
//     constructor(options) {
//         this.options = Object.assign({
//             holder: null,
//             hideOnClickOutside: false,
//             activeClass: 'active',
//             opener: '.opener',
//             slider: '.slide',
//             hiddenClass: 'js-hidden',
//             animSpeed: 500
//         }, options);
//
//         if (this.options.holder) {
//             this.holder = this.options.holder;
//             this.init();
//         }
//     }
//
//     init() {
//         this.findElements();
//         this.attachEvents();
//         this.makeCallback('onInit', this);
//     }
//
//     findElements() {
//         this.opener = this.holder.querySelector(this.options.opener);
//         this.slider = this.holder.querySelector(this.options.slider);
//         this.closer = this.holder.querySelector(this.options.closer);
//         this.isAnim = false;
//     }
//
//     attachEvents() {
//         this.eventHandler = (e) => {
//             e.preventDefault();
//
//             if (this.isAnim) return;
//
//             this.isAnim = true;
//
//             if (!this.holder.classList.contains(this.options.activeClass)) {
//                 this.holder.classList.add(this.options.activeClass);
//                 this.slideDown(this.slider, this.options.animSpeed);
//             } else {
//                 this.holder.classList.remove(this.options.activeClass);
//                 this.slideUp(this.slider, this.options.animSpeed);
//             }
//
//             setTimeout(() => {
//                 this.isAnim = false;
//             }, this.options.animSpeed);
//         };
//
//         if (this.closer) {
//             this.closer.addEventListener('click', () => {
//                 if (this.isAnim) return;
//
//                 this.holder.classList.remove(this.options.activeClass);
//                 this.slideUp(this.slider, this.options.animSpeed);
//             });
//         }
//
//         this.slider.classList.add(this.options.hiddenClass);
//         this.opener.addEventListener('click', this.eventHandler);
//     }
//
//     slideUp(target, duration) {
//         target.style.transitionProperty = 'height, margin, padding';
//         target.style.transitionDuration = duration + 'ms';
//         target.style.height = target.offsetHeight + 'px';
//         target.offsetHeight;
//         target.style.overflow = 'hidden';
//         target.style.height = 0;
//         target.style.paddingTop = 0;
//         target.style.paddingBottom = 0;
//         target.style.marginTop = 0;
//         target.style.marginBottom = 0;
//
//         window.setTimeout(() => {
//             target.classList.add(this.options.hiddenClass);
//             target.style.removeProperty('height');
//             target.style.removeProperty('padding-top');
//             target.style.removeProperty('padding-bottom');
//             target.style.removeProperty('margin-top');
//             target.style.removeProperty('margin-bottom');
//             target.style.removeProperty('overflow');
//             target.style.removeProperty('transition-duration');
//             target.style.removeProperty('transition-property');
//         }, duration);
//     }
//
//     slideDown(target, duration) {
//         const height = target.offsetHeight;
//
//         target.classList.remove(this.options.hiddenClass);
//         target.style.overflow = 'hidden';
//         target.style.height = 0;
//         target.style.paddingTop = 0;
//         target.style.paddingBottom = 0;
//         target.style.marginTop = 0;
//         target.style.marginBottom = 0;
//         target.offsetHeight;
//         target.style.transitionProperty = 'height, margin, padding';
//         target.style.transitionDuration = duration + 'ms';
//         target.style.height = height + 'px';
//         target.style.removeProperty('padding-top');
//         target.style.removeProperty('padding-bottom');
//         target.style.removeProperty('margin-top');
//         target.style.removeProperty('margin-bottom');
//
//         window.setTimeout(() => {
//             target.style.removeProperty('height');
//             target.style.removeProperty('overflow');
//             target.style.removeProperty('transition-duration');
//             target.style.removeProperty('transition-property');
//         }, duration);
//     }
//
//     makeCallback(name) {
//         if (typeof this.options[name] === 'function') {
//             const args = Array.prototype.slice.call(arguments);
//
//             args.shift();
//             this.options[name].apply(this, args);
//         }
//     }
// }

// // in case the document is already rendered
// if (document.readyState !== 'loading') bindReady();
// // modern browsers
// else if (document.addEventListener) document.addEventListener('DOMContentLoaded', bindReady);
