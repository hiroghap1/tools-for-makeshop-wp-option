import { registerBlockType } from '@wordpress/blocks';
import { TextareaControl, PanelBody, RangeControl, Button, Spinner } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import './editor.scss';
import './style.scss';
import metadata from './block.json';

registerBlockType(metadata.name, {
	...metadata,

	edit: ({ attributes, setAttributes }) => {
		const { productUrls, productsData, lastFetched, columnsDesktop, columnsTablet, columnsMobile } = attributes;
		const blockProps = useBlockProps();
		const [isLoading, setIsLoading] = useState(false);
		const [fetchError, setFetchError] = useState(null);

		// Fetch products when URLs change
		useEffect(() => {
			const urlList = productUrls ? productUrls.split('\n').filter(url => url.trim() !== '') : [];

			if (urlList.length === 0) {
				setAttributes({ productsData: [], lastFetched: 0 });
				return;
			}

			// Debounce: wait 1 second after user stops typing
			const timeoutId = setTimeout(() => {
				fetchProducts(urlList);
			}, 1000);

			return () => clearTimeout(timeoutId);
		}, [productUrls]);

		const fetchProducts = async (urls) => {
			setIsLoading(true);
			setFetchError(null);

			try {
				const response = await apiFetch({
					path: '/tfmwp/v1/fetch-products',
					method: 'POST',
					data: { urls },
				});

				setAttributes({
					productsData: response.products,
					lastFetched: response.lastFetched,
				});
			} catch (error) {
				setFetchError(error.message);
				console.error('Failed to fetch products:', error);
			} finally {
				setIsLoading(false);
			}
		};

		const handleRefresh = () => {
			const urlList = productUrls ? productUrls.split('\n').filter(url => url.trim() !== '') : [];
			if (urlList.length > 0) {
				fetchProducts(urlList);
			}
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
						<p>{__('Click the button below to refresh product data.', 'tools-for-makeshop-wp-option')}</p>
						<Button
							variant="secondary"
							onClick={handleRefresh}
							disabled={isLoading || urlCount === 0}
						>
							{__('Refresh Product Data', 'tools-for-makeshop-wp-option')}
						</Button>
						{lastFetched > 0 && (
							<p style={{ marginTop: '10px', fontSize: '12px', color: '#666' }}>
								{__('Last updated:', 'tools-for-makeshop-wp-option')} {new Date(lastFetched * 1000).toLocaleString()}
							</p>
						)}
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					<div className="tfmwp-product-display-editor">
						{isLoading ? (
							<div className="tfmwp-loading">
								<Spinner />
								<p>{__('Loading products...', 'tools-for-makeshop-wp-option')}</p>
							</div>
						) : fetchError ? (
							<div className="tfmwp-error">
								<span className="dashicons dashicons-warning"></span>
								<p>{__('Error loading products:', 'tools-for-makeshop-wp-option')} {fetchError}</p>
							</div>
						) : productsData && productsData.length > 0 ? (
							<div className="tfmwp-product-preview">
								<div className="tfmwp-preview-header">
									<span className="dashicons dashicons-products"></span>
									<strong>{__('makeshop Product Display', 'tools-for-makeshop-wp-option')}</strong>
								</div>
								<div className="tfmwp-product-grid-preview" style={{ display: 'grid', gridTemplateColumns: `repeat(${Math.min(columnsDesktop, 3)}, 1fr)`, gap: '16px' }}>
									{productsData.map((product, index) => (
										<div key={index} className="tfmwp-product-item-preview">
											{product.error ? (
												<div className="tfmwp-product-error">
													<span className="dashicons dashicons-warning"></span>
													<p>{product.error}</p>
												</div>
											) : (
												<>
													{product.image && (
														<div className="tfmwp-product-image">
															<img src={product.image} alt={product.name} />
														</div>
													)}
													<div className="tfmwp-product-info">
														{product.category && (
															<div className="tfmwp-product-category">{product.category}</div>
														)}
														{product.name && (
															<h4 className="tfmwp-product-name">{product.name}</h4>
														)}
														{product.price && (
															<div className="tfmwp-product-price">{product.price}</div>
														)}
													</div>
												</>
											)}
										</div>
									))}
								</div>
								<p className="tfmwp-preview-grid-info" style={{ marginTop: '16px', fontSize: '12px', color: '#666' }}>
									{__('Grid:', 'tools-for-makeshop-wp-option')}
									{' '}
									{__('Desktop', 'tools-for-makeshop-wp-option')}: {columnsDesktop},
									{' '}
									{__('Tablet', 'tools-for-makeshop-wp-option')}: {columnsTablet},
									{' '}
									{__('Mobile', 'tools-for-makeshop-wp-option')}: {columnsMobile}
								</p>
							</div>
						) : productUrls ? (
							<div className="tfmwp-product-placeholder">
								<span className="dashicons dashicons-products"></span>
								<p>{__('Enter valid product URLs to see preview.', 'tools-for-makeshop-wp-option')}</p>
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
