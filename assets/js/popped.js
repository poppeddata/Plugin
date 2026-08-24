( function () {
	'use strict';
	var header = document.querySelector( '[data-popped-header]' );
	var overlay = document.getElementById( 'popped-navigation' );
	var trigger = document.querySelector( '.popped-menu-trigger' );
	var closer = overlay ? overlay.querySelector( '.popped-menu-close' ) : null;
	var previousFocus = null;
	var closeTimer = null;
	var headerFrame = null;
	var motionQuery = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	function prefersReducedMotion() {
		return motionQuery.matches || document.body.classList.contains( 'popped-motion-none' ) || document.body.classList.contains( 'popped-motion-reduced' );
	}

	function updateHeaderState() {
		if ( ! header ) { return; }
		header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
		headerFrame = null;
	}

	if ( header ) {
		updateHeaderState();
		window.addEventListener( 'scroll', function () {
			if ( headerFrame ) { return; }
			headerFrame = window.requestAnimationFrame( updateHeaderState );
		}, { passive: true } );
	}

	function focusable() {
		return overlay ? Array.prototype.slice.call( overlay.querySelectorAll( 'a[href],button:not([disabled]),input:not([disabled]),[tabindex]:not([tabindex="-1"])' ) ) : [];
	}

	function openMenu() {
		if ( ! overlay || ! trigger ) { return; }
		if ( closeTimer ) {
			window.clearTimeout( closeTimer );
			closeTimer = null;
		}
		previousFocus = document.activeElement;
		overlay.hidden = false;
		trigger.setAttribute( 'aria-expanded', 'true' );
		window.requestAnimationFrame( function () {
			overlay.setAttribute( 'aria-hidden', 'false' );
			document.body.classList.add( 'popped-menu-open' );
			var items = focusable();
			if ( items.length ) { items[ 0 ].focus(); }
		} );
	}

	function closeMenu() {
		if ( ! overlay || ! trigger ) { return; }
		if ( closeTimer ) { window.clearTimeout( closeTimer ); }
		overlay.setAttribute( 'aria-hidden', 'true' );
		document.body.classList.remove( 'popped-menu-open' );
		trigger.setAttribute( 'aria-expanded', 'false' );
		closeTimer = window.setTimeout( function () {
			overlay.hidden = true;
			closeTimer = null;
		}, prefersReducedMotion() ? 0 : 280 );
		if ( previousFocus && document.contains( previousFocus ) && typeof previousFocus.focus === 'function' ) {
			previousFocus.focus();
		}
	}

	if ( trigger && closer && overlay ) {
		trigger.addEventListener( 'click', openMenu );
		closer.addEventListener( 'click', closeMenu );
		overlay.addEventListener( 'click', function ( event ) {
			if ( event.target === overlay ) { closeMenu(); }
		} );
		document.addEventListener( 'keydown', function ( event ) {
			if ( overlay.hidden ) { return; }
			if ( event.key === 'Escape' ) { closeMenu(); return; }
			if ( event.key !== 'Tab' ) { return; }
			var items = focusable();
			if ( ! items.length ) { event.preventDefault(); closer.focus(); return; }
			var first = items[ 0 ];
			var last = items[ items.length - 1 ];
			if ( event.shiftKey && document.activeElement === first ) { event.preventDefault(); last.focus(); }
			else if ( ! event.shiftKey && document.activeElement === last ) { event.preventDefault(); first.focus(); }
		} );
	}

	var rails = document.querySelectorAll( '.popped-rail,.popped-timeline--horizontal' );
	rails.forEach( function ( rail ) {
		var isRtl = window.getComputedStyle( rail ).direction === 'rtl';
		var pointerDown = false;
		var didDrag = false;
		var suppressClickUntil = 0;
		var startX = 0;
		var startScroll = 0;
		var activePointer = null;

		function logicalDelta( amount ) {
			return isRtl ? -amount : amount;
		}

		function finishDrag() {
			if ( didDrag ) {
				suppressClickUntil = Date.now() + 250;
			}
			pointerDown = false;
			didDrag = false;
			activePointer = null;
			rail.classList.remove( 'is-dragging' );
		}

		rail.addEventListener( 'keydown', function ( event ) {
			if ( event.key !== 'ArrowRight' && event.key !== 'ArrowLeft' ) { return; }
			event.preventDefault();
			var amount = event.key === 'ArrowRight' ? 320 : -320;
			rail.scrollBy( { left: logicalDelta( amount ), behavior: prefersReducedMotion() ? 'auto' : 'smooth' } );
		} );

		if ( rail.classList.contains( 'popped-timeline--horizontal' ) ) {
			rail.addEventListener( 'wheel', function ( event ) {
				if ( Math.abs( event.deltaY ) <= Math.abs( event.deltaX ) || rail.scrollWidth <= rail.clientWidth ) { return; }
				var before = rail.scrollLeft;
				rail.scrollBy( { left: logicalDelta( event.deltaY ), behavior: 'auto' } );
				if ( rail.scrollLeft !== before ) { event.preventDefault(); }
			}, { passive: false } );
		}

		rail.addEventListener( 'pointerdown', function ( event ) {
			if ( event.pointerType !== 'mouse' || event.button !== 0 ) { return; }
			pointerDown = true;
			didDrag = false;
			activePointer = event.pointerId;
			startX = event.clientX;
			startScroll = rail.scrollLeft;
		} );

		rail.addEventListener( 'pointermove', function ( event ) {
			if ( ! pointerDown || event.pointerId !== activePointer ) { return; }
			var distance = event.clientX - startX;
			if ( ! didDrag && Math.abs( distance ) < 6 ) { return; }
			if ( ! didDrag ) {
				didDrag = true;
				rail.classList.add( 'is-dragging' );
				if ( rail.setPointerCapture ) { rail.setPointerCapture( event.pointerId ); }
			}
			rail.scrollLeft = startScroll - logicalDelta( distance );
		} );

		rail.addEventListener( 'pointerup', finishDrag );
		rail.addEventListener( 'pointercancel', finishDrag );
		rail.addEventListener( 'lostpointercapture', finishDrag );
		rail.addEventListener( 'click', function ( event ) {
			if ( Date.now() > suppressClickUntil ) { return; }
			event.preventDefault();
			event.stopPropagation();
			suppressClickUntil = 0;
		}, true );
	} );

	document.querySelectorAll( '[data-popped-rail-prev],[data-popped-rail-next]' ).forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			var wrapper = button.closest( '.popped-wrap' ) || button.parentNode.parentNode;
			var rail = wrapper.querySelector( '.popped-timeline--horizontal,.popped-rail' );
			if ( ! rail ) { return; }
			var isRtl = window.getComputedStyle( rail ).direction === 'rtl';
			var amount = button.hasAttribute( 'data-popped-rail-prev' ) ? -420 : 420;
			rail.scrollBy( { left: isRtl ? -amount : amount, behavior: prefersReducedMotion() ? 'auto' : 'smooth' } );
		} );
	} );

	function tickerCanAnimate( ticker ) {
		return ! ticker.classList.contains( 'popped-ticker--static' ) && ! prefersReducedMotion();
	}

	function removeTickerClones( track ) {
		track.querySelectorAll( '[data-popped-ticker-clone]' ).forEach( function ( clone ) {
			clone.remove();
		} );
	}

	function makeTickerClone( source ) {
		var clone = source.cloneNode( true );
		clone.removeAttribute( 'data-popped-ticker-group' );
		clone.setAttribute( 'data-popped-ticker-clone', 'true' );
		clone.setAttribute( 'aria-hidden', 'true' );
		clone.setAttribute( 'inert', '' );
		clone.querySelectorAll( 'a[href],button,input,select,textarea,[tabindex]' ).forEach( function ( element ) {
			element.setAttribute( 'tabindex', '-1' );
		} );
		return clone;
	}

	function setTickerPaused( ticker, paused ) {
		var toggle = ticker.querySelector( '[data-popped-ticker-toggle]' );
		ticker.classList.toggle( 'popped-ticker--paused', paused );
		if ( ! toggle ) { return; }
		toggle.setAttribute( 'aria-pressed', paused ? 'true' : 'false' );
		var label = paused ? toggle.getAttribute( 'data-resume-label' ) : toggle.getAttribute( 'data-pause-label' );
		var text = toggle.querySelector( '.popped-ticker__toggle-text' );
		if ( text && label ) { text.textContent = label; }
	}

	function prepareTicker( ticker ) {
		var viewport = ticker.querySelector( '.popped-ticker__viewport' );
		var track = ticker.querySelector( '.popped-ticker__track' );
		var source = ticker.querySelector( '[data-popped-ticker-group="source"]' );
		var toggle = ticker.querySelector( '[data-popped-ticker-toggle]' );
		if ( ! viewport || ! track || ! source ) { return; }

		ticker.classList.remove( 'popped-ticker--ready' );
		removeTickerClones( track );
		track.style.removeProperty( '--popped-ticker-loop-width' );

		if ( ! tickerCanAnimate( ticker ) ) {
			setTickerPaused( ticker, false );
			if ( toggle ) { toggle.hidden = true; }
			return;
		}

		var groupWidth = source.getBoundingClientRect().width;
		var viewportWidth = viewport.getBoundingClientRect().width;
		if ( groupWidth <= 0 || viewportWidth <= 0 ) {
			if ( toggle ) { toggle.hidden = true; }
			return;
		}

		var requiredGroups = Math.max( 2, Math.ceil( ( viewportWidth + groupWidth ) / groupWidth ) );
		for ( var index = 1; index < requiredGroups; index += 1 ) {
			track.appendChild( makeTickerClone( source ) );
		}

		track.style.setProperty( '--popped-ticker-loop-width', groupWidth + 'px' );
		if ( toggle ) { toggle.hidden = false; }
		ticker.classList.add( 'popped-ticker--ready' );
	}

	function initTicker( ticker ) {
		if ( ticker.dataset.poppedTickerReady ) { return; }
		ticker.dataset.poppedTickerReady = 'true';

		var toggle = ticker.querySelector( '[data-popped-ticker-toggle]' );
		if ( toggle ) {
			toggle.addEventListener( 'click', function () {
				if ( ! tickerCanAnimate( ticker ) ) { return; }
				setTickerPaused( ticker, ! ticker.classList.contains( 'popped-ticker--paused' ) );
			} );
		}

		var frame = null;
		function schedule() {
			if ( frame ) { window.cancelAnimationFrame( frame ); }
			frame = window.requestAnimationFrame( function () {
				frame = null;
				prepareTicker( ticker );
			} );
		}

		prepareTicker( ticker );

		if ( window.ResizeObserver ) {
			var observer = new ResizeObserver( schedule );
			var viewport = ticker.querySelector( '.popped-ticker__viewport' );
			if ( viewport ) { observer.observe( viewport ); }
		} else {
			window.addEventListener( 'resize', schedule, { passive: true } );
		}

		if ( document.fonts && document.fonts.ready ) {
			document.fonts.ready.then( schedule );
		}

		if ( typeof motionQuery.addEventListener === 'function' ) {
			motionQuery.addEventListener( 'change', function () {
				schedule();
			} );
		} else if ( typeof motionQuery.addListener === 'function' ) {
			motionQuery.addListener( function () {
				schedule();
			} );
		}
	}

	document.querySelectorAll( '[data-popped-ticker]' ).forEach( initTicker );

	document.querySelectorAll( '.popped-view-switch input' ).forEach( function ( input ) { input.addEventListener( 'change', function () { var form = input.closest( 'form' ); if ( form && form.requestSubmit ) { form.requestSubmit(); } } ); } );
} )();
