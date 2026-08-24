( function ( wp ) {
	'use strict';
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useEffect = wp.element.useEffect;
	var useState = wp.element.useState;
	var useSelect = wp.data.useSelect;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var LinkControl = wp.blockEditor.LinkControl || wp.blockEditor.__experimentalLinkControl || null;
	var URLInput = wp.blockEditor.URLInput || null;
	var useSettings = wp.blockEditor.useSettings || null;
	var BlockControls = wp.blockEditor.BlockControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var TextControl = wp.components.TextControl;
	var ColorPalette = wp.components.ColorPalette;
	var ToggleControl = wp.components.ToggleControl;
	var RangeControl = wp.components.RangeControl;
	var ComboboxControl = wp.components.ComboboxControl;
	var Button = wp.components.Button;
	var ToolbarGroup = wp.components.ToolbarGroup;
	var ToolbarButton = wp.components.ToolbarButton;
	var Notice = wp.components.Notice;
	var Spinner = wp.components.Spinner;
	var ServerSideRender = wp.serverSideRender;
	var __ = wp.i18n.__;
	var decodeEntities = wp.htmlEntities && wp.htmlEntities.decodeEntities ? wp.htmlEntities.decodeEntities : function ( value ) { return value; };
	var config = window.poppedBlocks || { definitions: {}, defaults: {}, insertionDefaults: {}, collections: [] };


	function blockEditorSettings() {
		try {
			var store = wp.data.select( 'core/block-editor' );
			return store && store.getSettings ? ( store.getSettings() || {} ) : {};
		} catch ( error ) {
			return {};
		}
	}

	function flattenPresets( value, property, output ) {
		output = output || [];
		if ( Array.isArray( value ) ) {
			value.forEach( function ( item ) {
				flattenPresets( item, property, output );
			} );
			return output;
		}
		if ( ! value || typeof value !== 'object' ) { return output; }
		if ( value[ property ] ) {
			output.push( value );
			return output;
		}
		Object.keys( value ).forEach( function ( key ) {
			flattenPresets( value[ key ], property, output );
		} );
		return output;
	}

	function uniqueByValue( items ) {
		var seen = {};
		return items.filter( function ( item ) {
			var key = String( item.value || item.color || item.slug || '' );
			if ( ! key || seen[ key ] ) { return false; }
			seen[ key ] = true;
			return true;
		} );
	}

	function fontOptions( value ) {
		return uniqueByValue( flattenPresets( value, 'fontFamily' ).map( function ( item ) {
			return {
				label: item.name || item.slug || item.fontFamily,
				value: item.fontFamily
			};
		} ) );
	}

	function colorOptions( value ) {
		return uniqueByValue( flattenPresets( value, 'color' ).map( function ( item ) {
			return {
				name: item.name || item.slug || item.color,
				slug: item.slug || item.color,
				color: item.color
			};
		} ) );
	}

	function ThemeFontControlView( props, families ) {
		var optionsList = [ { label: __( 'Inherit from theme', 'popped' ), value: '' } ].concat( families );
		if ( props.value && ! optionsList.some( function ( option ) { return option.value === props.value; } ) ) {
			optionsList.push( { label: props.value, value: props.value } );
		}
		return el( ComboboxControl, {
			label: props.label,
			help: families.length ? __( 'Uses font families exposed by the active theme and Global Styles.', 'popped' ) : __( 'No theme font families are registered; leave this inherited.', 'popped' ),
			value: props.value || '',
			options: optionsList,
			onChange: function ( value ) { props.onChange( value || undefined ); }
		} );
	}

	function ThemeFontControlModern( props ) {
		var resolved = wp.blockEditor.useSettings( 'typography.fontFamilies' );
		return ThemeFontControlView( props, fontOptions( resolved && resolved.length ? resolved[ 0 ] : [] ) );
	}

	function ThemeFontControlLegacy( props ) {
		return ThemeFontControlView( props, fontOptions( blockEditorSettings().fontFamilies || [] ) );
	}

	function ThemeFontControl( props ) {
		return useSettings ? el( ThemeFontControlModern, props ) : el( ThemeFontControlLegacy, props );
	}

	function ThemeColorControlView( props, palette ) {
		return el(
			'div',
			{ className: 'popped-native-color-control' },
			el( 'span', { className: 'popped-native-control-label' }, props.label ),
			el( ColorPalette, {
				colors: palette,
				value: props.value,
				clearable: true,
				enableAlpha: false,
				onChange: function ( value ) { props.onChange( value || undefined ); }
			} )
		);
	}

	function ThemeColorControlModern( props ) {
		var resolved = wp.blockEditor.useSettings( 'color.palette' );
		return ThemeColorControlView( props, colorOptions( resolved && resolved.length ? resolved[ 0 ] : [] ) );
	}

	function ThemeColorControlLegacy( props ) {
		return ThemeColorControlView( props, colorOptions( blockEditorSettings().colors || [] ) );
	}

	function ThemeColorControl( props ) {
		return useSettings ? el( ThemeColorControlModern, props ) : el( ThemeColorControlLegacy, props );
	}

	function NativeLinkField( props ) {
		if ( LinkControl ) {
			return el(
				'div',
				{ className: 'popped-native-link-control' },
				el( 'span', { className: 'popped-native-control-label' }, props.label ),
				props.help && el( 'p', { className: 'popped-control-help' }, props.help ),
				el( LinkControl, {
					value: { url: props.value || '' },
					settings: [],
					onChange: function ( next ) { props.onChange( next && next.url ? next.url : undefined ); }
				} )
			);
		}
		if ( URLInput ) {
			return el(
				'div',
				{ className: 'popped-native-link-control' },
				el( 'span', { className: 'popped-native-control-label' }, props.label ),
				el( URLInput, {
					value: props.value || '',
					onChange: function ( value ) { props.onChange( value || undefined ); }
				} )
			);
		}
		return el( TextControl, {
			label: props.label,
			help: props.help,
			type: 'url',
			value: props.value || '',
			onChange: function ( value ) { props.onChange( value || undefined ); }
		} );
	}

	function HeadingLevelControl( props ) {
		return el( SelectControl, {
			label: props.label,
			value: String( props.value || props.fallback || 2 ),
			options: [ 2, 3, 4, 5, 6 ].map( function ( level ) {
				return { label: 'H' + level, value: String( level ) };
			} ),
			onChange: function ( value ) { props.onChange( parseInt( value, 10 ) ); }
		} );
	}


	function styleLabel( slug, style ) {
		if ( slug === 'featured-collection' && style === 'inherit' ) {
			return __( 'Use Collection Style', 'popped' );
		}

		var labels = {
			default: __( 'Default', 'popped' ),
			minimal: __( 'Minimal', 'popped' ),
			filmstrip: __( 'Filmstrip', 'popped' ),
			feature: __( 'Feature', 'popped' ),
			breaking: __( 'Breaking', 'popped' ),
			inherit: __( 'Inherit', 'popped' )
		};

		return labels[ style ] || style;
	}

	function options( pairs ) { return pairs.map( function ( item ) { return { label: item[ 0 ], value: item[ 1 ] }; } ); }
	function recommendedOptions( pairs, recommendedValue ) {
		return pairs.map( function ( item ) {
			return {
				label: item[ 0 ] + ( item[ 1 ] === recommendedValue ? ' · ' + __( 'Recommended', 'popped' ) : '' ),
				value: item[ 1 ]
			};
		} );
	}
	function has( list, value ) { return list.indexOf( value ) !== -1; }
	function daysInMonth( month ) {
		if ( month === 2 ) { return 29; }
		return has( [ 4, 6, 9, 11 ], month ) ? 30 : 31;
	}
	function hasOverride( attrs, keys ) { return keys.some( function ( key ) { return attrs[ key ] !== undefined; } ); }
	function resetKeys( setAttributes, keys ) { var update = {}; keys.forEach( function ( key ) { update[ key ] = undefined; } ); setAttributes( update ); }
	function setRecommended( setAttributes, keys, recommended ) {
		var update = {};
		keys.forEach( function ( key ) {
			var value = recommended ? recommended( key ) : undefined;
			update[ key ] = value !== undefined ? value : undefined;
		} );
		setAttributes( update );
	}
	function hasCustomOverride( attrs, keys, recommended ) {
		return keys.some( function ( key ) {
			if ( attrs[ key ] === undefined ) { return false; }
			var value = recommended ? recommended( key ) : undefined;
			return value === undefined || attrs[ key ] !== value;
		} );
	}
	function InspectorHint( text ) { return el( 'p', { className: 'popped-inspector-intro', key: 'popped-intro' }, text ); }
	function PresetButtons( props ) {
		return el(
			'div',
			{ className: 'popped-control-presets', role: 'group', 'aria-label': props.label },
			props.presets.map( function ( preset ) {
				return el( Button, {
					key: preset.value,
					variant: 'tertiary',
					isPressed: props.value === preset.value,
					onClick: function () { props.onChange( preset.value ); }
				}, preset.label );
			} )
		);
	}
	function PanelReset( props ) {
		if ( ! hasCustomOverride( props.attrs, props.keys, props.recommended ) ) { return null; }
		return el( 'div', { className: 'popped-panel-reset' },
			el( Button, { variant: 'tertiary', icon: 'undo', onClick: function () { setRecommended( props.setAttributes, props.keys, props.recommended ); } }, props.label || __( 'Restore recommended', 'popped' ) )
		);
	}
	function useDebouncedValue( value ) {
		var state = useState( value );
		useEffect( function () { var timer = window.setTimeout( function () { state[ 1 ]( value ); }, 250 ); return function () { window.clearTimeout( timer ); }; }, [ value ] );
		return state[ 0 ];
	}

	function TermSelector( props ) {
		var filterState = useState( '' );
		var filter = filterState[ 0 ];
		var setFilter = filterState[ 1 ];
		var debounced = useDebouncedValue( filter.trim() );
		var value = props.value && props.value.length ? props.value[ 0 ] : '';
		var result = useSelect( function ( select ) {
			var store = select( 'core' );
			var searchArgs = { search: debounced, per_page: 20, hide_empty: false, orderby: 'name', order: 'asc', _fields: 'id,name,slug' };
			var selectedArgs = { slug: value, per_page: 1, hide_empty: false, _fields: 'id,name,slug' };
			return {
				records: debounced.length >= 2 ? store.getEntityRecords( 'taxonomy', props.taxonomy, searchArgs ) : [],
				selected: value ? store.getEntityRecords( 'taxonomy', props.taxonomy, selectedArgs ) : [],
				loading: debounced.length >= 2 && store.isResolving( 'getEntityRecords', [ 'taxonomy', props.taxonomy, searchArgs ] )
			};
		}, [ props.taxonomy, debounced, value ] );
		var choices = ( result.records || [] ).slice();
		( result.selected || [] ).forEach( function ( term ) { if ( ! choices.some( function ( item ) { return item.slug === term.slug; } ) ) { choices.unshift( term ); } } );
		var help = result.loading ? __( 'Searching…', 'popped' ) : ( filter.trim().length < 2 ? __( 'Type at least two characters to search.', 'popped' ) : props.help );
		return el( Fragment, {},
			el( ComboboxControl, { label: props.label, help: help, value: value, options: choices.map( function ( term ) { return { value: term.slug, label: term.name }; } ), onFilterValueChange: setFilter, onChange: function ( next ) { props.onChange( next ? [ next ] : [] ); }, allowReset: true } ),
			result.loading && el( Spinner )
		);
	}

	function PostPicker( props ) {
		var selected = props.value || [];
		var filterState = useState( '' );
		var filter = filterState[ 0 ];
		var setFilter = filterState[ 1 ];
		var debounced = useDebouncedValue( filter.trim() );
		var dragState = useState( null );
		var dragged = dragState[ 0 ];
		var setDragged = dragState[ 1 ];
		var result = useSelect( function ( select ) {
			var store = select( 'core' );
			var searchArgs = { search: debounced, per_page: 20, status: 'publish', orderby: 'relevance', _fields: 'id,title' };
			var chosenArgs = { include: selected, per_page: Math.max( selected.length, 1 ), orderby: 'include', _fields: 'id,title' };
			return {
				records: debounced.length >= 2 ? store.getEntityRecords( 'postType', 'post', searchArgs ) : [],
				chosen: selected.length ? store.getEntityRecords( 'postType', 'post', chosenArgs ) : [],
				loading: debounced.length >= 2 && store.isResolving( 'getEntityRecords', [ 'postType', 'post', searchArgs ] )
			};
		}, [ debounced, selected.join( ',' ) ] );
		var byId = {};
		( result.chosen || [] ).forEach( function ( post ) { byId[ post.id ] = post; } );

		function title( post ) {
			if ( ! post || ! post.title || ! post.title.rendered ) { return __( 'Loading story…', 'popped' ); }
			return decodeEntities( post.title.rendered.replace( /<[^>]+>/g, '' ) );
		}

		function move( from, to ) {
			var next = selected.slice();
			var item = next.splice( from, 1 )[ 0 ];
			next.splice( to, 0, item );
			props.onChange( next );
		}

		var help = result.loading ? __( 'Searching…', 'popped' ) : ( filter.trim().length < 2 ? __( 'Type at least two characters to search.', 'popped' ) : props.help );

		return el( 'div', { className: 'popped-post-picker' },
			el( ComboboxControl, {
				label: props.label,
				help: help,
				value: '',
				options: ( result.records || [] )
					.filter( function ( post ) { return selected.indexOf( post.id ) === -1; } )
					.map( function ( post ) { return { value: String( post.id ), label: title( post ) }; } ),
				onFilterValueChange: setFilter,
				onChange: function ( next ) {
					if ( next ) {
						props.onChange( selected.concat( [ parseInt( next, 10 ) ] ) );
						setFilter( '' );
					}
				}
			} ),
			result.loading && el( Spinner ),
			selected.length > 0 && el( 'div', { className: 'popped-picker-summary' },
				el( 'span', {}, selected.length === 1 ? __( '1 story selected', 'popped' ) : selected.length + ' ' + __( 'stories selected', 'popped' ) ),
				el( Button, { variant: 'tertiary', isDestructive: true, onClick: function () { props.onChange( [] ); } }, __( 'Clear all', 'popped' ) )
			),
			selected.length > 0 && el( 'ol', { className: 'popped-selected-posts', 'aria-label': __( 'Selected stories', 'popped' ) }, selected.map( function ( id, index ) {
				var storyTitle = title( byId[ id ] );
				return el( 'li', {
					key: id,
					draggable: props.reorder !== false,
					onDragStart: function () { setDragged( index ); },
					onDragOver: function ( event ) { event.preventDefault(); },
					onDrop: function () {
						if ( dragged !== null && dragged !== index ) { move( dragged, index ); }
						setDragged( null );
					}
				},
					el( 'span', { className: 'popped-picker-handle', 'aria-hidden': true, title: props.reorder === false ? '' : __( 'Drag to reorder', 'popped' ) }, props.reorder === false ? '•' : '⋮⋮' ),
					el( 'span', { className: 'popped-picker-title', title: storyTitle }, storyTitle ),
					props.reorder !== false && el( Button, { icon: 'arrow-up-alt2', label: __( 'Move up', 'popped' ), disabled: index === 0, onClick: function () { move( index, index - 1 ); } } ),
					props.reorder !== false && el( Button, { icon: 'arrow-down-alt2', label: __( 'Move down', 'popped' ), disabled: index === selected.length - 1, onClick: function () { move( index, index + 1 ); } } ),
					el( Button, { icon: 'no-alt', label: __( 'Remove story', 'popped' ), isDestructive: true, onClick: function () { props.onChange( selected.filter( function ( item ) { return item !== id; } ) ); } } )
				);
			} ) )
		);
	}

	function ContentPanel( props ) {
		var slug = props.slug, attrs = props.attrs, effective = props.effective, recommended = props.recommended, set = props.set, controls = [];
		var queryBlocks = has( [ 'timeline', 'horizontal-timeline', 'mini-timeline', 'latest-stories', 'on-this-day', 'also-on-this-day', 'archive-explorer' ], slug );

		controls.push( InspectorHint( __( 'Choose the stories and editorial content this block uses. Visual styling lives in the Styles tab.', 'popped' ) ) );

		if ( has( [ 'timeline', 'horizontal-timeline', 'mini-timeline', 'latest-stories', 'on-this-day', 'also-on-this-day', 'archive-explorer', 'year-navigator' ], slug ) ) {
			controls.push( el( TextControl, { key: 'title', label: __( 'Section title', 'popped' ), value: effective( 'title' ) || '', onChange: function ( value ) { set( 'title', value ); } } ) );
		}

		if ( slug === 'featured-collection' ) {
			controls.push( el( SelectControl, {
				key: 'collection',
				label: __( 'Collection', 'popped' ),
				help: config.collections.length ? __( 'Content and the collection’s native style are inherited until this block overrides them.', 'popped' ) : __( 'Create a collection in Popped → Collections first.', 'popped' ),
				value: effective( 'collection' ) || '',
				options: [ { label: __( 'Choose a collection', 'popped' ), value: '' }, { label: __( 'Collection index', 'popped' ), value: 'all' } ].concat( config.collections || [] ),
				onChange: function ( value ) { set( 'collection', value ); }
			} ) );
			if ( effective( 'collection' ) === 'all' ) {
				controls.push( el( RangeControl, {
					key: 'collection-count',
					label: __( 'Collections shown', 'popped' ),
					value: effective( 'count' ) || recommended( 'count' ) || 5,
					min: 1,
					max: 12,
					step: 1,
					allowReset: true,
					resetFallbackValue: recommended( 'count' ) || 5,
					withInputField: true,
					onChange: function ( value ) { set( 'count', value ); }
				} ) );
			}
		}

		if ( queryBlocks ) {
			controls.push( el( SelectControl, {
				key: 'source',
				label: __( 'Show posts from', 'popped' ),
				value: effective( 'source' ),
				options: recommendedOptions( [
					[ __( 'All posts', 'popped' ), 'all' ],
					[ __( 'Timeline posts', 'popped' ), 'timeline' ],
					[ __( 'A category', 'popped' ), 'category' ],
					[ __( 'A tag', 'popped' ), 'tag' ],
					[ __( 'A category and tag', 'popped' ), 'categories-tags' ],
					[ __( 'Stories I choose', 'popped' ), 'manual' ]
				], recommended( 'source' ) ),
				onChange: function ( value ) {
					props.setAttributes( {
						source: value,
						order: value === 'manual' ? 'manual' : ( effective( 'order' ) === 'manual' ? 'newest' : attrs.order )
					} );
				}
			} ) );
			if ( has( [ 'category', 'categories-tags' ], effective( 'source' ) ) ) {
				controls.push( el( TermSelector, { key: 'category', taxonomy: 'category', label: __( 'Category', 'popped' ), value: attrs.categories || [], onChange: function ( value ) { set( 'categories', value ); } } ) );
			}
			if ( has( [ 'tag', 'categories-tags' ], effective( 'source' ) ) ) {
				controls.push( el( TermSelector, { key: 'tag', taxonomy: 'post_tag', label: __( 'Tag', 'popped' ), value: attrs.tags || [], onChange: function ( value ) { set( 'tags', value ); } } ) );
			}
			if ( effective( 'source' ) === 'manual' ) {
				controls.push( el( PostPicker, { key: 'posts', label: __( 'Choose stories', 'popped' ), value: attrs.posts || [], onChange: function ( value ) { set( 'posts', value ); }, reorder: true } ) );
			}
		}

		if ( has( [ 'related-stories', 'continue-story' ], slug ) ) {
			controls.push( el( SelectControl, {
				key: 'mode',
				label: __( 'Story selection', 'popped' ),
				value: effective( 'selectionMode' ),
				options: recommendedOptions( [ [ __( 'Automatic', 'popped' ), 'automatic' ], [ __( 'Manual', 'popped' ), 'manual' ] ], recommended( 'selectionMode' ) ),
				onChange: function ( value ) { set( 'selectionMode', value ); }
			} ) );
			if ( effective( 'selectionMode' ) === 'manual' ) {
				controls.push( el( PostPicker, { key: 'manual', label: __( 'Selected stories', 'popped' ), value: attrs.posts || [], onChange: function ( value ) { set( 'posts', value ); }, reorder: true } ) );
			}
			if ( slug === 'related-stories' && effective( 'selectionMode' ) === 'automatic' ) {
				controls.push( el( SelectControl, {
					key: 'relevance',
					label: __( 'Match stories by', 'popped' ),
					value: effective( 'relevance' ),
					options: recommendedOptions( [ [ __( 'Categories and tags', 'popped' ), 'both' ], [ __( 'Categories only', 'popped' ), 'category' ], [ __( 'Tags only', 'popped' ), 'tag' ] ], recommended( 'relevance' ) ),
					onChange: function ( value ) { set( 'relevance', value ); }
				} ) );
			}
		}

		if ( slug === 'news-ticker' ) {
			controls.push( el( SelectControl, {
				key: 'ticker-source',
				label: __( 'Headlines', 'popped' ),
				value: effective( 'source' ),
				options: recommendedOptions( [ [ __( 'Latest posts', 'popped' ), 'latest' ], [ __( 'Stories I choose', 'popped' ), 'manual' ], [ __( 'Chosen stories, then latest', 'popped' ), 'mixed' ] ], recommended( 'source' ) ),
				onChange: function ( value ) { set( 'source', value ); }
			} ) );
			if ( effective( 'source' ) !== 'latest' ) {
				controls.push( el( PostPicker, { key: 'ticker-posts', label: __( 'Choose headlines', 'popped' ), value: attrs.posts || [], onChange: function ( value ) { set( 'posts', value ); }, reorder: true } ) );
			}
			controls.push( el( TextControl, { key: 'ticker-label', label: __( 'Label', 'popped' ), value: effective( 'tickerLabel' ), onChange: function ( value ) { set( 'tickerLabel', value ); } } ) );
		}

		if ( has( [ 'timeline', 'horizontal-timeline', 'mini-timeline', 'on-this-day', 'also-on-this-day', 'latest-stories', 'archive-explorer', 'related-stories', 'news-ticker' ], slug ) ) {
			var countMaximums = {
				timeline: 36,
				'horizontal-timeline': 12,
				'mini-timeline': 8,
				'on-this-day': 12,
				'also-on-this-day': 8,
				'latest-stories': 12,
				'archive-explorer': 24,
				'related-stories': 8,
				'news-ticker': 12
			};
			var countLabels = {
				timeline: __( 'Stories per page', 'popped' ),
				'archive-explorer': __( 'Stories per page', 'popped' ),
				'news-ticker': __( 'Headlines', 'popped' )
			};
			var countRecommended = recommended( 'count' ) || effective( 'count' );
			controls.push( el( RangeControl, {
				key: 'count',
				label: countLabels[ slug ] || __( 'Number of stories', 'popped' ),
				value: effective( 'count' ),
				min: 1,
				max: countMaximums[ slug ] || 12,
				step: 1,
				allowReset: true,
				resetFallbackValue: countRecommended,
				withInputField: true,
				isShiftStepEnabled: true,
				shiftStep: 5,
				onChange: function ( value ) { set( 'count', value ); }
			} ) );
			if ( countRecommended > 1 ) {
				controls.push( el( 'p', { key: 'count-help', className: 'popped-control-help popped-control-help--after' }, __( 'Recommended', 'popped' ) + ': ' + countRecommended + '. ' + __( 'Higher values make this section substantially longer.', 'popped' ) ) );
			}
		}

		if ( has( [ 'timeline', 'horizontal-timeline', 'mini-timeline', 'latest-stories', 'archive-explorer', 'also-on-this-day' ], slug ) ) {
			var orderChoices = [ [ __( 'Oldest first', 'popped' ), 'chronological' ], [ __( 'Newest first', 'popped' ), 'newest' ] ];
			if ( effective( 'source' ) === 'manual' ) { orderChoices.push( [ __( 'Chosen order', 'popped' ), 'manual' ] ); }
			controls.push( el( SelectControl, { key: 'order', label: __( 'Order', 'popped' ), value: effective( 'order' ), options: recommendedOptions( orderChoices, recommended( 'order' ) ), onChange: function ( value ) { set( 'order', value ); } } ) );
		}

		if ( slug === 'on-this-day' ) {
			controls.push( el( ToggleControl, {
				key: 'today',
				label: __( 'Use today’s date automatically', 'popped' ),
				checked: !! effective( 'useToday' ),
				onChange: function ( value ) {
					if ( ! value && attrs.month === undefined && attrs.day === undefined ) {
						props.setAttributes( { useToday: false, month: config.currentMonth || 1, day: config.currentDay || 1 } );
						return;
					}
					set( 'useToday', value );
				}
			} ) );
			if ( ! effective( 'useToday' ) ) {
				controls.push(
					el( SelectControl, {
						key: 'month',
						label: __( 'Month', 'popped' ),
						value: String( effective( 'month' ) || config.currentMonth || 1 ),
						options: options( [
							[ __( 'January', 'popped' ), '1' ], [ __( 'February', 'popped' ), '2' ], [ __( 'March', 'popped' ), '3' ],
							[ __( 'April', 'popped' ), '4' ], [ __( 'May', 'popped' ), '5' ], [ __( 'June', 'popped' ), '6' ],
							[ __( 'July', 'popped' ), '7' ], [ __( 'August', 'popped' ), '8' ], [ __( 'September', 'popped' ), '9' ],
							[ __( 'October', 'popped' ), '10' ], [ __( 'November', 'popped' ), '11' ], [ __( 'December', 'popped' ), '12' ]
						] ),
						onChange: function ( value ) {
							var nextMonth = parseInt( value, 10 );
							var nextDay = Math.min( effective( 'day' ) || config.currentDay || 1, daysInMonth( nextMonth ) );
							props.setAttributes( { month: nextMonth, day: nextDay } );
						}
					} ),
					el( RangeControl, {
						key: 'day',
						label: __( 'Day', 'popped' ),
						value: effective( 'day' ) || config.currentDay || 1,
						min: 1,
						max: daysInMonth( effective( 'month' ) || config.currentMonth || 1 ),
						step: 1,
						withInputField: true,
						onChange: function ( value ) { set( 'day', value ); }
					} )
				);
			}
			controls.push( el( TextControl, { key: 'fallback', label: __( 'No-results message', 'popped' ), value: effective( 'fallbackText' ), onChange: function ( value ) { set( 'fallbackText', value ); } } ) );
		}

		if ( slug === 'year-navigator' ) {
			controls.push(
				el( RangeControl, {
					key: 'start-year',
					label: __( 'First year', 'popped' ),
					value: effective( 'startYear' ),
					min: 1000,
					max: 3000,
					step: 1,
					allowReset: true,
					resetFallbackValue: recommended( 'startYear' ) || effective( 'startYear' ),
					withInputField: true,
					isShiftStepEnabled: true,
					shiftStep: 10,
					onChange: function ( value ) { set( 'startYear', value ); }
				} ),
				el( RangeControl, {
					key: 'end-year',
					label: __( 'Last year', 'popped' ),
					value: effective( 'endYear' ),
					min: 1000,
					max: 3000,
					step: 1,
					allowReset: true,
					resetFallbackValue: recommended( 'endYear' ) || effective( 'endYear' ),
					withInputField: true,
					isShiftStepEnabled: true,
					shiftStep: 10,
					onChange: function ( value ) { set( 'endYear', value ); }
				} ),
				el( ToggleControl, {
					key: 'limit-years',
					label: __( 'Limit years shown', 'popped' ),
					help: effective( 'maxYears' ) > 0 ? __( 'Limits the navigator to the most useful years within your selected range.', 'popped' ) : __( 'Shows every populated year, up to the 100-year safety limit.', 'popped' ),
					checked: effective( 'maxYears' ) > 0,
					onChange: function ( value ) { set( 'maxYears', value ? ( recommended( 'maxYears' ) || 12 ) : 0 ); }
				} ),
				effective( 'maxYears' ) > 0 && el( RangeControl, {
					key: 'max-years',
					label: __( 'Years shown', 'popped' ),
					value: effective( 'maxYears' ),
					min: 1,
					max: 100,
					step: 1,
					allowReset: true,
					resetFallbackValue: recommended( 'maxYears' ) || 12,
					withInputField: true,
					onChange: function ( value ) { set( 'maxYears', value ); }
				} )
			);
		}

		if ( slug === 'archive-explorer' ) {
			controls.push(
				el( ToggleControl, { key: 'filter-search', label: __( 'Visitor search', 'popped' ), checked: !! effective( 'filterSearch' ), onChange: function ( value ) { set( 'filterSearch', value ); } } ),
				el( ToggleControl, { key: 'filter-year', label: __( 'Year & month filters', 'popped' ), checked: !! effective( 'filterYear' ), onChange: function ( value ) { set( 'filterYear', value ); } } ),
				el( ToggleControl, { key: 'filter-category', label: __( 'Category filter', 'popped' ), checked: !! effective( 'filterCategory' ), onChange: function ( value ) { set( 'filterCategory', value ); } } ),
				el( ToggleControl, { key: 'filter-tag', label: __( 'Tag filter', 'popped' ), checked: !! effective( 'filterTag' ), onChange: function ( value ) { set( 'filterTag', value ); } } )
			);
		}

		if ( slug === 'search' ) {
			controls.push(
				el( ToggleControl, { key: 'result-count', label: __( 'Show result count', 'popped' ), checked: !! effective( 'showResultCount' ), onChange: function ( value ) { set( 'showResultCount', value ); } } ),
				el( ToggleControl, { key: 'search-category', label: __( 'Category filter', 'popped' ), checked: !! effective( 'filterCategory' ), onChange: function ( value ) { set( 'filterCategory', value ); } } ),
				el( ToggleControl, { key: 'search-tag', label: __( 'Tag filter', 'popped' ), checked: !! effective( 'filterTag' ), onChange: function ( value ) { set( 'filterTag', value ); } } )
			);
		}

		return el( PanelBody, { title: __( 'Content', 'popped' ), initialOpen: true }, controls );
	}

	function LayoutPanel( props ) {
		var slug = props.slug, e = props.effective, set = props.set, recommended = props.recommended, controls = [];
		controls.push( InspectorHint( __( 'Choose the layout. Popped handles responsive breakpoints automatically.', 'popped' ) ) );

		if ( slug === 'timeline' ) {
			controls.push(
				InspectorHint( __( 'Timeline is the full vertical chronology. Use Horizontal Timeline when you want a swipeable rail.', 'popped' ) ),
				el( ToggleControl, { key: 'paginate', label: __( 'Paginate results', 'popped' ), checked: !! e( 'paginate' ), onChange: function ( value ) { set( 'paginate', value ); } } ),
				el( ToggleControl, { key: 'years', label: __( 'Group by year', 'popped' ), checked: !! e( 'groupByYear' ), onChange: function ( value ) { set( 'groupByYear', value ); } } )
			);
		}

		if ( slug === 'horizontal-timeline' ) {
			controls.push(
				el( SelectControl, { key: 'width', label: __( 'Card width', 'popped' ), value: e( 'cardWidth' ), options: recommendedOptions( [ [ __( 'Narrow', 'popped' ), 'narrow' ], [ __( 'Medium', 'popped' ), 'medium' ], [ __( 'Wide', 'popped' ), 'wide' ] ], recommended( 'cardWidth' ) ), onChange: function ( value ) { set( 'cardWidth', value ); } } ),
				el( ToggleControl, { key: 'navigation', label: __( 'Navigation buttons', 'popped' ), checked: !! e( 'showNavigation' ), onChange: function ( value ) { set( 'showNavigation', value ); } } )
			);
		}

		if ( has( [ 'latest-stories', 'related-stories', 'featured-collection', 'search' ], slug ) ) {
			var editorialPresentations = slug === 'search'
				? [ [ __( 'List', 'popped' ), 'list' ], [ __( 'Cards', 'popped' ), 'cards' ] ]
				: [ [ __( 'Balanced grid', 'popped' ), 'cards' ], [ __( 'Lead + supporting', 'popped' ), 'lead' ], [ __( 'Compact list', 'popped' ), 'list' ], [ __( 'Swipeable rail', 'popped' ), 'rail' ] ];
			controls.push(
				el( SelectControl, { key: 'display', label: __( 'Presentation', 'popped' ), help: slug === 'search' ? __( 'Choose how search results are shown.', 'popped' ) : __( 'Choose an editorial composition, not just a cosmetic style.', 'popped' ), value: e( 'displayLayout' ) || ( slug === 'search' ? 'list' : 'cards' ), options: recommendedOptions( editorialPresentations, recommended( 'displayLayout' ) ), onChange: function ( value ) { set( 'displayLayout', value ); } } ),
				! has( [ 'list', 'rail', 'lead' ], e( 'displayLayout' ) ) && el( SelectControl, {
					key: 'columns',
					label: __( 'Columns on wide screens', 'popped' ),
					help: __( 'Popped automatically reduces columns on smaller screens.', 'popped' ),
					value: String( e( 'columns' ) || 3 ),
					options: recommendedOptions( [
						[ __( '1 — Feature', 'popped' ), '1' ],
						[ __( '2 — Spacious', 'popped' ), '2' ],
						[ __( '3 — Balanced', 'popped' ), '3' ],
						[ __( '4 — Compact', 'popped' ), '4' ]
					], String( recommended( 'columns' ) || 3 ) ),
					onChange: function ( value ) { set( 'columns', parseInt( value, 10 ) ); }
				} )
			);
		}

		if ( slug === 'archive-explorer' ) {
			controls.push(
				el( SelectControl, { key: 'display', label: __( 'Initial view', 'popped' ), value: e( 'displayLayout' ), options: recommendedOptions( [ [ __( 'Grid', 'popped' ), 'grid' ], [ __( 'List', 'popped' ), 'list' ], [ __( 'Timeline', 'popped' ), 'timeline' ], [ __( 'Magazine', 'popped' ), 'magazine' ] ], recommended( 'displayLayout' ) ), onChange: function ( value ) { set( 'displayLayout', value ); } } ),
				has( [ 'grid', 'magazine' ], e( 'displayLayout' ) ) && el( SelectControl, {
					key: 'columns',
					label: __( 'Columns on wide screens', 'popped' ),
					help: __( 'Popped automatically reduces columns on smaller screens.', 'popped' ),
					value: String( e( 'columns' ) || 3 ),
					options: recommendedOptions( [
						[ __( '1 — Feature', 'popped' ), '1' ],
						[ __( '2 — Spacious', 'popped' ), '2' ],
						[ __( '3 — Balanced', 'popped' ), '3' ],
						[ __( '4 — Compact', 'popped' ), '4' ]
					], String( recommended( 'columns' ) || 3 ) ),
					onChange: function ( value ) { set( 'columns', parseInt( value, 10 ) ); }
				} )
			);
		}

		if ( slug === 'year-navigator' ) {
			controls.push(
				el( SelectControl, {
					key: 'year-display',
					label: __( 'Presentation', 'popped' ),
					help: e( 'displayLayout' ) === 'scroll' ? __( 'Fits up to 12 years evenly across one desktop line. Longer ranges link to the full archive; narrow screens keep one touch-scroll row.', 'popped' ) : '',
					value: e( 'displayLayout' ),
					options: recommendedOptions( [
						[ __( 'Grid', 'popped' ), 'grid' ],
						[ __( 'Single row (fit)', 'popped' ), 'scroll' ],
						[ __( 'Inline (wrap)', 'popped' ), 'inline' ],
						[ __( 'Compact list', 'popped' ), 'list' ]
					], recommended( 'displayLayout' ) ),
					onChange: function ( value ) { set( 'displayLayout', value ); }
				} ),
				e( 'displayLayout' ) === 'grid' && el( SelectControl, {
					key: 'year-columns',
					label: __( 'Columns on wide screens', 'popped' ),
					help: __( 'Popped reduces columns automatically on smaller screens.', 'popped' ),
					value: String( e( 'columns' ) || 5 ),
					options: recommendedOptions( [
						[ '2', '2' ], [ '3', '3' ], [ '4', '4' ], [ '5', '5' ], [ '6', '6' ]
					], String( recommended( 'columns' ) || 5 ) ),
					onChange: function ( value ) { set( 'columns', parseInt( value, 10 ) ); }
				} ),
				el( SelectControl, {
					key: 'year-order',
					label: __( 'Year order', 'popped' ),
					value: e( 'yearOrder' ) || 'oldest',
					options: recommendedOptions( [
						[ __( 'Newest first', 'popped' ), 'newest' ],
						[ __( 'Oldest first', 'popped' ), 'oldest' ]
					], recommended( 'yearOrder' ) || 'newest' ),
					onChange: function ( value ) { set( 'yearOrder', value ); }
				} ),
				el( ToggleControl, { key: 'counts', label: __( 'Story counts', 'popped' ), checked: !! e( 'showCounts' ), onChange: function ( value ) { set( 'showCounts', value ); } } ),
				el( NativeLinkField, { key: 'destination', label: __( 'Archive destination', 'popped' ), help: __( 'Leave blank to use the configured archive page or the current page.', 'popped' ), value: e( 'destination' ) || config.archiveUrl || '', onChange: function ( value ) { set( 'destination', value ); } } )
			);
		}

		if ( slug === 'mini-timeline' || slug === 'horizontal-timeline' ) {
			if ( slug === 'horizontal-timeline' ) {
				controls.push(
					el( ToggleControl, { key: 'result-count', label: __( 'Show item count', 'popped' ), checked: !! e( 'showResultCount' ), onChange: function ( value ) { set( 'showResultCount', value ); } } )
				);
			}
			controls.push(
				el( ToggleControl, { key: 'view-link', label: __( 'Full timeline link', 'popped' ), checked: !! e( 'showViewLink' ), onChange: function ( value ) { set( 'showViewLink', value ); } } ),
				e( 'showViewLink' ) && el( TextControl, { key: 'link-text', label: __( 'Link text', 'popped' ), value: e( 'linkText' ), onChange: function ( value ) { set( 'linkText', value ); } } ),
				e( 'showViewLink' ) && el( NativeLinkField, { key: 'link-url', label: __( 'Destination', 'popped' ), help: __( 'Leave blank to use the configured Timeline page or the current page.', 'popped' ), value: e( 'linkUrl' ) || config.timelineUrl || '', onChange: function ( value ) { set( 'linkUrl', value ); } } )
			);
		}

		if ( slug === 'news-ticker' ) {
			controls.push(
				el( SelectControl, { key: 'speed', label: __( 'Movement', 'popped' ), help: __( 'Reduced or disabled site motion, and the operating-system preference, keep this static.', 'popped' ), value: e( 'tickerSpeed' ), options: recommendedOptions( [ [ __( 'Static', 'popped' ), 'static' ], [ __( 'Slow', 'popped' ), 'slow' ], [ __( 'Standard', 'popped' ), 'standard' ] ], recommended( 'tickerSpeed' ) ), onChange: function ( value ) { set( 'tickerSpeed', value ); } } ),
				e( 'tickerSpeed' ) !== 'static' && el( SelectControl, { key: 'direction', label: __( 'Direction', 'popped' ), value: e( 'tickerDirection' ) || 'left', options: recommendedOptions( [ [ __( 'Move left', 'popped' ), 'left' ], [ __( 'Move right', 'popped' ), 'right' ] ], recommended( 'tickerDirection' ) || 'left' ), onChange: function ( value ) { set( 'tickerDirection', value ); } } ),
				el( SelectControl, { key: 'separator', label: __( 'Headline separator', 'popped' ), value: e( 'tickerSeparator' ) || 'dot', options: recommendedOptions( [ [ __( 'Dot', 'popped' ), 'dot' ], [ __( 'Bullet', 'popped' ), 'bullet' ], [ __( 'Slash', 'popped' ), 'slash' ], [ __( 'None', 'popped' ), 'none' ] ], recommended( 'tickerSeparator' ) || 'dot' ), onChange: function ( value ) { set( 'tickerSeparator', value ); } } ),
				e( 'tickerSpeed' ) !== 'static' && el( ToggleControl, { key: 'pause', label: __( 'Pause on hover or focus', 'popped' ), checked: !! e( 'tickerPause' ), onChange: function ( value ) { set( 'tickerPause', value ); } } ),
				el( ToggleControl, { key: 'date', label: __( 'Show dates', 'popped' ), checked: !! e( 'showDate' ), onChange: function ( value ) { set( 'showDate', value ); } } )
			);
		}


		return el( PanelBody, { title: __( 'Layout & behaviour', 'popped' ), initialOpen: false }, controls );
	}

	function ImagesPanel( props ) {
		var e = props.effective, set = props.set, recommended = props.recommended, featuredCollection = props.slug === 'featured-collection';
		var imageKeys = [ 'showImage', 'showCollectionImage', 'imageRatio', 'imageFit', 'imagePosition', 'featureSize', 'radius' ];
		var controls = [
			InspectorHint( __( 'Crop and focal controls apply to story imagery inside this block. Use Gutenberg’s outer block styles for the block background and border.', 'popped' ) )
		];

		if ( featuredCollection ) {
			controls.push( el( ToggleControl, {
				key: 'collection-image',
				label: __( 'Show collection image', 'popped' ),
				help: __( 'Its crop follows the selected image shape and focal position.', 'popped' ),
				checked: !! e( 'showCollectionImage' ),
				onChange: function ( value ) { set( 'showCollectionImage', value ); }
			} ) );
		}

		controls.push( el( ToggleControl, {
			key: 'story-images',
			label: featuredCollection ? __( 'Show story images', 'popped' ) : __( 'Show featured images', 'popped' ),
			checked: !! e( 'showImage' ),
			onChange: function ( value ) { set( 'showImage', value ); }
		} ) );

		if ( e( 'showImage' ) ) {
			if ( props.slug === 'on-this-day' ) {
				controls.push( el( SelectControl, {
					key: 'feature-size',
					label: __( 'Feature image size', 'popped' ),
					help: __( 'Changes the image-to-copy proportion on larger screens. Phones still use a full-width image.', 'popped' ),
					value: e( 'featureSize' ) || 'compact',
					options: recommendedOptions( [
						[ __( 'Compact', 'popped' ), 'compact' ],
						[ __( 'Standard', 'popped' ), 'standard' ],
						[ __( 'Large', 'popped' ), 'large' ]
					], recommended( 'featureSize' ) || 'compact' ),
					onChange: function ( value ) { set( 'featureSize', value ); }
				} ) );
			}
			controls.push(
				el( SelectControl, {
					key: 'image-shape',
					label: featuredCollection ? __( 'Story image shape', 'popped' ) : __( 'Image shape', 'popped' ),
					value: e( 'imageRatio' ),
					options: recommendedOptions( [
						[ __( 'Original', 'popped' ), 'original' ],
						[ __( 'Classic 3:2', 'popped' ), 'classic' ],
						[ __( 'Landscape 4:3', 'popped' ), 'landscape' ],
						[ __( 'Wide 16:9', 'popped' ), 'wide' ],
						[ __( 'Cinematic 21:9', 'popped' ), 'cinematic' ],
						[ __( 'Square 1:1', 'popped' ), 'square' ],
						[ __( 'Portrait 4:5', 'popped' ), 'portrait' ],
						[ __( 'Tall portrait 2:3', 'popped' ), 'tall' ]
					], recommended( 'imageRatio' ) ),
					onChange: function ( value ) { set( 'imageRatio', value ); }
				} ),
				el( SelectControl, {
					key: 'image-fit',
					label: featuredCollection ? __( 'Story image fit', 'popped' ) : __( 'Image fit', 'popped' ),
					help: e( 'imageRatio' ) === 'original' ? __( 'Fit only affects constrained image shapes.', 'popped' ) : undefined,
					value: e( 'imageFit' ),
					options: recommendedOptions( [ [ __( 'Crop to fill', 'popped' ), 'cover' ], [ __( 'Show whole image', 'popped' ), 'contain' ] ], recommended( 'imageFit' ) ),
					onChange: function ( value ) { set( 'imageFit', value ); }
				} ),
				e( 'imageFit' ) === 'cover' && e( 'imageRatio' ) !== 'original' && el( SelectControl, {
					key: 'image-position',
					label: __( 'Crop focal position', 'popped' ),
					help: __( 'Protect the most important part of the image when its crop changes.', 'popped' ),
					value: e( 'imagePosition' ) || 'center',
					options: recommendedOptions( [
						[ __( 'Centre', 'popped' ), 'center' ],
						[ __( 'Top', 'popped' ), 'top' ],
						[ __( 'Bottom', 'popped' ), 'bottom' ],
						[ __( 'Left', 'popped' ), 'left' ],
						[ __( 'Right', 'popped' ), 'right' ]
					], recommended( 'imagePosition' ) ),
					onChange: function ( value ) { set( 'imagePosition', value ); }
				} ),
				el( SelectControl, {
					key: 'corner-shape',
					label: featuredCollection ? __( 'Story image corners', 'popped' ) : __( 'Image corners', 'popped' ),
					value: e( 'radius' ),
					options: recommendedOptions( [ [ __( 'Use site setting', 'popped' ), 'inherit' ], [ __( 'Square', 'popped' ), 'square' ], [ __( 'Soft', 'popped' ), 'soft' ], [ __( 'Rounded', 'popped' ), 'rounded' ] ], recommended( 'radius' ) ),
					onChange: function ( value ) { set( 'radius', value ); }
				} )
			);
		}

		controls.push( el( PanelReset, { key: 'reset-images', attrs: props.attrs, keys: imageKeys, setAttributes: props.setAttributes, recommended: recommended } ) );
		return el( PanelBody, { title: __( 'Story images', 'popped' ), initialOpen: false }, controls );
	}

	function StoryDetailsPanel( props ) {
		var e = props.effective, set = props.set, recommended = props.recommended;
		var detailKeys = [ 'showDate', 'showCategory', 'showTags', 'showAuthor', 'showExcerpt', 'excerptLength' ];
		return el(
			PanelBody,
			{ title: __( 'Story details', 'popped' ), initialOpen: false },
			InspectorHint( __( 'Choose which supporting details readers see. Their visual treatment is controlled in the Styles tab.', 'popped' ) ),
			el( ToggleControl, { label: __( 'Show date', 'popped' ), checked: !! e( 'showDate' ), onChange: function ( value ) { set( 'showDate', value ); } } ),
			el( ToggleControl, { label: __( 'Show category', 'popped' ), checked: !! e( 'showCategory' ), onChange: function ( value ) { set( 'showCategory', value ); } } ),
			el( ToggleControl, { label: __( 'Show tags', 'popped' ), checked: !! e( 'showTags' ), onChange: function ( value ) { set( 'showTags', value ); } } ),
			el( ToggleControl, { label: __( 'Show author', 'popped' ), checked: !! e( 'showAuthor' ), onChange: function ( value ) { set( 'showAuthor', value ); } } ),
			el( ToggleControl, { label: __( 'Show excerpt', 'popped' ), checked: !! e( 'showExcerpt' ), onChange: function ( value ) { set( 'showExcerpt', value ); } } ),
			e( 'showExcerpt' ) && el( RangeControl, {
				label: __( 'Excerpt length', 'popped' ),
				help: __( 'Approximate word count. Around 28 words is a balanced editorial default.', 'popped' ),
				value: e( 'excerptLength' ),
				min: 8,
				max: 60,
				step: 2,
				allowReset: true,
				resetFallbackValue: recommended( 'excerptLength' ) || 28,
				withInputField: true,
				isShiftStepEnabled: true,
				shiftStep: 5,
				onChange: function ( value ) { set( 'excerptLength', value ); }
			} ),
			el( PanelReset, { attrs: props.attrs, keys: detailKeys, setAttributes: props.setAttributes, recommended: recommended } )
		);
	}

	function TypographyPanel( props ) {
		var e = props.effective, set = props.set, recommended = props.recommended;
		var typeKeys = [ 'headingSize', 'headingWeight', 'headingLineHeight', 'excerptSize' ];
		return el(
			PanelBody,
			{ title: __( 'Type & metadata', 'popped' ), initialOpen: false },
			InspectorHint( __( 'Adjust card-title hierarchy and the tone of supporting details.', 'popped' ) ),
			el( SelectControl, {
				label: __( 'Card title size', 'popped' ),
				value: e( 'headingSize' ),
				options: recommendedOptions( [
					[ __( 'Small', 'popped' ), 'small' ],
					[ __( 'Medium', 'popped' ), 'medium' ],
					[ __( 'Large', 'popped' ), 'large' ],
					[ __( 'Display', 'popped' ), 'display' ]
				], recommended( 'headingSize' ) ),
				onChange: function ( value ) { set( 'headingSize', value ); }
			} ),
			el( SelectControl, {
				label: __( 'Card title weight', 'popped' ),
				value: e( 'headingWeight' ) || 'medium',
				options: recommendedOptions( [
					[ __( 'Regular', 'popped' ), 'regular' ],
					[ __( 'Medium', 'popped' ), 'medium' ],
					[ __( 'Semibold', 'popped' ), 'semibold' ],
					[ __( 'Bold', 'popped' ), 'bold' ]
				], recommended( 'headingWeight' ) ),
				onChange: function ( value ) { set( 'headingWeight', value ); }
			} ),
			el( SelectControl, {
				label: __( 'Card title line height', 'popped' ),
				value: e( 'headingLineHeight' ) || 'snug',
				options: recommendedOptions( [
					[ __( 'Tight', 'popped' ), 'tight' ],
					[ __( 'Snug', 'popped' ), 'snug' ],
					[ __( 'Standard', 'popped' ), 'balanced' ],
					[ __( 'Relaxed', 'popped' ), 'comfortable' ]
				], recommended( 'headingLineHeight' ) ),
				onChange: function ( value ) { set( 'headingLineHeight', value ); }
			} ),
			e( 'showExcerpt' ) && el( SelectControl, {
				label: __( 'Excerpt size', 'popped' ),
				value: e( 'excerptSize' ) || 'medium',
				options: recommendedOptions( [ [ __( 'Small', 'popped' ), 'small' ], [ __( 'Medium', 'popped' ), 'medium' ], [ __( 'Large', 'popped' ), 'large' ] ], recommended( 'excerptSize' ) ),
				onChange: function ( value ) { set( 'excerptSize', value ); }
			} ),
			! e( 'showExcerpt' ) && el( 'p', { className: 'popped-control-help' }, __( 'Turn on excerpts in Settings → Story details to style excerpt text.', 'popped' ) ),
			el( SelectControl, {
				label: __( 'Metadata tone', 'popped' ),
				value: e( 'metadataTone' ) || 'muted',
				options: recommendedOptions( [ [ __( 'Muted', 'popped' ), 'muted' ], [ __( 'Ink', 'popped' ), 'ink' ], [ __( 'Accent', 'popped' ), 'accent' ] ], recommended( 'metadataTone' ) ),
				onChange: function ( value ) { set( 'metadataTone', value ); }
			} ),
			el( SelectControl, {
				label: __( 'Metadata case', 'popped' ),
				value: e( 'metadataCase' ) || 'normal',
				options: recommendedOptions( [ [ __( 'Natural case', 'popped' ), 'normal' ], [ __( 'Uppercase', 'popped' ), 'uppercase' ] ], recommended( 'metadataCase' ) ),
				onChange: function ( value ) { set( 'metadataCase', value ); }
			} ),
			el( PanelReset, { attrs: props.attrs, keys: typeKeys.concat( [ 'metadataTone', 'metadataCase' ] ), setAttributes: props.setAttributes, recommended: recommended } )
		);
	}

	function MetadataStylePanel( props ) {
		var e = props.effective, set = props.set, recommended = props.recommended;
		var metaKeys = [ 'metadataSize', 'metadataTone', 'metadataCase', 'metadataWeight', 'metadataSeparator' ];
		var hasMetadata = !! ( e( 'showDate' ) || e( 'showCategory' ) || e( 'showTags' ) || e( 'showAuthor' ) );

		return el(
			PanelBody,
			{ title: __( 'Metadata style', 'popped' ), initialOpen: false },
			hasMetadata ? InspectorHint( __( 'Style the date, category, tags and author line without changing which details are shown.', 'popped' ) ) : el( 'p', { className: 'popped-control-help' }, __( 'Turn on date, category, tags or author in Settings → Story details to style metadata.', 'popped' ) ),
			hasMetadata && el( SelectControl, {
				label: __( 'Metadata size', 'popped' ),
				value: e( 'metadataSize' ) || 'small',
				options: recommendedOptions( [ [ __( 'Small', 'popped' ), 'small' ], [ __( 'Medium', 'popped' ), 'medium' ], [ __( 'Large', 'popped' ), 'large' ] ], recommended( 'metadataSize' ) ),
				onChange: function ( value ) { set( 'metadataSize', value ); }
			} ),
			hasMetadata && el( SelectControl, {
				label: __( 'Metadata tone', 'popped' ),
				value: e( 'metadataTone' ) || 'muted',
				options: recommendedOptions( [ [ __( 'Muted', 'popped' ), 'muted' ], [ __( 'Ink', 'popped' ), 'ink' ], [ __( 'Accent', 'popped' ), 'accent' ] ], recommended( 'metadataTone' ) ),
				onChange: function ( value ) { set( 'metadataTone', value ); }
			} ),
			hasMetadata && el( SelectControl, {
				label: __( 'Metadata case', 'popped' ),
				value: e( 'metadataCase' ) || 'uppercase',
				options: recommendedOptions( [ [ __( 'Natural case', 'popped' ), 'normal' ], [ __( 'Uppercase', 'popped' ), 'uppercase' ] ], recommended( 'metadataCase' ) ),
				onChange: function ( value ) { set( 'metadataCase', value ); }
			} ),
			hasMetadata && el( SelectControl, {
				label: __( 'Metadata weight', 'popped' ),
				value: e( 'metadataWeight' ) || 'semibold',
				options: recommendedOptions( [ [ __( 'Regular', 'popped' ), 'regular' ], [ __( 'Semibold', 'popped' ), 'semibold' ], [ __( 'Bold', 'popped' ), 'bold' ] ], recommended( 'metadataWeight' ) ),
				onChange: function ( value ) { set( 'metadataWeight', value ); }
			} ),
			hasMetadata && el( SelectControl, {
				label: __( 'Metadata separator', 'popped' ),
				value: e( 'metadataSeparator' ) || 'dot',
				options: recommendedOptions( [ [ __( 'Dot', 'popped' ), 'dot' ], [ __( 'Slash', 'popped' ), 'slash' ], [ __( 'Bullet', 'popped' ), 'bullet' ], [ __( 'None', 'popped' ), 'none' ] ], recommended( 'metadataSeparator' ) ),
				onChange: function ( value ) { set( 'metadataSeparator', value ); }
			} ),
			el( PanelReset, { attrs: props.attrs, keys: metaKeys, setAttributes: props.setAttributes, recommended: recommended } )
		);
	}

	function ExactStylePanel( props ) {
		var e = props.effective, set = props.set, attrs = props.attrs;
		var exactKeys = [
			'headingFontSize', 'headingFontFamily', 'headingColor',
			'excerptFontSizeExact', 'excerptFontFamily', 'excerptColor',
			'metadataFontSizeExact', 'metadataFontFamily', 'metadataColor',
			'sectionTitleFontSize', 'sectionTitleFontFamily', 'sectionTitleColor',
			'mobileHeadingFontSize', 'mobileExcerptFontSize', 'mobileMetadataFontSize', 'mobileSectionTitleFontSize',
			'mobileColumns'
		];

		function exactToggle( key, fallback ) {
			return el( ToggleControl, {
				label: __( 'Use exact size', 'popped' ),
				checked: attrs[ key ] !== undefined,
				onChange: function ( enabled ) {
					var update = {};
					update[ key ] = enabled ? fallback : undefined;
					props.setAttributes( update );
				}
			} );
		}

		function sizeControl( key, label, min, max, fallback ) {
			return el( Fragment, { key: key },
				exactToggle( key, fallback ),
				attrs[ key ] !== undefined && el( RangeControl, {
					label: label,
					value: attrs[ key ],
					min: min,
					max: max,
					step: 1,
					withInputField: true,
					onChange: function ( value ) { set( key, value ); }
				} )
			);
		}

		return el(
			PanelBody,
			{ title: __( 'Exact type & colours', 'popped' ), initialOpen: false },
			InspectorHint( __( 'Use these only when the designed presets are not exact enough. No custom CSS is required.', 'popped' ) ),
			el( 'hr', { className: 'popped-control-divider' } ),
			el( 'strong', { className: 'popped-control-subhead' }, __( 'Card titles', 'popped' ) ),
			sizeControl( 'headingFontSize', __( 'Title size', 'popped' ), 12, 96, 28 ),
			el( ThemeFontControl, {
				label: __( 'Title font family', 'popped' ),
				value: attrs.headingFontFamily || '',
				onChange: function ( value ) { set( 'headingFontFamily', value ); }
			} ),
			el( ThemeColorControl, {
				label: __( 'Title colour', 'popped' ),
				value: attrs.headingColor,
				onChange: function ( value ) { set( 'headingColor', value ); }
			} ),

			e( 'showExcerpt' ) && el( Fragment, {},
				el( 'hr', { className: 'popped-control-divider' } ),
				el( 'strong', { className: 'popped-control-subhead' }, __( 'Excerpts', 'popped' ) ),
				sizeControl( 'excerptFontSizeExact', __( 'Excerpt size', 'popped' ), 10, 48, 16 ),
				el( ThemeFontControl, {
					label: __( 'Excerpt font family', 'popped' ),
					value: attrs.excerptFontFamily || '',
					onChange: function ( value ) { set( 'excerptFontFamily', value ); }
				} ),
				el( ThemeColorControl, {
					label: __( 'Excerpt colour', 'popped' ),
					value: attrs.excerptColor,
					onChange: function ( value ) { set( 'excerptColor', value ); }
				} )
			),

			el( 'hr', { className: 'popped-control-divider' } ),
			el( 'strong', { className: 'popped-control-subhead' }, __( 'Metadata', 'popped' ) ),
			sizeControl( 'metadataFontSizeExact', __( 'Metadata size', 'popped' ), 9, 32, 12 ),
			el( ThemeFontControl, {
				label: __( 'Metadata font family', 'popped' ),
				value: attrs.metadataFontFamily || '',
				onChange: function ( value ) { set( 'metadataFontFamily', value ); }
			} ),
			el( ThemeColorControl, {
				label: __( 'Metadata colour', 'popped' ),
				value: attrs.metadataColor,
				onChange: function ( value ) { set( 'metadataColor', value ); }
			} ),

			el( 'hr', { className: 'popped-control-divider' } ),
			el( 'strong', { className: 'popped-control-subhead' }, __( 'Section heading', 'popped' ) ),
			sizeControl( 'sectionTitleFontSize', __( 'Section heading size', 'popped' ), 14, 96, 32 ),
			el( ThemeFontControl, {
				label: __( 'Section heading font family', 'popped' ),
				value: attrs.sectionTitleFontFamily || '',
				onChange: function ( value ) { set( 'sectionTitleFontFamily', value ); }
			} ),
			el( ThemeColorControl, {
				label: __( 'Section heading colour', 'popped' ),
				value: attrs.sectionTitleColor,
				onChange: function ( value ) { set( 'sectionTitleColor', value ); }
			} ),

			el( PanelBody, { title: __( 'Mobile overrides', 'popped' ), initialOpen: false },
				InspectorHint( __( 'Only set these when the automatic responsive result needs a deliberate exception.', 'popped' ) ),
				el( SelectControl, {
					label: __( 'Story columns on mobile', 'popped' ),
					value: attrs.mobileColumns === undefined ? 0 : attrs.mobileColumns,
					options: [
						{ label: __( 'Automatic', 'popped' ), value: 0 },
						{ label: '1', value: 1 },
						{ label: '2', value: 2 }
					],
					onChange: function ( value ) { set( 'mobileColumns', Number( value ) || undefined ); }
				} ),
				sizeControl( 'mobileHeadingFontSize', __( 'Mobile title size', 'popped' ), 12, 72, 24 ),
				sizeControl( 'mobileExcerptFontSize', __( 'Mobile excerpt size', 'popped' ), 10, 40, 15 ),
				sizeControl( 'mobileMetadataFontSize', __( 'Mobile metadata size', 'popped' ), 9, 28, 11 ),
				sizeControl( 'mobileSectionTitleFontSize', __( 'Mobile section heading size', 'popped' ), 14, 72, 25 )
			),
			el( PanelReset, { attrs: props.attrs, keys: exactKeys, setAttributes: props.setAttributes, recommended: function () { return undefined; } } )
		);
	}

	function CardStylePanel( props ) {
		var e = props.effective, set = props.set, recommended = props.recommended;
		var cardKeys = [ 'cardSurface', 'cardBorder', 'cardRadius', 'cardPadding', 'cardGap', 'contentGap', 'itemGap' ];
		var exactState = useState( false );
		var showExact = exactState[ 0 ];
		var setShowExact = exactState[ 1 ];
		var spacingRecipes = {
			compact: { cardPadding: 0, itemGap: 16, cardGap: 10, contentGap: 7 },
			balanced: { cardPadding: 0, itemGap: 24, cardGap: 14, contentGap: 9 },
			airy: { cardPadding: 16, itemGap: 32, cardGap: 20, contentGap: 12 }
		};
		var activeRecipe = 'custom';
		Object.keys( spacingRecipes ).some( function ( name ) {
			var values = spacingRecipes[ name ];
			var matches = Object.keys( values ).every( function ( key ) { return Number( e( key ) || 0 ) === values[ key ]; } );
			if ( matches ) { activeRecipe = name; }
			return matches;
		} );

		return el(
			PanelBody,
			{ title: __( 'Fine tune', 'popped' ), initialOpen: false },
			InspectorHint( __( 'Optional controls for card treatment and exact spacing. Most blocks should not need these.', 'popped' ) ),
			el( SelectControl, {
				label: __( 'Card surface', 'popped' ),
				value: e( 'cardSurface' ) || 'transparent',
				options: recommendedOptions( [ [ __( 'Transparent', 'popped' ), 'transparent' ], [ __( 'Soft', 'popped' ), 'soft' ], [ __( 'Paper', 'popped' ), 'paper' ] ], recommended( 'cardSurface' ) ),
				onChange: function ( value ) { set( 'cardSurface', value ); }
			} ),
			el( SelectControl, {
				label: __( 'Card border', 'popped' ),
				value: e( 'cardBorder' ) || 'none',
				options: recommendedOptions( [ [ __( 'None', 'popped' ), 'none' ], [ __( 'Hairline', 'popped' ), 'hairline' ], [ __( 'Strong', 'popped' ), 'strong' ] ], recommended( 'cardBorder' ) ),
				onChange: function ( value ) { set( 'cardBorder', value ); }
			} ),
			el( SelectControl, {
				label: __( 'Card corners', 'popped' ),
				value: e( 'cardRadius' ) || 'square',
				options: recommendedOptions( [ [ __( 'Square', 'popped' ), 'square' ], [ __( 'Soft', 'popped' ), 'soft' ], [ __( 'Rounded', 'popped' ), 'rounded' ] ], recommended( 'cardRadius' ) ),
				onChange: function ( value ) { set( 'cardRadius', value ); }
			} ),
			el( SelectControl, {
				label: __( 'Spacing recipe', 'popped' ),
				help: __( 'A fast starting point for card padding and internal gaps.', 'popped' ),
				value: activeRecipe,
				options: options( [
					[ __( 'Custom', 'popped' ), 'custom' ],
					[ __( 'Compact', 'popped' ), 'compact' ],
					[ __( 'Balanced', 'popped' ), 'balanced' ],
					[ __( 'Airy', 'popped' ), 'airy' ]
				] ),
				onChange: function ( value ) {
					if ( spacingRecipes[ value ] ) { props.setAttributes( spacingRecipes[ value ] ); }
				}
			} ),
			el( ToggleControl, {
				label: __( 'Show exact spacing controls', 'popped' ),
				checked: showExact,
				onChange: setShowExact
			} ),
			showExact && el( Fragment, {},
				el( RangeControl, {
					label: __( 'Card padding', 'popped' ),
					value: e( 'cardPadding' ) || 0,
					min: 0, max: 48, step: 4, allowReset: true,
					resetFallbackValue: recommended( 'cardPadding' ) || 0,
					withInputField: true,
					onChange: function ( value ) { set( 'cardPadding', value ); }
				} ),
				el( RangeControl, {
					label: __( 'Gap between cards', 'popped' ),
					value: e( 'itemGap' ) || 24,
					min: 8, max: 64, step: 4, allowReset: true,
					resetFallbackValue: recommended( 'itemGap' ) || 24,
					withInputField: true,
					onChange: function ( value ) { set( 'itemGap', value ); }
				} ),
				el( RangeControl, {
					label: __( 'Image to text gap', 'popped' ),
					value: e( 'cardGap' ) || 16,
					min: 0, max: 48, step: 2, allowReset: true,
					resetFallbackValue: recommended( 'cardGap' ) || 16,
					withInputField: true,
					onChange: function ( value ) { set( 'cardGap', value ); }
				} ),
				el( RangeControl, {
					label: __( 'Text rhythm', 'popped' ),
					value: e( 'contentGap' ) || 10,
					min: 4, max: 32, step: 2, allowReset: true,
					resetFallbackValue: recommended( 'contentGap' ) || 10,
					withInputField: true,
					onChange: function ( value ) { set( 'contentGap', value ); }
				} )
			),
			el( PanelReset, { attrs: props.attrs, keys: cardKeys, setAttributes: props.setAttributes, recommended: recommended } )
		);
	}

	function AdvancedPanel( props ) {
		var canExclude = has( [ 'timeline', 'horizontal-timeline', 'mini-timeline', 'latest-stories', 'archive-explorer', 'on-this-day', 'also-on-this-day' ], props.slug );
		if ( ! canExclude ) { return null; }
		return el(
			PanelBody,
			{ title: __( 'Exclusions', 'popped' ), initialOpen: false },
			InspectorHint( __( 'Remove individual stories after the main source and taxonomy filters have been applied.', 'popped' ) ),
			el( PostPicker, {
				label: __( 'Exclude stories', 'popped' ),
				value: props.attrs.excludePosts || [],
				onChange: function ( value ) { props.set( 'excludePosts', value ); },
				reorder: false
			} )
		);
	}

	function AppearancePanel( props ) {
		var appearanceKeys = [
			'density', 'showImage', 'showCollectionImage', 'imageRatio', 'imageFit', 'imagePosition', 'radius',
			'headingSize', 'headingWeight', 'headingLineHeight', 'excerptSize',
			'metadataSize', 'metadataTone', 'metadataCase', 'metadataWeight', 'metadataSeparator',
			'contentAlign', 'sectionTitleAlign', 'headingFontSize', 'headingFontFamily', 'headingColor', 'excerptFontSizeExact', 'excerptFontFamily', 'excerptColor', 'metadataFontSizeExact', 'metadataFontFamily', 'metadataColor', 'sectionTitleFontSize', 'sectionTitleFontFamily', 'sectionTitleColor', 'mobileColumns', 'mobileHeadingFontSize', 'mobileExcerptFontSize', 'mobileMetadataFontSize', 'mobileSectionTitleFontSize',
			'cardSurface', 'cardBorder', 'cardRadius', 'cardPadding', 'cardGap', 'contentGap', 'itemGap'
		];
		var inherited = ! hasOverride( props.attrs, appearanceKeys );
		var customized = hasCustomOverride( props.attrs, appearanceKeys, props.recommended );
		var recommendedApplied = ! inherited && ! customized;
		var statusText = inherited
			? __( 'Using inherited Popped defaults', 'popped' )
			: ( recommendedApplied ? __( 'Using recommended Popped defaults', 'popped' ) : __( 'Custom Popped styles', 'popped' ) );
		var statusClass = inherited ? 'is-inherited' : ( recommendedApplied ? 'is-recommended' : 'is-custom' );
		var recipes = [
			{
				name: 'editorial', label: __( 'Editorial', 'popped' ), note: __( 'Balanced default for most sections', 'popped' ),
				values: { density: 'standard', imageRatio: 'classic', imageFit: 'cover', imagePosition: 'center', headingSize: 'medium', headingWeight: 'semibold', headingLineHeight: 'balanced', excerptSize: 'medium', metadataSize: 'small', metadataTone: 'muted', metadataCase: 'normal', metadataWeight: 'semibold', metadataSeparator: 'dot', cardSurface: 'transparent', cardBorder: 'none', cardRadius: 'soft', cardPadding: 0, cardGap: 12, contentGap: 8, itemGap: 18 }
			},
			{
				name: 'compact', label: __( 'Compact', 'popped' ), note: __( 'More stories with less scrolling', 'popped' ),
				values: { density: 'compact', imageRatio: 'wide', imageFit: 'cover', imagePosition: 'center', headingSize: 'small', headingWeight: 'semibold', headingLineHeight: 'balanced', excerptSize: 'small', metadataSize: 'small', metadataTone: 'muted', metadataCase: 'normal', metadataWeight: 'semibold', metadataSeparator: 'dot', cardSurface: 'transparent', cardBorder: 'none', cardRadius: 'soft', cardPadding: 0, cardGap: 10, contentGap: 6, itemGap: 12 }
			},
			{
				name: 'minimal', label: __( 'Minimal', 'popped' ), note: __( 'Quiet, text-led and border-free', 'popped' ),
				values: { density: 'compact', imageRatio: 'classic', imageFit: 'cover', imagePosition: 'center', headingSize: 'medium', headingWeight: 'medium', headingLineHeight: 'balanced', excerptSize: 'small', metadataSize: 'small', metadataTone: 'muted', metadataCase: 'normal', metadataWeight: 'regular', metadataSeparator: 'dot', cardSurface: 'transparent', cardBorder: 'none', cardRadius: 'square', cardPadding: 0, cardGap: 10, contentGap: 6, itemGap: 14 }
			},
			{
				name: 'cards', label: __( 'Cards', 'popped' ), note: __( 'Contained cards without heavy framing', 'popped' ),
				values: { density: 'standard', imageRatio: 'classic', imageFit: 'cover', imagePosition: 'center', headingSize: 'small', headingWeight: 'semibold', headingLineHeight: 'balanced', excerptSize: 'small', metadataSize: 'small', metadataTone: 'muted', metadataCase: 'normal', metadataWeight: 'semibold', metadataSeparator: 'dot', cardSurface: 'soft', cardBorder: 'none', cardRadius: 'soft', cardPadding: 14, cardGap: 10, contentGap: 7, itemGap: 16 }
			}
		];

		function applyRecipe( recipe ) {
			var update = Object.assign( {}, recipe.values );
			if ( props.slug === 'on-this-day' ) {
				update.featureSize = recipe.name === 'feature' ? 'standard' : 'compact';
			}
			props.setAttributes( update );
		}

		return el(
			PanelBody,
			{ title: __( 'Design', 'popped' ), initialOpen: true },
			InspectorHint( __( 'Pick a designed starting point. Popped keeps it responsive automatically; use the panels below only when you want to fine-tune it.', 'popped' ) ),
			el( SelectControl, {
				label: __( 'Density', 'popped' ),
				value: props.effective( 'density' ) || 'inherit',
				options: recommendedOptions( [ [ __( 'Use site setting', 'popped' ), 'inherit' ], [ __( 'Compact', 'popped' ), 'compact' ], [ __( 'Standard', 'popped' ), 'standard' ], [ __( 'Spacious', 'popped' ), 'spacious' ] ], props.recommended( 'density' ) ),
				onChange: function ( value ) { props.set( 'density', value ); }
			} ),
			el( SelectControl, {
				label: __( 'Story text alignment', 'popped' ),
				value: props.effective( 'contentAlign' ) || 'left',
				options: options( [ [ __( 'Left', 'popped' ), 'left' ], [ __( 'Centre', 'popped' ), 'center' ], [ __( 'Right', 'popped' ), 'right' ] ] ),
				onChange: function ( value ) { props.set( 'contentAlign', value ); }
			} ),
			el( SelectControl, {
				label: __( 'Section heading alignment', 'popped' ),
				value: props.effective( 'sectionTitleAlign' ) || 'left',
				options: options( [ [ __( 'Left', 'popped' ), 'left' ], [ __( 'Centre', 'popped' ), 'center' ], [ __( 'Right', 'popped' ), 'right' ] ] ),
				onChange: function ( value ) { props.set( 'sectionTitleAlign', value ); }
			} ),
			el( HeadingLevelControl, {
				label: __( 'Story title HTML level', 'popped' ),
				value: props.effective( 'headingLevel' ),
				fallback: 3,
				onChange: function ( value ) { props.set( 'headingLevel', value ); }
			} ),
			el( HeadingLevelControl, {
				label: __( 'Section heading HTML level', 'popped' ),
				value: props.effective( 'sectionTitleLevel' ),
				fallback: 2,
				onChange: function ( value ) { props.set( 'sectionTitleLevel', value ); }
			} ),
			el( 'div', { className: 'popped-quick-style-grid' }, recipes.map( function ( recipe ) {
				return el( Button, {
					key: recipe.name,
					variant: 'tertiary',
					onClick: function () { applyRecipe( recipe ); }
				}, el( 'strong', {}, recipe.label ), el( 'span', {}, recipe.note ) );
			} ) ),
			el( 'div', { className: 'popped-appearance-status' },
				el( 'span', { className: statusClass }, statusText )
			),
			el( Button, {
				variant: 'secondary',
				icon: 'yes-alt',
				disabled: recommendedApplied,
				onClick: function () { setRecommended( props.setAttributes, appearanceKeys, props.recommended ); }
			}, inherited ? __( 'Apply recommended appearance', 'popped' ) : __( 'Restore recommended appearance', 'popped' ) ),
			! inherited && el( Button, {
				variant: 'tertiary',
				onClick: function () { resetKeys( props.setAttributes, appearanceKeys ); }
			}, __( 'Use inherited defaults', 'popped' ) )
		);
	}

	function UtilityStylePanel( props ) {
		var e = props.effective, set = props.set, attrs = props.attrs;
		var keys = [
			'utilityAlign', 'utilityFontSize', 'utilitySecondaryFontSize', 'mobileUtilityFontSize', 'mobileUtilitySecondaryFontSize',
			'utilityColor', 'utilitySecondaryColor', 'utilityAccentColor', 'utilityGap',
			'sectionTitleAlign', 'sectionTitleFontSize', 'sectionTitleColor'
		];
		function optionalSize( key, label, min, max, fallback ) {
			return el( Fragment, { key: key },
				el( ToggleControl, {
					label: __( 'Use exact ' + label.toLowerCase(), 'popped' ),
					checked: attrs[ key ] !== undefined,
					onChange: function ( enabled ) { var update = {}; update[ key ] = enabled ? fallback : undefined; props.setAttributes( update ); }
				} ),
				attrs[ key ] !== undefined && el( RangeControl, {
					label: label, value: attrs[ key ], min: min, max: max, step: 1, withInputField: true,
					onChange: function ( value ) { set( key, value ); }
				} )
			);
		}
		return el(
			PanelBody,
			{ title: __( 'Design', 'popped' ), initialOpen: true },
			InspectorHint( __( 'The useful visual controls for this block live here. Leave anything unset to inherit Popped’s design.', 'popped' ) ),
			el( SelectControl, {
				label: __( 'Alignment', 'popped' ),
				value: e( 'utilityAlign' ) || 'left',
				options: options( [ [ __( 'Left', 'popped' ), 'left' ], [ __( 'Centre', 'popped' ), 'center' ], [ __( 'Right', 'popped' ), 'right' ] ] ),
				onChange: function ( value ) { set( 'utilityAlign', value ); }
			} ),
			optionalSize( 'utilityFontSize', __( 'Primary text size', 'popped' ), 10, 72, props.slug === 'news-ticker' ? 13 : 18 ),
			optionalSize( 'utilitySecondaryFontSize', __( 'Secondary text size', 'popped' ), 9, 36, 12 ),
			el( ThemeColorControl, {
				label: __( 'Primary text colour', 'popped' ), value: attrs.utilityColor,
				onChange: function ( value ) { set( 'utilityColor', value ); }
			} ),
			el( ThemeColorControl, {
				label: __( 'Secondary text colour', 'popped' ), value: attrs.utilitySecondaryColor,
				onChange: function ( value ) { set( 'utilitySecondaryColor', value ); }
			} ),
			el( ThemeColorControl, {
				label: __( 'Accent colour', 'popped' ), value: attrs.utilityAccentColor,
				onChange: function ( value ) { set( 'utilityAccentColor', value ); }
			} ),
			el( RangeControl, {
				label: __( 'Element gap', 'popped' ), value: e( 'utilityGap' ) || 8, min: 0, max: 48, step: 2, withInputField: true,
				onChange: function ( value ) { set( 'utilityGap', value ); }
			} ),
			has( [ 'year-navigator', 'timeline-navigation' ], props.slug ) && el( Fragment, {},
				el( 'hr', { className: 'popped-control-divider' } ),
				el( 'strong', { className: 'popped-control-subhead' }, __( 'Section heading', 'popped' ) ),
				el( SelectControl, {
					label: __( 'Heading alignment', 'popped' ), value: e( 'sectionTitleAlign' ) || 'left',
					options: options( [ [ __( 'Left', 'popped' ), 'left' ], [ __( 'Centre', 'popped' ), 'center' ], [ __( 'Right', 'popped' ), 'right' ] ] ),
					onChange: function ( value ) { set( 'sectionTitleAlign', value ); }
				} ),
				el( HeadingLevelControl, {
					label: __( 'Heading HTML level', 'popped' ),
					value: e( 'sectionTitleLevel' ),
					fallback: 2,
					onChange: function ( value ) { set( 'sectionTitleLevel', value ); }
				} ),
				optionalSize( 'sectionTitleFontSize', __( 'Heading size', 'popped' ), 14, 72, 28 ),
				el( ThemeColorControl, {
					label: __( 'Heading colour', 'popped' ), value: attrs.sectionTitleColor,
					onChange: function ( value ) { set( 'sectionTitleColor', value ); }
				} )
			),
			el( PanelBody, { title: __( 'Mobile overrides', 'popped' ), initialOpen: false },
				InspectorHint( __( 'Only set these when the automatic mobile result needs a deliberate exception.', 'popped' ) ),
				optionalSize( 'mobileUtilityFontSize', __( 'Mobile primary size', 'popped' ), 10, 56, props.slug === 'news-ticker' ? 12 : 16 ),
				optionalSize( 'mobileUtilitySecondaryFontSize', __( 'Mobile secondary size', 'popped' ), 9, 30, 11 )
			),
			el( PanelReset, { attrs: props.attrs, keys: keys, setAttributes: props.setAttributes, recommended: function () { return undefined; } } )
		);
	}

	function QuickToolbar( props ) {
		var isStory = has( [ 'timeline', 'horizontal-timeline', 'mini-timeline', 'on-this-day', 'also-on-this-day', 'related-stories', 'latest-stories', 'archive-explorer', 'featured-collection', 'search' ], props.slug );
		var isUtility = has( [ 'news-ticker', 'year-navigator', 'continue-story', 'timeline-navigation' ], props.slug );
		if ( ! isStory && ! isUtility ) { return null; }
		var key = isStory ? 'contentAlign' : 'utilityAlign';
		var current = props.effective( key ) || 'left';
		var labels = { left: __( 'Align left', 'popped' ), center: __( 'Align centre', 'popped' ), right: __( 'Align right', 'popped' ) };
		return el(
			BlockControls,
			{},
			el( ToolbarGroup, {},
				[ 'left', 'center', 'right' ].map( function ( value ) {
					return el( ToolbarButton, {
						key: value,
						icon: value === 'left' ? 'editor-alignleft' : ( value === 'center' ? 'editor-aligncenter' : 'editor-alignright' ),
						label: labels[ value ],
						isPressed: current === value,
						onClick: function () { props.set( key, value ); }
					} );
				} )
			)
		);
	}

	function BlockInfoPanel( props ) {
		var note = props.slug === 'homepage'
			? __( 'This block renders the homepage sections configured in Popped → Components. Use the Styles tab for outer block colour, spacing, typography and borders.', 'popped' )
			: __( 'This block is driven by the current post and Popped’s global settings. Use the Styles tab for outer block appearance.', 'popped' );
		return el(
			PanelBody,
			{ title: __( 'Popped block', 'popped' ), initialOpen: true },
			el( 'p', { className: 'popped-inspector-intro' }, props.definition.description ),
			el( 'p', { className: 'popped-control-help' }, note )
		);
	}

	function EmptyPreview() { return el( Notice, { status: 'info', isDismissible: false }, el( 'strong', {}, __( 'No stories match this selection.', 'popped' ) ), el( 'p', {}, __( 'Change the source or filters in the block sidebar.', 'popped' ) ) ); }
	function ErrorPreview() { return el( Notice, { status: 'error', isDismissible: false }, __( 'Popped could not update this preview. Your settings are safe; try again.', 'popped' ) ); }

	Object.keys( config.definitions ).forEach( function ( slug ) {
		var definition = config.definitions[ slug ];
		var metadata = config.metadata && config.metadata[ slug ] ? config.metadata[ slug ] : {};
		wp.blocks.registerBlockType( 'popped/' + slug, {
			title: definition.title,
			description: definition.description,
			category: 'popped',
			icon: definition.icon,
			apiVersion: metadata.apiVersion || 3,
			attributes: metadata.attributes || {},
			supports: metadata.supports || { html: false },
			styles: ( definition.styles || [] ).map( function ( style, index ) {
				return {
					name: style,
					label: styleLabel( slug, style ),
					isDefault: index === 0
				};
			} ),
			edit: function ( props ) {
				var attrs = props.attributes;
				var defaults = config.defaults[ slug ] || {};
				var insertionDefaults = config.insertionDefaults && config.insertionDefaults[ slug ] ? config.insertionDefaults[ slug ] : {};

				function effective( key ) { return attrs[ key ] !== undefined ? attrs[ key ] : defaults[ key ]; }
				function recommended( key ) { return insertionDefaults[ key ] !== undefined ? insertionDefaults[ key ] : defaults[ key ]; }
				function set( key, value ) {
					var update = {};
					update[ key ] = value;
					props.setAttributes( update );
				}

				var shared = {
					slug: slug,
					attrs: attrs,
					effective: effective,
					recommended: recommended,
					set: set,
					setAttributes: props.setAttributes,
					definition: definition
				};
				var previewAttributes = Object.assign( {}, attrs );
				[ 'style', 'backgroundColor', 'textColor', 'gradient', 'fontSize', 'fontFamily', 'borderColor' ].forEach( function ( key ) {
					delete previewAttributes[ key ];
				} );

				var settingsInspector = [];
				var stylesInspector = [];
				var hasContentPanels = definition.panels && definition.panels.some( function ( panel ) { return has( [ 'content', 'layout', 'metadata' ], panel ); } );

				if ( has( definition.panels, 'content' ) ) {
					settingsInspector.push( el( ContentPanel, Object.assign( { key: 'content' }, shared ) ) );
				}
				if ( has( definition.panels, 'metadata' ) ) {
					settingsInspector.push( el( StoryDetailsPanel, Object.assign( { key: 'details' }, shared ) ) );
				}
				if ( has( definition.panels, 'layout' ) ) {
					settingsInspector.push( el( LayoutPanel, Object.assign( { key: 'layout' }, shared ) ) );
				}
				if ( has( [ 'timeline', 'horizontal-timeline', 'mini-timeline', 'latest-stories', 'archive-explorer', 'on-this-day', 'also-on-this-day' ], slug ) ) {
					settingsInspector.push( el( AdvancedPanel, Object.assign( { key: 'advanced' }, shared ) ) );
				}
				if ( ! hasContentPanels ) {
					settingsInspector.push( el( BlockInfoPanel, Object.assign( { key: 'info' }, shared ) ) );
				}

				if ( has( definition.panels, 'images' ) || has( definition.panels, 'metadata' ) ) {
					stylesInspector.push( el( AppearancePanel, Object.assign( { key: 'appearance' }, shared ) ) );
				}
				if ( has( definition.panels, 'images' ) ) {
					stylesInspector.push( el( ImagesPanel, Object.assign( { key: 'images' }, shared ) ) );
				}
				if ( has( definition.panels, 'utility' ) ) {
					stylesInspector.push( el( UtilityStylePanel, Object.assign( { key: 'utility-style' }, shared ) ) );
				}
				if ( has( definition.panels, 'metadata' ) ) {
					stylesInspector.push( el( TypographyPanel, Object.assign( { key: 'typography' }, shared ) ) );
					stylesInspector.push( el( ExactStylePanel, Object.assign( { key: 'exact-style' }, shared ) ) );
					stylesInspector.push( el( CardStylePanel, Object.assign( { key: 'cards' }, shared ) ) );
				}

				return el(
					Fragment,
					{},
					el( QuickToolbar, shared ),
					el( InspectorControls, { group: 'settings' }, settingsInspector ),
					stylesInspector.length > 0 && el( InspectorControls, { group: 'styles' }, stylesInspector ),
					el(
						'div',
						useBlockProps( { className: 'popped-editor-preview' } ),
						ServerSideRender
							? el( ServerSideRender, {
								block: 'popped/' + slug,
								attributes: previewAttributes,
								EmptyResponsePlaceholder: EmptyPreview,
								ErrorResponsePlaceholder: ErrorPreview,
								LoadingResponsePlaceholder: function () {
									return el( 'div', { className: 'popped-preview-loading', role: 'status' }, el( Spinner ), __( 'Updating preview…', 'popped' ) );
								}
							} )
							: el( ErrorPreview )
					)
				);
			},
			save: function () { return null; }
		} );

		var defaultVariation = config.insertionDefaults && config.insertionDefaults[ slug ] ? config.insertionDefaults[ slug ] : {};
		if ( wp.blocks.registerBlockVariation && Object.keys( defaultVariation ).length ) {
			wp.blocks.registerBlockVariation( 'popped/' + slug, {
				name: 'popped-recommended',
				title: definition.title,
				description: __( 'Popped recommended starting point.', 'popped' ),
				attributes: defaultVariation,
				isDefault: true,
				scope: [ 'inserter' ]
			} );
		}
	} );
} )( window.wp );
