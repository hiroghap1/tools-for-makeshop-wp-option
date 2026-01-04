(function() {
	const { registerBlockType } = wp.blocks;
	const { TextareaControl, PanelBody, RangeControl, Button } = wp.components;
	const { InspectorControls } = wp.blockEditor;
	const { Fragment } = wp.element;
	const { __ } = wp.i18n;
	const { serverSideRender: ServerSideRender } = wp;

	registerBlockType('tfmwp/product-display', {
		title: __('makeshop Product Display', 'tools-for-makeshop-wp-option'),
		description: __('Display makeshop products in a grid layout', 'tools-for-makeshop-wp-option'),
		icon: 'products',
		category: 'widgets',
		keywords: [
			__('makeshop', 'tools-for-makeshop-wp-option'),
			__('product', 'tools-for-makeshop-wp-option'),
			__('shop', 'tools-for-makeshop-wp-option')
		],

		attributes: {
			productUrls: {
				type: 'string',
				default: ''
			},
			columnsDesktop: {
				type: 'number',
				default: 4
			},
			columnsTablet: {
				type: 'number',
				default: 3
			},
			columnsMobile: {
				type: 'number',
				default: 2
			}
		},

		edit: function(props) {
			const { attributes, setAttributes } = props;
			const { productUrls, columnsDesktop, columnsTablet, columnsMobile } = attributes;

			// Handle refresh cache
			const handleRefresh = function() {
				// Force re-render by updating a timestamp or similar
				// This will trigger a new server-side render
				const currentUrls = productUrls;
				setAttributes({ productUrls: '' });
				setTimeout(function() {
					setAttributes({ productUrls: currentUrls });
				}, 100);
			};

			return (
				<Fragment>
					<InspectorControls>
						<PanelBody title={__('Product URLs', 'tools-for-makeshop-wp-option')}>
							<TextareaControl
								label={__('Product URLs (one per line)', 'tools-for-makeshop-wp-option')}
								help={__('Enter makeshop product URLs, one per line', 'tools-for-makeshop-wp-option')}
								value={productUrls}
								onChange={function(value) {
									setAttributes({ productUrls: value });
								}}
								rows={10}
							/>
						</PanelBody>

						<PanelBody title={__('Grid Settings', 'tools-for-makeshop-wp-option')}>
							<RangeControl
								label={__('Desktop Columns', 'tools-for-makeshop-wp-option')}
								value={columnsDesktop}
								onChange={function(value) {
									setAttributes({ columnsDesktop: value });
								}}
								min={1}
								max={6}
							/>
							<RangeControl
								label={__('Tablet Columns', 'tools-for-makeshop-wp-option')}
								value={columnsTablet}
								onChange={function(value) {
									setAttributes({ columnsTablet: value });
								}}
								min={1}
								max={4}
							/>
							<RangeControl
								label={__('Mobile Columns', 'tools-for-makeshop-wp-option')}
								value={columnsMobile}
								onChange={function(value) {
									setAttributes({ columnsMobile: value });
								}}
								min={1}
								max={3}
							/>
						</PanelBody>

						<PanelBody title={__('Cache Control', 'tools-for-makeshop-wp-option')}>
							<p>{__('Product information is cached for 1 hour. Click the button below to refresh.', 'tools-for-makeshop-wp-option')}</p>
							<Button
								isSecondary
								onClick={handleRefresh}
							>
								{__('Refresh Product Data', 'tools-for-makeshop-wp-option')}
							</Button>
						</PanelBody>
					</InspectorControls>

					<div className="tfmwp-product-display-editor">
						{productUrls ? (
							<ServerSideRender
								block="tfmwp/product-display"
								attributes={attributes}
							/>
						) : (
							<div className="tfmwp-product-placeholder">
								<p>{__('Enter product URLs in the sidebar to display products.', 'tools-for-makeshop-wp-option')}</p>
							</div>
						)}
					</div>
				</Fragment>
			);
		},

		save: function() {
			// Server-side rendering, so return null
			return null;
		}
	});
})();
