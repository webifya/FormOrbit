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
    function colors() { $('input[type=color]:not([data-hex-ready])').each(function () {
        const input = $(this);
        input.attr('data-hex-ready', '1');
        const hex = $('<input type="text" class="formorbit-hex" maxlength="7" aria-label="Hex color">').val(input.val());
        input.after(hex);
        hex.on('change', function () { if (/^#[0-9a-f]{6}$/i.test(this.value)) input.val(this.value).trigger('change'); else this.value = input.val(); });
        input.on('input change', function () { hex.val(input.val()); });
    }); }
    colors();
    const properties = document.querySelector('.webform-properties');
    if (properties) new MutationObserver(colors).observe(properties, {childList:true, subtree:true});
    document.addEventListener('formorbit:field-settings-rendered', colors);
    refresh();
}(jQuery));
