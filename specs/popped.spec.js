import { expect, test } from '@wordpress/e2e-test-utils-playwright';

const BLOCK_NAMES = [
	'popped/homepage',
	'popped/timeline',
	'popped/horizontal-timeline',
	'popped/mini-timeline',
	'popped/on-this-day',
	'popped/also-on-this-day',
	'popped/continue-story',
	'popped/timeline-navigation',
	'popped/related-stories',
	'popped/news-ticker',
	'popped/latest-stories',
	'popped/archive-explorer',
	'popped/year-navigator',
	'popped/featured-collection',
	'popped/search',
];

test( 'registers the complete Popped block catalogue', async ( { admin, page } ) => {
	await admin.createNewPost();

	const registered = await page.evaluate( ( expected ) => {
		return expected.filter( ( name ) => Boolean( window.wp.blocks.getBlockType( name ) ) );
	}, BLOCK_NAMES );

	expect( registered ).toEqual( BLOCK_NAMES );
} );


test( 'inserts every Popped block without an editor runtime error', async ( {
	admin,
	editor,
	page,
} ) => {
	const pageErrors = [];
	page.on( 'pageerror', ( error ) => pageErrors.push( error.message ) );

	await admin.createNewPost();

	await page.evaluate( ( names ) => {
		const blocks = names.map( ( name ) => window.wp.blocks.createBlock( name ) );
		window.wp.data.dispatch( 'core/block-editor' ).insertBlocks( blocks );
	}, BLOCK_NAMES );

	await expect.poll( async () => {
		const blocks = await editor.getBlocks();
		return blocks.map( ( block ) => block.name );
	} ).toEqual( BLOCK_NAMES );

	await page.waitForTimeout( 1000 );
	expect( pageErrors ).toEqual( [] );
} );

test( 'inserts Year Navigator through the editor and persists its key UX settings', async ( {
	admin,
	editor,
	page,
} ) => {
	await admin.createNewPost();

	await page.getByRole( 'button', { name: 'Block Inserter' } ).click();
	const library = page.getByRole( 'region', { name: 'Block Library' } );
	const search = library.getByRole( 'searchbox' );
	await search.fill( 'Year Navigator' );
	await library.getByRole( 'option', { name: 'Year Navigator', exact: true } ).click();

	await expect.poll( editor.getBlocks ).toMatchObject( [
		{ name: 'popped/year-navigator' },
	] );

	await page.evaluate( () => {
		const clientId = window.wp.data
			.select( 'core/block-editor' )
			.getSelectedBlockClientId();

		window.wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes( clientId, {
			displayLayout: 'scroll',
			maxYears: 12,
			yearOrder: 'newest',
			sectionTitleLevel: 3,
		} );
	} );

	await page.getByRole( 'button', { name: 'Save draft' } ).click();
	await expect( page.getByText( 'Saved' ) ).toBeVisible();
	await page.reload();

	await expect.poll( editor.getBlocks ).toMatchObject( [
		{
			name: 'popped/year-navigator',
			attributes: {
				displayLayout: 'scroll',
				maxYears: 12,
				yearOrder: 'newest',
				sectionTitleLevel: 3,
			},
		},
	] );
} );

test( 'renders long editorial content without page-level horizontal overflow', async ( {
	page,
	requestUtils,
} ) => {
	await requestUtils.createPost( {
		status: 'publish',
		title: 'Popped E2E source story',
		content: '<!-- wp:paragraph --><p>Source story.</p><!-- /wp:paragraph -->',
	} );

	const longTitle =
		'Explore a very long historical archive heading that must remain contained even in narrow layouts';
	const testPage = await requestUtils.createPost( {
		postType: 'page',
		status: 'publish',
		title: 'Popped layout resilience',
		content:
			`<!-- wp:popped/year-navigator {"title":"${ longTitle }","startYear":2010,"endYear":2026,"displayLayout":"scroll","maxYears":12,"yearOrder":"newest"} /-->` +
			`<!-- wp:popped/latest-stories {"title":"${ longTitle }","count":5,"displayLayout":"cards"} /-->`,
	} );

	await page.goto( `?page_id=${ testPage.id }` );

	const overflow = await page.evaluate( () => {
		const root = document.documentElement;
		return root.scrollWidth - root.clientWidth;
	} );
	expect( overflow ).toBeLessThanOrEqual( 1 );

	const yearLinks = page.locator( '.popped-year-list a' );
	await expect( yearLinks ).toHaveCount( 12 );
} );


test( 'delivers design tokens with Popped blocks while leaving the theme shell alone', async ( {
	page,
	requestUtils,
} ) => {
	const testPage = await requestUtils.createPost( {
		postType: 'page',
		status: 'publish',
		title: 'Popped theme ownership',
		content: '<!-- wp:popped/year-navigator {"maxYears":6} /-->',
	} );

	await page.goto( `?page_id=${ testPage.id }` );

	await expect( page.locator( '.popped-block--year-navigator' ) ).toBeVisible();
	const inlineTokens = page.locator( 'style#popped-inline-css' );
	await expect( inlineTokens ).toContainText( '--popped-background' );
	await expect( page.locator( '.popped-site-header, .popped-site-footer' ) ).toHaveCount( 0 );
} );

test( 'keeps the horizontal timeline keyboard-accessible as a named region', async ( {
	page,
	requestUtils,
} ) => {
	for ( let index = 0; index < 3; index += 1 ) {
		await requestUtils.createPost( {
			status: 'publish',
			title: `Timeline source ${ index + 1 }`,
			content: '<!-- wp:paragraph --><p>Timeline story.</p><!-- /wp:paragraph -->',
		} );
	}

	const testPage = await requestUtils.createPost( {
		postType: 'page',
		status: 'publish',
		title: 'Popped rail accessibility',
		content:
			'<!-- wp:popped/horizontal-timeline {"source":"all","count":3,"showNavigation":true} /-->',
	} );

	await page.goto( `?page_id=${ testPage.id }` );

	const region = page.getByRole( 'region', {
		name: 'Scrollable timeline stories',
	} );
	await expect( region ).toBeVisible();
	await expect( region ).toHaveAttribute( 'tabindex', '0' );
} );
