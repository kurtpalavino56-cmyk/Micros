@props([
    'id' => 'rich-editor',
    'name' => 'content',
    'value' => '',
    'placeholder' => 'Write here...',
])

@php
    $editorId = preg_replace('/[^A-Za-z0-9\_-]/', '-', (string) $id);
    $fieldName = $name;
    $initialValue = (string) $value;
    $editorPlaceholder = $placeholder;
@endphp

<div class="up-ckeditor-wrapper" data-ckeditor-wrapper>
    <textarea
        id="{{ $editorId }}"
        name="{{ $fieldName }}"
        data-ckeditor
        data-placeholder="{{ $editorPlaceholder }}"
    >{{ $initialValue }}</textarea>
</div>

<style>
    .up-ckeditor-wrapper {
        width: 100%;
    }

    .up-ckeditor-wrapper .ck-editor {
        width: 100%;
    }

    .up-ckeditor-wrapper .ck-editor__main > .ck-editor__editable {
        min-height: 180px;
        padding: 14px 16px;
        font-family: Arial, "Segoe UI", sans-serif;
        font-size: 14px;
        line-height: 1.65;
        color: #1d2939;
    }

    .up-ckeditor-wrapper .ck-editor__editable_inline {
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    .up-ckeditor-wrapper .ck.ck-toolbar {
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
        border-color: #d7dce5;
        background: #f7f8fa;
    }

    .up-ckeditor-wrapper .ck.ck-editor__main > .ck-editor__editable {
        border-color: #d7dce5;
    }

    .up-ckeditor-wrapper .ck.ck-editor__main > .ck-editor__editable.ck-focused {
        border-color: #155eef;
        box-shadow: 0 0 0 1px #155eef;
    }

    .up-ckeditor-wrapper .ck.ck-button.ck-on,
    .up-ckeditor-wrapper .ck.ck-button:active {
        background: #e6efff;
        color: #155eef;
    }

    .up-ckeditor-wrapper .ck-content p {
        margin: 0 0 9px;
    }

    .up-ckeditor-wrapper .ck-content h1 {
        margin: 18px 0 9px;
        font-size: 24px;
        line-height: 1.25;
    }

    .up-ckeditor-wrapper .ck-content h2 {
        margin: 16px 0 8px;
        font-size: 20px;
        line-height: 1.3;
    }

    .up-ckeditor-wrapper .ck-content h3 {
        margin: 14px 0 7px;
        font-size: 17px;
        line-height: 1.35;
    }

    .up-ckeditor-wrapper .ck-content blockquote {
        margin: 10px 0;
    }

    .up-ckeditor-wrapper .ck-content pre {
        margin: 10px 0;
    }

    .up-ckeditor-wrapper .ck-content img {
        max-width: 100%;
        height: auto;
    }

    .up-ckeditor-wrapper .ck-content a {
        color: #155eef;
    }

    @media (max-width: 900px) {
        .up-ckeditor-wrapper .ck.ck-toolbar {
            flex-wrap: wrap;
        }
    }
</style>