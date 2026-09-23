import {
    ClassicEditor,
    FileRepository,
    ButtonView,
    Plugin,
    toWidget,
    Essentials,
    Paragraph,
    Heading,
    Bold,
    Italic,
    Underline,
    Strikethrough,
    Font,
    Subscript,
    Superscript,
    Link,
    BlockQuote,
    List,
    Alignment,
    Undo,
    CodeBlock,
    RemoveFormat,
    
    Image,
    ImageToolbar,
    ImageCaption,
    ImageStyle,
    ImageUpload,
    SimpleUploadAdapter,
    MediaEmbed,
} from 'ckeditor5';

import 'ckeditor5/ckeditor5.css';

const editorInstances = new Map();

class UpskillFileUploadAdapter {
    constructor(loader) {
        this.loader = loader;
        this.xhr = null;
    }

    upload() {
        return this.loader.file.then((file) => new Promise((resolve, reject) => {
            const formData = new FormData();

            formData.append('upload', file);

            const xhr = this.xhr = new XMLHttpRequest();

            xhr.open('POST', '/Faculty-editor/upload?kind=file', true);

            xhr.setRequestHeader(
                'X-CSRF-TOKEN',
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || ''
            );

            xhr.responseType = 'json';

            xhr.addEventListener('error', () => {
                reject('File upload failed.');
            });

            xhr.addEventListener('abort', () => {
                reject();
            });

            xhr.addEventListener('load', () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    const response = xhr.response;

                    if (response?.url) {
                        resolve({
                            default: response.url,
                        });
                    } else {
                        reject(response?.message || 'Invalid upload response.');
                    }

                    return;
                }

                reject(
                    xhr.response?.message ||
                    `Upload failed with status ${xhr.status}.`
                );
            });

            if (xhr.upload) {
                xhr.upload.addEventListener('progress', (event) => {
                    if (event.lengthComputable) {
                        this.loader.uploadTotal = event.total;
                        this.loader.uploaded = event.loaded;
                    }
                });
            }

            xhr.send(formData);
        }));
    }

    abort() {
        if (this.xhr) {
            this.xhr.abort();
        }
    }
}

