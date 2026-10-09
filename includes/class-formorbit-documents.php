<?php
defined('ABSPATH') || exit;
class FormOrbit_Documents {
    public function __construct() {
        add_filter('webform_pro_pdf_attachment', array($this, 'pdf_attachment'), 10, 4);
        add_action('webform_after_submission', array($this, 'notify'), 25, 4);
    }
    public function notify($entry_id, $form_id, $data, $settings) {
        if (!empty($settings['pdf_notifications']) && is_email($settings['notification_email'] ?? '')) $this->pdf_notification($settings['notification_email'], $form_id, $entry_id, $data);
    }
    private function pdf_notification($email, $form_id, $entry_id, $data) {
        $file = $this->pdf_attachment('', $form_id, $entry_id, $data);
        if (!$file) return;
        wp_mail($email, sprintf(__('PDF submission: %s', 'formorbit'), get_the_title($form_id)), __('A PDF copy of the form submission is attached.', 'formorbit'), array(), array($file));
        wp_delete_file($file);
    }
    public function pdf_attachment($file, $form_id, $entry_id, $data) {
        if (is_string($file) && $file && file_exists($file)) return $file;
        $lines = array(get_the_title($form_id), 'Entry #' . absint($entry_id), gmdate('Y-m-d H:i:s') . ' UTC', '');
        foreach ($data as $item) {
            $value = is_array($item['value'] ?? '') ? implode(', ', $item['value']) : ($item['value'] ?? '');
            $lines[] = ($item['label'] ?? '') . ': ' . $value;
        }
        $content = "BT /F1 11 Tf 50 760 Td 14 TL\n";
        foreach (array_slice($lines, 0, 48) as $line) {
            $line = preg_replace('/[^\x20-\x7E]/', '?', (string) $line);
            $content .= '(' . str_replace(array('\\', '(', ')'), array('\\\\', '\\(', '\\)'), substr($line, 0, 110)) . ") Tj T*\n";
        }
        $content .= 'ET';
        $objects = array(
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream",
        );
        $pdf = "%PDF-1.4\n";
        $offsets = array(0);
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        for ($index = 1; $index <= count($objects); $index++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$index]);
        $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
        $file = wp_tempnam('webform-entry-' . $entry_id . '.pdf');
        if (!$file) return '';
        file_put_contents($file, $pdf);
        return $file;
    }
}
