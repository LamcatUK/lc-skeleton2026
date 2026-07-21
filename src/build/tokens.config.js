'use strict';

/**
 * Single source of truth for breakpoints and the utility/grid classes the
 * generator produces. CSS custom properties can't be read inside an @media
 * condition, so breakpoint pixel values live here, not in tokens.css.
 *
 * A couple of hand-authored files (src/css/nav.css) hardcode the "lg" value
 * in a plain @media query for the same reason — keep them in sync with this
 * file if a breakpoint changes.
 */

// Empty-string key = no media query (mobile-first base). Order matters —
// generated in this order, later ones win on equal specificity.
const breakpoints = {
	'': null,
	sm: 576,
	md: 768,
	lg: 992,
	xl: 1200,
	xxl: 1400,
};

const gridColumns = 12;

// property: [className prefix, CSS property, { suffix: value } ]
const utilities = {
	display: {
		prop: 'display',
		values: { block: 'block', flex: 'flex', 'inline-flex': 'inline-flex', grid: 'grid', none: 'none' },
	},
	'flex-direction': {
		prop: 'flex-direction',
		values: { row: 'row', column: 'column' },
	},
	'justify-content': {
		prop: 'justify-content',
		values: { start: 'flex-start', end: 'flex-end', center: 'center', between: 'space-between' },
	},
	'align-items': {
		prop: 'align-items',
		values: { start: 'flex-start', end: 'flex-end', center: 'center' },
	},
	'text-align': {
		className: 'text',
		prop: 'text-align',
		values: { start: 'left', center: 'center', end: 'right' },
	},
};

// Spacing utilities (gap, margin, padding) driven off the tokens.css spacing scale.
const spacingScale = [0, 1, 2, 3, 4, 5];

module.exports = { breakpoints, gridColumns, utilities, spacingScale };