/**start */
class UpskillAttachmentPlugin extends Plugin {
    init() {
        const editor = this.editor;

        // Register the attachment as a real CKEditor object.
        editor.model.schema.register('upskillAttachment', {
            isObject: true,
            isBlock: true,
            allowWhere: '$block',
            allowAttributes: [
                'fileUrl',
                'fileName',
                'fileType',
            ],
        });

        /*
         * HTML → CKEditor model
         *
         * PDF:
         * <figure class="upskill-pdf">
         *     <oembed url="..."></oembed>
         * </figure>
         */
        editor.conversion.for('upcast').elementToElement({
            view: {
                name: 'figure',
                classes: 'upskill-pdf',
            },
            model: (viewElement, { writer }) => {
                const oembed = viewElement.getChild(0);

                const fileUrl =
                    oembed?.getAttribute('url') || '';

                const fileName =
                    viewElement.getAttribute('data-file-name') ||
                    'Attached PDF';

                return writer.createElement(
                    'upskillAttachment',
                    {
                        fileUrl,
                        fileName,
                        fileType: 'pdf',
                    }
                );
            },
        });

        /*
         * HTML → CKEditor model
         *
         * Other file attachments:
         * <a class="lesson-file-attachment" ...>
         */
        editor.conversion.for('upcast').elementToElement({
            view: {
                name: 'a',
                classes: 'lesson-file-attachment',
            },
            model: (viewElement, { writer }) => {
                return writer.createElement(
                    'upskillAttachment',
                    {
                        fileUrl:
                            viewElement.getAttribute('href') || '',

                        fileName:
                            viewElement.getAttribute(
                                'data-file-name'
                            ) ||
                            viewElement.getChild(0)?.data ||
                            'Attached file',

                        fileType:
                            viewElement.getAttribute(
                                'data-file-type'
                            ) || 'file',
                    }
                );
            },
        });

        /*
         * CKEditor model → saved HTML
         *
         * PDF → embedded PDF structure.
         *
         * We use <oembed> instead of <iframe> so that
         * Laravel's existing rich-text sanitizer can
         * safely preserve the PDF URL.
         */
        editor.conversion.for('dataDowncast').elementToElement({
            model: 'upskillAttachment',

            view: (modelElement, { writer }) => {
                const fileUrl =
                    modelElement.getAttribute('fileUrl') || '';

                const fileName =
                    modelElement.getAttribute('fileName') ||
                    'Attached file';

                const fileType =
                    (
                        modelElement.getAttribute('fileType') ||
                        'file'
                    ).toLowerCase();

                /*
                 * PDF
                 */
                if (fileType === 'pdf') {
                    const figure =
                        writer.createContainerElement(
                            'figure',
                            {
                                class: 'upskill-pdf',
                                'data-file-name': fileName,
                            }
                        );

                    const oembed =
                        writer.createEmptyElement(
                            'oembed',
                            {
                                url: fileUrl,
                            }
                        );

                    writer.insert(
                        writer.createPositionAt(
                            figure,
                            0
                        ),
                        oembed
                    );

                    return figure;
                }

                /*
                 * Other files remain normal
                 * downloadable attachments.
                 */
                const link =
                    writer.createContainerElement(
                        'a',
                        {
                            href: fileUrl,
                            class:
                                'lesson-file-attachment',
                            'data-file-name': fileName,
                            'data-file-type': fileType,
                            target: '_blank',
                            rel:
                                'noopener noreferrer',
                        }
                    );

                writer.insert(
                    writer.createPositionAt(link, 0),
                    writer.createText(
                        `📎 ${fileName}`
                    )
                );

                return link;
            },
        });

        /*
         * CKEditor editing view
         *
         * PDF → show an actual PDF preview
         * inside the editor.
         */
        editor.conversion.for(
            'editingDowncast'
        ).elementToElement({
            model: 'upskillAttachment',

            view: (modelElement, { writer }) => {
                const fileUrl =
                    modelElement.getAttribute(
                        'fileUrl'
                    ) || '';

                const fileName =
                    modelElement.getAttribute(
                        'fileName'
                    ) || 'Attached file';

                const fileType =
                    (
                        modelElement.getAttribute(
                            'fileType'
                        ) || 'file'
                    ).toLowerCase();

                /*
                 * PDF viewer
                 */
                if (fileType === 'pdf') {
                    const container =
                        writer.createContainerElement(
                            'div',
                            {
                                class:
                                    'upskill-pdf-editor',
                                'data-file-url':
                                    fileUrl,
                                'data-file-name':
                                    fileName,
                            }
                        );

                    const header =
                        writer.createContainerElement(
                            'div',
                            {
                                class:
                                    'upskill-pdf-editor__header',
                            }
                        );

                    writer.insert(
                        writer.createPositionAt(
                            header,
                            0
                        ),
                        writer.createText(
                            `📄 ${fileName}`
                        )
                    );

                    const iframe =
                        writer.createRawElement(
                            'iframe',
                            {
                                src: fileUrl,
                                title: fileName,
                                class:
                                    'upskill-pdf-editor__frame',
                            },
                            (domElement) => {
                                domElement.setAttribute(
                                    'loading',
                                    'lazy'
                                );

                                domElement.setAttribute(
                                    'allow',
                                    'fullscreen'
                                );
                            }
                        );

                    writer.insert(
                        writer.createPositionAt(
                            container,
                            0
                        ),
                        header
                    );

                    writer.insert(
                        writer.createPositionAt(
                            container,
                            'end'
                        ),
                        iframe
                    );

                    return toWidget(container, writer, {
                        label: `PDF: ${fileName}`,
                    });
                }

                /*
                 * Other files
                 */
                const container =
                    writer.createContainerElement(
                        'div',
                        {
                            class:
                                'lesson-file-attachment',
                            'data-file-url':
                                fileUrl,
                            'data-file-name':
                                fileName,
                            'data-file-type':
                                fileType,
                        }
                    );

                const icon =
                    writer.createContainerElement(
                        'div',
                        {
                            class:
                                'lesson-file-attachment__icon',
                        }
                    );

                writer.insert(
                    writer.createPositionAt(
                        icon,
                        0
                    ),
                    writer.createText('📎')
                );

                const info =
                    writer.createContainerElement(
                        'div',
                        {
                            class:
                                'lesson-file-attachment__info',
                        }
                    );

                const name =
                    writer.createContainerElement(
                        'div',
                        {
                            class:
                                'lesson-file-attachment__name',
                        }
                    );

                writer.insert(
                    writer.createPositionAt(
                        name,
                        0
                    ),
                    writer.createText(fileName)
                );

                const type =
                    writer.createContainerElement(
                        'div',
                        {
                            class:
                                'lesson-file-attachment__type',
                        }
                    );

                writer.insert(
                    writer.createPositionAt(
                        type,
                        0
                    ),
                    writer.createText(
                        `${fileType.toUpperCase()} file`
                    )
                );

                writer.insert(
                    writer.createPositionAt(
                        info,
                        0
                    ),
                    name
                );

                writer.insert(
                    writer.createPositionAt(
                        info,
                        'end'
                    ),
                    type
                );

                writer.insert(
                    writer.createPositionAt(
                        container,
                        0
                    ),
                    icon
                );

                writer.insert(
                    writer.createPositionAt(
                        container,
                        'end'
                    ),
                    info
                );

                return container;
            },
        });
    }
}
/*end*/

