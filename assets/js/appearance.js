(function ($) {
    'use strict';
    document.addEventListener('click', function (event) {
        const stage = event.target.closest('.formorbit-stage-fields');
        if (stage && window.FormOrbitBuilder) window.FormOrbitBuilder.setActiveStage(Number(stage.dataset.stage));
    }, true);
    document.addEventListener('webform:collect-settings', function (event) {
        event.detail.submit_alignment = $('#webform-submit-alignment').val() || 'center';
        event.detail.custom_css = $('#webform-custom-css').val() || '';
        event.detail.stage_font_size = $('#formorbit-stage-font-size').val();
        event.detail.hide_stage_name = $('#formorbit-hide-stage-name').is(':checked');
    });
    function refresh() {
        $('.formorbit-editor-actions').remove();
        const alignment = $('#webform-submit-alignment').val() || 'center';
        $('<div class="formorbit-editor-actions"><button type="button" class="button button-primary"></button></div>').css('text-align', alignment === 'full' ? 'center' : alignment).find('button').text($('#webform-submit-label').val() || 'Submit').end().appendTo('.webform-canvas-panel');
        $('.webform-live-preview-actions').css('justify-content', {left:'flex-start', center:'center', right:'flex-end'}[alignment] || 'center');
    }
    const canvas = document.getElementById('webform-canvas');
    if (canvas) new MutationObserver(refresh).observe(canvas, {childList:true});
    $(document).on('input change', '#webform-submit-alignment,#webform-submit-label', refresh);
    $('input[type=color]').each(function () {
        const input = $(this);
        const hex = $('<input type="text" class="formorbit-hex" maxlength="7" aria-label="Hex color">').val(input.val());
        input.after(hex);
        hex.on('change', function () { if (/^#[0-9a-f]{6}$/i.test(this.value)) input.val(this.value).trigger('change'); else this.value = input.val(); });
        input.on('input change', function () { hex.val(input.val()); });
    });
    refresh();
    if (!$('.webform-live-preview-trigger').length && $('#webform-save').length) {
        $('<button type="button" class="button">Preview</button>').insertBefore('#webform-save').on('click', function () {
            const dialog = $('<div class="formorbit-preview-overlay" role="dialog" aria-modal="true" aria-label="Form preview"><div class="formorbit-preview-dialog"><button type="button" class="button formorbit-preview-close">Close preview</button><div class="webform-public"></div></div></div>');
            const form = dialog.find('.webform-public').addClass('webform-style-' + $('#webform-style-preset').val());
            form.append($('#webform-canvas').children().clone());
            form.find('button,.webform-drag,.webform-type').remove();
            form.append($('.formorbit-editor-actions').clone());
            dialog.find('.formorbit-preview-close').on('click', function () { dialog.remove(); $('#webform-save').trigger('focus'); });
            dialog.on('keydown', function (event) { if (event.key === 'Escape') dialog.find('.formorbit-preview-close').trigger('click'); });
            dialog.appendTo('body').find('.formorbit-preview-close').trigger('focus');
        });
    }
}(jQuery));
