
/*
 * Imports
 * -------
 */

/* Polyfills */

import 'core-js/es/object/assign';
import 'core-js/es/array/from';

/* Functions */

import { setElements } from 'Formation/utils/utils';

/* Modules */

import focusRing from 'Formation/objects/form/focus-ring';

/* Classes */

import Table from 'Formation/objects/table/table';
import LoadMore from 'Formation/objects/load/load-more';

/*
 * Variables
 * ---------
 */

let el = {};

let elMeta = [
	{
		prop: 'tri',
		selector: '.o-tri',
		all: true,
		array: true
	},
	{
		prop: 'tables',
		selector: '.o-table',
		all: true,
		array: true
	},
	{
		prop: 'searchTrigger',
		selector: '.c-search-trigger'
	},
	{
		prop: 'cta',
		selector: '.c-cta'
	},
	{
		prop: 'loadMore',
		selector: '.js-load-more'
	},
	{
		prop: 'footer',
		selector: '.fusion-footer'
	}
];

/*
 * DOM loaded
 * ----------
 */

const initialize = () => {

	let ns = 'pma',
		n = window[ns];

    /*
     * Set elements object
     * -------------------
     */

    setElements( elMeta, el );

    /*
     * Custom keyboard focus
     * ---------------------
     */

    focusRing();

	/*
	 * Set heights of triangles
	 * ------------------------
	 */

	if( el.tri ) {
        let resizeTimer;

        const tri = () => {
            el.tri.forEach( ( t ) => {
                t.style.height = `${ t.clientWidth }px`;
            } );
        };

        window.addEventListener( 'resize', () => {
            // throttles resize event
            clearTimeout( resizeTimer );

            resizeTimer = setTimeout( () => {
                tri();
            }, 100 );
        } );

        tri();
	}

	/*
	 * Set up table for collapsing
	 * ---------------------------
	 */

	 if( el.tables ) {
		 el.tables.forEach( ( table ) => {
			 if( !table.hasAttribute( 'data-collapse' ) )
			 	return;

			new Table( {
				table: table,
				equalWidthTo: table.parentElement
			} );
		 } );
	 }

 	/*
  	 * Toggle search input
  	 * -------------------
  	 */

 	 if( el.searchTrigger ) {
		 let search = document.getElementById( el.searchTrigger.getAttribute( 'aria-controls' ) ),
			 searchInput = search.querySelector( '.c-search__input' ),
		 	 open = false;

		 if( search ) {
			 el.searchTrigger.addEventListener( 'click', () => {
				 open = !open;

				 search.setAttribute( 'data-open', open );
				 el.searchTrigger.setAttribute( 'aria-expanded', open );

				 if( open ) {
					 searchInput.focus();
				 } else {
					 searchInput.value = '';
				 }
			 } );
		 }
 	 }

	/*
   	 * Load more posts
   	 * ---------------
   	 */

 	if( el.loadMore ) {
 		let button = el.loadMore,
 			loader = button.querySelector( '.o-loader' ),
 			type = button.getAttribute( 'data-type' ),
 			offset = parseInt( button.getAttribute( 'data-posts-per-page' ) ),
 			total = parseInt( button.getAttribute( 'data-total' ) ),
 			insertSelector = button.getAttribute( 'data-insert-selector' ),
 			noResults = Array.from( document.querySelectorAll( '.js-no-results' ) ),
			filtersForm = document.querySelector( '.js-load-more-filters' ),
 			filterItems = Array.from( document.querySelectorAll( '.js-load-more-filter' ) ),
 			filtersLoader = document.querySelector( '.js-load-more-filters-loader' ),
 			filters = [];

		// prevent enter from submitting form
		if( filtersForm )
			filtersForm.addEventListener( 'submit', ( e ) => {
				e.preventDefault();
			} );

 		/* Filters */

 		filterItems.forEach( ( f ) => {
 			let ff = f,
 				type = f.tagName.toLowerCase();

 			if( type == 'input' )
 				type = f.type;

 			filters.push( {
 				item: ff,
 				type: type
 			} );
 		} );

 		/* Data */

		let data = {
			action: 'pma_ajax_get_posts'
		};

		if( window.hasOwnProperty( ns + '_load_posts_query' ) )
			data['query_args'] = window[ns + '_load_posts_query'];

		if( window.hasOwnProperty( ns + '_load_posts_query_static' ) )
			data['query_args_static'] = window[ns + '_load_posts_query_static'];

         /* Arguments */

 		let args = {
 			url: n.ajax_url,
 			data: data,
 			button: button,
 			buttonContainer: button.parentElement,
 			loader: loader,
 			type: type,
 			offset: offset,
 			total: total,
 			filters: filters,
 			filtersLoader: filtersLoader,
 			insertInto: document.querySelector( insertSelector )
 		};

 		if( button.hasAttribute( 'data-ajax-posts-per-page' ) )
 			args['ajaxPpp'] = parseInt( button.getAttribute( 'data-ajax-posts-per-page' ) );

 		if( noResults ) {
 			let containers = [],
 				buttons = [];

 			noResults.forEach( ( n ) => {
 				let b = n.querySelector( '.js-no-results__button' );

 				if( b )
 					buttons.push( b );

 				containers.push( n );
 			} );

 			args['noResults'] = {
 				containers: containers,
 				buttons: buttons
 			};
 		}

 		let lm = new LoadMore( args );
 	}

	/*
 	 * Add margin for cta to footer
 	 * ---------------------------
 	 */

	 if( el.cta && el.footer ) {
		 let resizeTimer;

		 const setMargin = () => {
			 let val = el.cta.clientHeight / 16;
			 el.footer.style.marginBottom = `${ val }rem`;
		 };

		 window.addEventListener( 'resize', () => {
			clearTimeout( resizeTimer );

			resizeTimer = setTimeout( () => {
				setMargin();
			}, 100 );
		 } );

		 setMargin();
	 }

}; // end initialize

document.addEventListener( 'DOMContentLoaded', initialize );