/**start */
class UpskillFileUploadButton extends Plugin {
    init() {
        const editor = this.editor;

        editor.ui.componentFactory.add('attachFile', (locale) => {
            const button = new ButtonView(locale);

            button.set({
                label: 'Attach File',
                withText: true,
                tooltip: 'Attach a PDF or file',
            });

            button.on('execute', () => {
                const input = document.createElement('input');

                input.type = 'file';
                input.accept = [
                    '.pdf',
                    '.doc',
                    '.docx',
                    '.ppt',
                    '.pptx',
                    '.xls',
                    '.xlsx',
                    '.txt',
                    '.zip',
                    '.rar',
                ].join(',');

                input.style.display = 'none';
                document.body.appendChild(input);

                input.addEventListener('change', async () => {
                    const file = input.files?.[0];

                    if (!file) {
                        input.remove();
                        return;
                    }

                    try {
                        const formData = new FormData();
                        formData.append('upload', file);

                        const csrfToken = document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content');

                        const response = await fetch(
                            '/Faculty-editor/upload?kind=file',
                            {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken || '',
                                    'Accept': 'application/json',
                                },
                                body: formData,
                            }
                        );

                        const data = await response.json();

                        if (!response.ok || !data.url) {
                            throw new Error(
                                data.message ||
                                `Upload failed with status ${response.status}.`
                            );
                        }

                        /*start */
                        const extension = (
                            file.name.split('.').pop() || 'file'
                        ).toLowerCase();

                        editor.model.change((writer) => {
                            const attachment = writer.createElement(
                                'upskillAttachment',
                                {
                                    fileUrl: data.url,
                                    fileName: file.name,
                                    fileType: extension,
                                }
                            );

                            editor.model.insertObject(
                                attachment,
                                null,
                                null,
                                {
                                    findOptimalPosition: 'auto',
                                    setSelection: 'on',
                                }
                            );
                        });/*end */

                    } catch (error) {
                        console.error('File upload failed:', error);
                    } finally {
                        input.remove();
                    }
                });

                input.click();
            });

            return button;
        });
    }
}/*end */

function UpskillFileUploadPlugin(editor) {
    editor.plugins.get(FileRepository).createUploadAdapter = (loader) => {
        return new UpskillFileUploadAdapter(loader);
    };

    editor.plugins.get(UpskillFileUploadButton);
}

function getEditorConfig(element) {
    return {
        licenseKey: 'GPL',

        plugins: [
            Essentials,
            Paragraph,
            Heading,
            Bold,
            Italic,
            Underline,
            Strikethrough,
            Font,
            Subscript,
            Superscript,
            Link,
            BlockQuote,
            List,
            Alignment,
            Undo,
            CodeBlock,
            RemoveFormat,

            Image,
            ImageToolbar,
            ImageCaption,
            ImageStyle,
            ImageUpload,
            SimpleUploadAdapter,
            MediaEmbed,

            FileRepository,
            UpskillAttachmentPlugin,
            UpskillFileUploadButton,
            UpskillFileUploadPlugin,
        ],

        toolbar: [
            'undo',
            'redo',
            '|',
            'heading',
            '|',
            'bold',
            'italic',
            'underline',
            'strikethrough',
            '|',
            'fontColor',
            'fontBackgroundColor',
            '|',
            'subscript',
            'superscript',
            '|',
            'bulletedList',
            'numberedList',
            '|',
            'alignment',
            '|',
            'link',
            'imageUpload',
            'attachFile',
            'blockQuote',
            'codeBlock',
            'removeFormat',
            'mediaEmbed',
        ],

        image: {
    toolbar: [
        'imageTextAlternative',
        'toggleImageCaption',
        '|',
        'imageStyle:inline',
        'imageStyle:block',
        'imageStyle:side',
    ],

},

        simpleUpload: {
            uploadUrl: '/Faculty-editor/upload?kind=image',
            headers: {
                'X-CSRF-TOKEN': document
                   .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content'),
                },
            },

        heading: {
            options: [
                {
                    model: 'paragraph',
                    title: 'Normal',
                    class: 'ck-heading_paragraph',
                },
                {
                    model: 'heading1',
                    view: 'h1',
                    title: 'Heading 1',
                    class: 'ck-heading_heading1',
                },
                {
                    model: 'heading2',
                    view: 'h2',
                    title: 'Heading 2',
                    class: 'ck-heading_heading2',
                },
                {
                    model: 'heading3',
                    view: 'h3',
                    title: 'Heading 3',
                    class: 'ck-heading_heading3',
                },
            ],
        },

        alignment: {
            options: ['left', 'center', 'right', 'justify'],
        },

        link: {
            addTargetToExternalLinks: true,
            defaultProtocol: 'https://',
        },
    };
}

function initializeEditors() {
    document.querySelectorAll('[data-ckeditor]').forEach((element) => {
        if (editorInstances.has(element)) {
            return;
        }

        const target = element;

        ClassicEditor
            .create(target, getEditorConfig(target))
            .then((editor) => {
                editorInstances.set(target, editor);

                const form = target.closest('form');

                if (form) {
                    // if (form) {
                //     if (form) {
                //     form.addEventListener('submit', () => {
                //         target.value = editor.getData();
                //     });
                // }

                // editor.model.document.on('change:data', () => {
                //     target.value = editor.getData();
                // });
                form.addEventListener('submit', () => {
                    target.value = editor.getData();
                });
            }

            editor.model.document.on('change:data', () => {
                target.value = editor.getData();
            });
            })
            .catch((error) => {
                console.error('CKEditor initialization failed:', error);
            });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeEditors);
} else {
    initializeEditors();
}

window.initializeUpskillEditors = initializeEditors;
window.upskillEditorInstances = editorInstances;