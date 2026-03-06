(function (blocks, element, blockEditor, components, i18n) {
    const el = element.createElement;
    const __ = i18n.__;
    const InspectorControls = blockEditor.InspectorControls;
    const MediaUpload = blockEditor.MediaUpload;
    const MediaUploadCheck = blockEditor.MediaUploadCheck;
    const useBlockProps = blockEditor.useBlockProps;
    const PanelBody = components.PanelBody;
    const Button = components.Button;
    const Notice = components.Notice;
    const Placeholder = components.Placeholder;

    function isValidGeoJsonFile(media) {
        if (!media) return false;

        const filename = (media.filename || '').toLowerCase();
        const mime = (media.mime || '').toLowerCase();

        return (
            filename.endsWith('.geojson') ||
            filename.endsWith('.json') ||
            mime === 'application/geo+json' ||
            mime === 'application/json'
        );
    }

    blocks.registerBlockType('hw/map-geojson', {
        edit: function (props) {
            const attributes = props.attributes;
            const setAttributes = props.setAttributes;

            const blockProps = useBlockProps();

            function onSelectMedia(media) {
                if (!isValidGeoJsonFile(media)) {
                    window.alert(__('Please select a .geojson or .json file.', 'wp-maptools'));
                    return;
                }

                setAttributes({
                    attachmentId: media.id || 0,
                    fileUrl: media.url || '',
                    fileName: media.filename || media.title || ''
                });
            }

            function removeFile() {
                setAttributes({
                    attachmentId: 0,
                    fileUrl: '',
                    fileName: ''
                });
            }

            const inspector = el(
                InspectorControls,
                {},
                el(
                    PanelBody,
                    { title: __('GeoJSON Settings', 'wp-maptools'), initialOpen: true },

                    el('div', { style: { marginTop: '8px' } },
                        el(MediaUploadCheck, {},
                            el(MediaUpload, {
                                onSelect: onSelectMedia,
                                allowedTypes: ['application/json', 'application/geo+json'],
                                value: attributes.attachmentId,
                                render: function (obj) {
                                    return el(Button, {
                                        variant: 'primary',
                                        onClick: obj.open
                                    }, attributes.attachmentId
                                        ? __('Replace file', 'wp-maptools')
                                        : __('Select GeoJSON file', 'wp-maptools')
                                    );
                                }
                            })
                        )
                    ),

                    attributes.attachmentId
                        ? el('div', { style: { marginTop: '12px' } },
                            el(Button, {
                                variant: 'secondary',
                                isDestructive: true,
                                onClick: removeFile
                            }, __('Remove file', 'wp-maptools'))
                        )
                        : null
                )
            );

            let preview;

            if (attributes.attachmentId && attributes.fileUrl) {
                preview = el('div', {},
                    el('p', {},
                        el('strong', {}, __('Selected file:', 'wp-maptools'))
                    ),
                    el('p', {}, attributes.fileName || attributes.fileUrl),
                    el('p', { style: { color: '#757575', fontSize: '0.85em' } },
                        __('Map will render on the front end.', 'wp-maptools')
                    )
                );
            } else {
                preview = el(Placeholder, {
                    label: __('GeoJSON Map', 'wp-maptools'),
                    instructions: __('Select or upload a GeoJSON file to display as an interactive map.', 'wp-maptools')
                },
                    el(MediaUploadCheck, {},
                        el(MediaUpload, {
                            onSelect: onSelectMedia,
                            allowedTypes: ['application/json', 'application/geo+json'],
                            value: attributes.attachmentId,
                            render: function (obj) {
                                return el(Button, {
                                    variant: 'primary',
                                    onClick: obj.open
                                }, __('Select GeoJSON file', 'wp-maptools'));
                            }
                        })
                    ),
                    el(Notice, { status: 'info', isDismissible: false },
                        __('This block stores the selected file in the block itself, not as a per-post custom field.', 'wp-maptools')
                    )
                );
            }

            return el('div', blockProps, inspector, preview);
        },

        save: function () {
            return null;
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n
);
