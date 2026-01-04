import { registerBlockType } from '@wordpress/blocks';
import { TextareaControl, PanelBody, RangeControl, Button } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import './editor.scss';
import './style.scss';
import metadata from './block.json';

registerBlockType(metadata.name, {
	...metadata,

	edit: ({ attributes, setAttributes }) => {
		const { productUrls, columnsDesktop, columnsTablet, columnsMobile } = attributes;
		const blockProps = useBlockProps();

		const handleRefresh = () => {
			const currentUrls = productUrls;
			setAttributes({ productUrls: '' });
			setTimeout(() => {
				setAttributes({ productUrls: currentUrls });
			}, 100);
		};

		// Count URLs
		const urlList = productUrls ? productUrls.split('\n').filter(url => url.trim() !== '') : [];
		const urlCount = urlList.length;

		return (
			<>
				<InspectorControls>
					<PanelBody title={__('Product URLs', 'tools-for-makeshop-wp-option')}>
						<TextareaControl
							label={__('Product URLs (one per line)', 'tools-for-makeshop-wp-option')}
							help={__('Enter makeshop product URLs, one per line', 'tools-for-makeshop-wp-option')}
							value={productUrls}
							onChange={(value) => setAttributes({ productUrls: value })}
							rows={10}
						/>
					</PanelBody>

					<PanelBody title={__('Grid Settings', 'tools-for-makeshop-wp-option')}>
						<RangeControl
							label={__('Desktop Columns', 'tools-for-makeshop-wp-option')}
							value={columnsDesktop}
							onChange={(value) => setAttributes({ columnsDesktop: value })}
							min={1}
							max={6}
						/>
						<RangeControl
							label={__('Tablet Columns', 'tools-for-makeshop-wp-option')}
							value={columnsTablet}
							onChange={(value) => setAttributes({ columnsTablet: value })}
							min={1}
							max={4}
						/>
						<RangeControl
							label={__('Mobile Columns', 'tools-for-makeshop-wp-option')}
							value={columnsMobile}
							onChange={(value) => setAttributes({ columnsMobile: value })}
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

				<div {...blockProps}>
					<div className="tfmwp-product-display-editor">
						{productUrls ? (
							<div className="tfmwp-product-preview">
								<div className="tfmwp-preview-header">
									<span className="dashicons dashicons-products"></span>
									<strong>{__('makeshop Product Display', 'tools-for-makeshop-wp-option')}</strong>
								</div>
								<div className="tfmwp-preview-info">
									<p>
										{urlCount === 1
											? __('1 product will be displayed', 'tools-for-makeshop-wp-option')
											: `${urlCount} ${__('products will be displayed', 'tools-for-makeshop-wp-option')}`
										}
									</p>
									<p className="tfmwp-preview-grid-info">
										{__('Grid:', 'tools-for-makeshop-wp-option')}
										{' '}
										{__('Desktop', 'tools-for-makeshop-wp-option')}: {columnsDesktop},
										{' '}
										{__('Tablet', 'tools-for-makeshop-wp-option')}: {columnsTablet},
										{' '}
										{__('Mobile', 'tools-for-makeshop-wp-option')}: {columnsMobile}
									</p>
									<p className="tfmwp-preview-note">
										{__('Products will be fetched and displayed on the frontend.', 'tools-for-makeshop-wp-option')}
									</p>
								</div>
							</div>
						) : (
							<div className="tfmwp-product-placeholder">
								<span className="dashicons dashicons-products"></span>
								<p>{__('Enter product URLs in the sidebar to display products.', 'tools-for-makeshop-wp-option')}</p>
							</div>
						)}
					</div>
				</div>
			</>
		);
	},

	save: () => {
		return null;
	}
});
