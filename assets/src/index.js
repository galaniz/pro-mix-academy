
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
	}
];

/*
 * DOM loaded
 * ----------
 */

const initialize = () => {

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

}; // end initialize

document.addEventListener( 'DOMContentLoaded', initialize );
