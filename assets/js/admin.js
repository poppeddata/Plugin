( function () {
	'use strict';
	var apiFetch = window.wp && window.wp.apiFetch;
	var i18n = window.wp && window.wp.i18n;
	var __ = i18n ? i18n.__ : function ( text ) { return text; };
	var _n = i18n ? i18n._n : function ( single, plural, number ) { return number === 1 ? single : plural; };
	var sprintf = i18n ? i18n.sprintf : function ( format, value ) { return format.replace( '%d', value ); };

	function sortable( list ) {
		if ( ! list || list.dataset.poppedReady || list.closest( '[data-reorder="false"]' ) ) { return; }
		list.dataset.poppedReady = 'true';
		var dragged;
		list.addEventListener( 'dragstart', function ( event ) { dragged = event.target.closest( 'li' ); if ( dragged ) { dragged.classList.add( 'is-dragging' ); } } );
		list.addEventListener( 'dragend', function () { if ( dragged ) { dragged.classList.remove( 'is-dragging' ); } dragged = null; } );
		list.addEventListener( 'dragover', function ( event ) { event.preventDefault(); var target = event.target.closest( 'li' ); if ( dragged && target && target !== dragged ) { var rect = target.getBoundingClientRect(); list.insertBefore( dragged, event.clientY < rect.top + rect.height / 2 ? target : target.nextSibling ); } } );
	}
	document.querySelectorAll( '[data-popped-sortable],[data-popped-selected-posts]' ).forEach( sortable );

	function plainTitle( post ) {
		var title = post.title && post.title.rendered ? String( post.title.rendered ) : '';
		title = title.replace( /<[^>]*>/g, '' );
		if ( window.wp && window.wp.htmlEntities && typeof window.wp.htmlEntities.decodeEntities === 'function' ) {
			title = window.wp.htmlEntities.decodeEntities( title );
		}
		return title.trim() || __( '(Untitled story)', 'popped' );
	}

	function closeResults( input, results ) { results.hidden = true; results.replaceChildren(); input.setAttribute( 'aria-expanded', 'false' ); }
	function showResults( input, results, records, label, choose ) {
		results.replaceChildren();
		records.forEach( function ( record ) {
			var button = document.createElement( 'button' );
			button.type = 'button'; button.className = 'popped-admin-search-option'; button.setAttribute( 'role', 'option' ); button.textContent = label( record );
			button.addEventListener( 'click', function () { choose( record ); closeResults( input, results ); input.focus(); } );
			button.addEventListener( 'keydown', function ( event ) {
				if ( event.key === 'Escape' ) { event.preventDefault(); closeResults( input, results ); input.focus(); }
				if ( event.key === 'ArrowDown' || event.key === 'ArrowUp' ) { event.preventDefault(); var buttons = Array.prototype.slice.call( results.querySelectorAll( 'button' ) ); var index = buttons.indexOf( button ) + ( event.key === 'ArrowDown' ? 1 : -1 ); ( buttons[ Math.max( 0, Math.min( buttons.length - 1, index ) ) ] || input ).focus(); }
			} );
			results.appendChild( button );
		} );
		results.hidden = false; input.setAttribute( 'aria-expanded', 'true' );
	}

	function restSearch( input, path, label, choose ) {
		if ( ! apiFetch || input.dataset.poppedReady ) { return; }
		input.dataset.poppedReady = 'true';
		var scope = input.closest( '.popped-admin-search' ) || input.closest( '.popped-collection-manual' ) || input.closest( '[data-popped-post-picker]' );
		var results = scope.querySelector( '[data-popped-search-results]' );
		var status = scope.querySelector( '[data-popped-search-status]' );
		var timer = 0; var request = 0;
		input.addEventListener( 'input', function () {
			window.clearTimeout( timer ); request += 1; var ownRequest = request; var search = input.value.trim();
			if ( input.hasAttribute( 'data-popped-term-picker' ) ) { scope.querySelector( '[data-popped-term-value]' ).value = ''; }
			if ( search.length < 2 ) { status.textContent = search ? __( 'Type at least two characters.', 'popped' ) : ''; closeResults( input, results ); return; }
			status.textContent = __( 'Searching…', 'popped' ); input.setAttribute( 'aria-busy', 'true' );
			timer = window.setTimeout( function () {
				apiFetch( { path: path( search ) } ).then( function ( records ) {
					if ( ownRequest !== request ) { return; }
					input.removeAttribute( 'aria-busy' ); status.textContent = records.length ? sprintf( _n( '%d result', '%d results', records.length, 'popped' ), records.length ) : __( 'No matches.', 'popped' );
					showResults( input, results, records, label, choose );
				} ).catch( function () { if ( ownRequest !== request ) { return; } input.removeAttribute( 'aria-busy' ); status.textContent = __( 'Search is temporarily unavailable.', 'popped' ); closeResults( input, results ); } );
			}, 250 );
		} );
		input.addEventListener( 'keydown', function ( event ) { if ( event.key === 'ArrowDown' && ! results.hidden ) { var first = results.querySelector( 'button' ); if ( first ) { event.preventDefault(); first.focus(); } } if ( event.key === 'Escape' ) { closeResults( input, results ); } } );
	}

	function initTermPicker( input ) {
		var taxonomy = input.getAttribute( 'data-popped-term-picker' );
		var endpoint = taxonomy === 'category' ? 'categories' : 'tags';
		restSearch( input, function ( search ) { return '/wp/v2/' + endpoint + '?search=' + encodeURIComponent( search ) + '&per_page=20&page=1&orderby=name&order=asc&_fields=id,name,slug'; }, function ( term ) { return term.name; }, function ( term ) { input.value = term.name; input.closest( '.popped-admin-search' ).querySelector( '[data-popped-term-value]' ).value = term.slug; } );
	}

	function selectedPostItem( post, inputName, reorder ) {
		var item = document.createElement( 'li' ); if ( reorder ) { item.draggable = true; } item.dataset.postId = post.id;
		item.innerHTML = ( reorder ? '<span class="popped-drag" aria-hidden="true">⋮⋮</span>' : '' ) + '<span data-popped-post-title></span><input type="hidden"><span class="popped-order-actions">' + ( reorder ? '<button type="button" class="button" data-popped-move-post="up">↑</button><button type="button" class="button" data-popped-move-post="down">↓</button>' : '' ) + '<button type="button" class="button-link-delete" data-popped-remove-post></button></span>';
		if ( reorder ) {
			item.querySelector( '[data-popped-move-post="up"]' ).setAttribute( 'aria-label', __( 'Move story up', 'popped' ) );
			item.querySelector( '[data-popped-move-post="down"]' ).setAttribute( 'aria-label', __( 'Move story down', 'popped' ) );
		}
		item.querySelector( '[data-popped-remove-post]' ).textContent = __( 'Remove', 'popped' );
		item.querySelector( '[data-popped-post-title]' ).textContent = plainTitle( post ); item.querySelector( 'input' ).name = inputName; item.querySelector( 'input' ).value = post.id; return item;
	}

	function initPostSearch( input ) {
		var scope = input.closest( '.popped-collection-manual' ) || input.closest( '[data-popped-post-picker]' ); var selected = scope.querySelector( '[data-popped-selected-posts]' );
		var inputName = scope.hasAttribute( 'data-input-name' ) ? scope.getAttribute( 'data-input-name' ) : selected.getAttribute( 'data-input-base' ) + '[]';
		var max = parseInt( scope.getAttribute( 'data-max' ) || '0', 10 ); var reorder = scope.getAttribute( 'data-reorder' ) !== 'false';
		restSearch( input, function ( search ) { return '/wp/v2/posts?search=' + encodeURIComponent( search ) + '&per_page=20&page=1&status=publish&orderby=relevance&_fields=id,title'; }, plainTitle, function ( post ) { if ( ! selected.querySelector( '[data-post-id="' + post.id + '"]' ) ) { if ( max === 1 ) { selected.replaceChildren(); } selected.appendChild( selectedPostItem( post, inputName, reorder ) ); sortable( selected ); } input.value = ''; } );
	}

	function updateCollection( card ) {
		var source = card.querySelector( '[data-popped-collection-source]' ).value;
		card.querySelectorAll( '[data-popped-collection-field]' ).forEach( function ( field ) { var type = field.getAttribute( 'data-popped-collection-field' ); field.hidden = type === 'manual' ? source !== 'manual' : type !== source && source !== 'categories-tags'; } );
		card.querySelectorAll( '[data-popped-term-picker]' ).forEach( initTermPicker ); card.querySelectorAll( '[data-popped-post-search]' ).forEach( initPostSearch ); card.querySelectorAll( '[data-popped-selected-posts]' ).forEach( sortable );
	}

	document.querySelectorAll( '[data-popped-collection]' ).forEach( function ( card ) { updateCollection( card ); card.querySelector( '[data-popped-collection-source]' ).addEventListener( 'change', function () { updateCollection( card ); } ); } );
	function initPostPickers() { document.querySelectorAll( '[data-popped-post-picker] [data-popped-post-search]' ).forEach( initPostSearch ); }
	initPostPickers();
	if ( window.MutationObserver && document.body ) { new MutationObserver( initPostPickers ).observe( document.body, { childList: true, subtree: true } ); }
	document.addEventListener( 'input', function ( event ) { if ( event.target.matches( '[data-popped-collection-name]' ) ) { event.target.closest( '[data-popped-collection]' ).querySelector( '[data-popped-collection-title]' ).textContent = event.target.value || __( 'Untitled collection', 'popped' ); } } );
	document.addEventListener( 'click', function ( event ) {
		var add = event.target.closest( '[data-popped-add-collection]' );
		if ( add ) { var template = document.getElementById( 'popped-collection-template' ); var holder = document.querySelector( '[data-popped-collections]' ); var wrapper = document.createElement( 'div' ); wrapper.innerHTML = template.innerHTML.replace( /__INDEX__/g, String( Date.now() ) ); var card = wrapper.firstElementChild; holder.appendChild( card ); updateCollection( card ); card.querySelector( '[data-popped-collection-source]' ).addEventListener( 'change', function () { updateCollection( card ); } ); card.querySelector( 'input[type=text]' ).focus(); }
		var removeCollection = event.target.closest( '[data-popped-remove-collection]' ); if ( removeCollection && window.confirm( __( 'Remove this collection? Stories will not be deleted.', 'popped' ) ) ) { removeCollection.closest( '[data-popped-collection]' ).remove(); }
		var removePost = event.target.closest( '[data-popped-remove-post]' ); if ( removePost ) { removePost.closest( 'li' ).remove(); }
		var movePost = event.target.closest( '[data-popped-move-post]' ); if ( movePost ) { var item = movePost.closest( 'li' ); var list = item.parentNode; if ( movePost.dataset.poppedMovePost === 'up' && item.previousElementSibling ) { list.insertBefore( item, item.previousElementSibling ); } if ( movePost.dataset.poppedMovePost === 'down' && item.nextElementSibling ) { list.insertBefore( item.nextElementSibling, item ); } movePost.focus(); }

		var choose = event.target.closest( '[data-popped-media]' ); var remove = event.target.closest( '[data-popped-media-remove]' );
		if ( remove ) { event.preventDefault(); var removeField = remove.closest( '.popped-media-field' ); removeField.querySelector( '[data-popped-media-value]' ).value = '0'; removeField.querySelector( '.popped-media-name' ).textContent = __( 'None selected', 'popped' ); }
		if ( ! choose || ! window.wp || ! wp.media ) { return; }
		event.preventDefault(); var field = choose.closest( '.popped-media-field' ); var kind = choose.getAttribute( 'data-popped-media' );
		var frame = wp.media( { title: kind === 'font' ? __( 'Choose or upload a font', 'popped' ) : __( 'Choose an image', 'popped' ), multiple: false, library: kind === 'image' ? { type: 'image' } : {} } );
		frame.on( 'select', function () { var attachment = frame.state().get( 'selection' ).first().toJSON(); field.querySelector( '[data-popped-media-value]' ).value = attachment.id; field.querySelector( '.popped-media-name' ).textContent = attachment.title || attachment.filename; } ); frame.open();
	} );
} )();
