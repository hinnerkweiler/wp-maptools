(function (blocks, element, blockEditor, components, i18n) {
    const el = element.createElement;
    const __ = i18n.__;
    const InspectorControls = blockEditor.InspectorControls;
    const MediaUpload = blockEditor.MediaUpload;
    const MediaUploadCheck = blockEditor.MediaUploadCheck;
    const useBlockProps = blockEditor.useBlockProps;
    const PanelBody = components.PanelBody;
    const Button = components.Button;
    const TextControl = components.TextControl;
    const ToggleControl = components.ToggleControl;
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
                    window.alert(__('Please select a .geojson or .json file.', 'hw-map-geojson'));
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
                    { title: __('GeoJSON Settings', 'hw-map-geojson'), initialOpen: true },

                    el(TextControl, {
                        label: __('Button text', 'hw-map-geojson'),
                        value: attributes.buttonText || '',
                        onChange: function (value) {
                            setAttributes({ buttonText: value });
                        }
                    }),

                    el(ToggleControl, {
                        label: __('Open in new tab', 'hw-map-geojson'),
                        checked: !!attributes.openInNewTab,
                        onChange: function (value) {
                            setAttributes({ openInNewTab: value });
                        }
                    }),

                    el('div', { style: { marginTop: '16px' } },
                        el(MediaUploadCheck, {},
                            el(MediaUpload, {
                                onSelect: onSelectMedia,
                                allowedTypes: ['application/json'],
                                value: attributes.attachmentId,
                                render: function (obj) {
                                    return el(Button, {
                                        variant: 'primary',
                                        onClick: obj.open
                                    }, attributes.attachmentId
                                        ? __('Replace file', 'hw-map-geojson')
                                        : __('Select GeoJSON file', 'hw-map-geojson')
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
                            }, __('Remove file', 'hw-map-geojson'))
                        )
                        : null
                )
            );

            let preview;

            if (attributes.attachmentId && attributes.fileUrl) {
                preview = el('div', {},
                    el('p', {},
                        el('strong', {}, __('Selected file:', 'hw-map-geojson'))
                    ),
                    el('p', {}, attributes.fileName || attributes.fileUrl),
                    el('p', {},
                        el('a', {
                            href: attributes.fileUrl,
                            target: '_blank',
                            rel: 'noreferrer noopener'
                        }, attributes.buttonText || __('Download GeoJSON', 'hw-map-geojson'))
                    )
                );
            } else {
                preview = el(Placeholder, {
                    label: __('Download GeoJSON', 'hw-map-geojson'),
                    instructions: __('Choose a GeoJSON file for this block.', 'hw-map-geojson')
                },
                    el(MediaUploadCheck, {},
                        el(MediaUpload, {
                            onSelect: onSelectMedia,
                            allowedTypes: ['application/json'],
                            value: attributes.attachmentId,
                            render: function (obj) {
                                return el(Button, {
                                    variant: 'primary',
                                    onClick: obj.open
                                }, __('Select GeoJSON file', 'hw-map-geojson'));
                            }
                        })
                    ),
                    el(Notice, { status: 'info', isDismissible: false },
                        __('This block stores the selected file in the block itself, not as a per-post custom field.', 'hw-map-geojson')
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
