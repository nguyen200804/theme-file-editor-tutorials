/**
 * Admin Custom Fields JavaScript
 *
 * Xử lý tương tác media uploader (chọn ảnh, gallery) và thêm/xóa dòng repeater.
 *
 * @package Theme_File_Editor_Tutorials
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        /* ==========================================================================
           1. Single Image Field
           ========================================================================== */
        $(document).on('click', '.ten-upload-image-button', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var $wrapper = $btn.closest('.ten-image-field-wrap');
            var $input = $wrapper.find('.ten-image-id-input');
            var $preview = $wrapper.find('.ten-image-preview');
            var $removeBtn = $wrapper.find('.ten-remove-image-button');

            var frame = wp.media({
                title: 'Chọn hoặc tải ảnh lên',
                button: { text: 'Sử dụng ảnh này' },
                multiple: false
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.id);

                var imgUrl = (attachment.sizes && attachment.sizes.thumbnail) 
                    ? attachment.sizes.thumbnail.url 
                    : attachment.url;

                $preview.html('<img src="' + imgUrl + '" style="max-width:150px;max-height:150px;border-radius:4px;display:block;margin-bottom:8px;" />');
                $removeBtn.show();
            });

            frame.open();
        });

        $(document).on('click', '.ten-remove-image-button', function (e) {
            e.preventDefault();
            var $wrapper = $(this).closest('.ten-image-field-wrap');
            $wrapper.find('.ten-image-id-input').val('');
            $wrapper.find('.ten-image-preview').empty();
            $(this).hide();
        });

        /* ==========================================================================
           2. Gallery Field
           ========================================================================== */
        function updateGalleryInput($wrapper) {
            var ids = [];
            $wrapper.find('.ten-gallery-preview-item').each(function () {
                var id = $(this).data('id');
                if (id) {
                    ids.push(id);
                }
            });
            $wrapper.find('.ten-gallery-ids-input').val(ids.join(','));
        }

        $(document).on('click', '.ten-upload-gallery-button', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var $wrapper = $btn.closest('.ten-gallery-field-wrap');
            var $previewList = $wrapper.find('.ten-gallery-preview-list');

            var frame = wp.media({
                title: 'Chọn ảnh cho bộ sưu tập',
                button: { text: 'Thêm vào bộ sưu tập' },
                multiple: true
            });

            frame.on('select', function () {
                var selection = frame.state().get('selection');
                selection.map(function (attachment) {
                    attachment = attachment.toJSON();
                    var imgUrl = (attachment.sizes && attachment.sizes.thumbnail)
                        ? attachment.sizes.thumbnail.url
                        : attachment.url;

                    var itemHtml = '<div class="ten-gallery-preview-item" data-id="' + attachment.id + '" style="display:inline-block;position:relative;margin:0 8px 8px 0;">' +
                        '<img src="' + imgUrl + '" style="width:75px;height:75px;object-fit:cover;border-radius:4px;border:1px solid #ccd0d4;display:block;" />' +
                        '<button type="button" class="ten-remove-gallery-item" style="position:absolute;top:-6px;right:-6px;background:#d63638;color:#fff;border:none;border-radius:50%;width:18px;height:18px;line-height:16px;font-size:12px;cursor:pointer;padding:0;">&times;</button>' +
                        '</div>';

                    $previewList.append(itemHtml);
                });

                updateGalleryInput($wrapper);
            });

            frame.open();
        });

        $(document).on('click', '.ten-remove-gallery-item', function (e) {
            e.preventDefault();
            var $wrapper = $(this).closest('.ten-gallery-field-wrap');
            $(this).closest('.ten-gallery-preview-item').remove();
            updateGalleryInput($wrapper);
        });

        /* ==========================================================================
           3. Repeater Field
           ========================================================================== */
        $(document).on('click', '.ten-repeater-add-row', function (e) {
            e.preventDefault();

            var $wrapper = $(this).closest('.ten-repeater-wrap');
            var $tbody = $wrapper.find('.ten-repeater-tbody');
            var templateHtml = $wrapper.find('.ten-repeater-template').html();

            var newIndex = new Date().getTime();
            var rowHtml = templateHtml.replace(/{{INDEX}}/g, newIndex);

            $tbody.append(rowHtml);
        });

        $(document).on('click', '.ten-repeater-remove-row', function (e) {
            e.preventDefault();
            $(this).closest('.ten-repeater-row').remove();
        });

    });
})(jQuery);
