import { InspectorControls } from '@wordpress/block-editor';
import { ComboboxControl, PanelBody } from '@wordpress/components';
import { useEntityRecords } from '@wordpress/core-data';
import { useState } from '@wordpress/element';
import type { WP_REST_API_Post as WPRestAPIPost } from 'wp-types';
import { useDebounce } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';

type SinglePostPickerProps = {
	attributes: {
		[ key: string ]: unknown;
		query: {
			[ key: string ]: unknown;
			selectedPostId: string;
		};
	};
	setAttributes: ( attributes: { [ key: string ]: unknown } ) => void;
};

const SinglePostPicker: React.FC< SinglePostPickerProps > = ( {
	setAttributes,
	attributes,
} ) => {
	const [ search, setSearch ] = useState( '' );
	const { isResolving, records: posts } = useEntityRecords< WPRestAPIPost >(
		'postType',
		'post',
		{
			per_page: 10,
			search,
		}
	);

	const setSearchDebounced = useDebounce( ( value ) => {
		setSearch( value );
	}, 300 );

	return (
		<>
			<InspectorControls>
				<PanelBody>
					<ComboboxControl
						label={ __(
							'Search a post',
							'single-post-query-loop-selector'
						) }
						onChange={ ( value ) => {
							const newAttributes = {
								attributes,
								query: {
									...attributes.query,
									include: [ value ],
									selectedPostId: value,
								},
							};
							setAttributes( newAttributes );
						} }
						onFilterValueChange={ ( value ) => {
							setSearchDebounced( value );
						} }
						options={
							isResolving
								? [
										{
											label: __(
												'Loading&hellip;',
												'single-post-query-loop-selector'
											),
											value: 'loading',
										},
								  ]
								: posts?.map( ( post ) => ( {
										label: post?.title?.rendered,
										value: String( post?.id ),
								  } ) ) || []
						}
						value={ attributes?.query?.selectedPostId || 'loading' }
					/>
				</PanelBody>
			</InspectorControls>
		</>
	);
};

export { SinglePostPicker };
