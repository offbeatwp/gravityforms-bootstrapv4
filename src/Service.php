<?php
namespace OffbeatWP\GravityFormsBootstrapV4;

use OffbeatWP\Services\AbstractService;
use OffbeatWP\Contracts\View;

class Service extends AbstractService
{
    public function register(View $view)
    {
        add_filter('gform_get_form_filter', [$this, 'bootstrapClasses'], 10, 2);
        add_filter('gform_field_container', [$this, 'bootstrapContainer'], 10, 6);
        add_filter('gform_field_content', [$this, 'fieldBootstrapClasses'], 10, 5);

        add_filter('gform_submit_button', [$this, 'buttonClassFrontend'], 10, 2);
        add_filter('gform_submit_button', [$this, 'inputToButton'], 10, 2);

        if (is_admin()) {
            add_action('gform_field_appearance_settings', [$this, 'customFields']);
            add_action('gform_editor_js', [$this, 'customFieldSizes']);
            add_filter('gform_tooltips', [$this, 'customFieldTooltips']);
        }
    }

    public static function bootstrapContainer($field_container, $field, $form, $css_class, $style, $field_content)
    {
        $replacement = 'class=$1$2 form-group';

        if (strpos($field['cssClass'], 'col-') == false) {
            if ($field['colXs'] == '' || !$field['colXs']) {
                $field['colXs'] = '12';
            }

            if ($field['colXs']) {
                $replacement .= ' col-' . $field['colXs'];
            }

            if ($field['colMd']) {
                $replacement .= ' col-md-' . $field['colMd'];
            }

            if ($field['colLg']) {
                $replacement .= ' col-lg-' . $field['colLg'];
            }
        }

        $replacement .= '$3';

        $field_container = preg_replace('/class=(\'|")([^\'"]+)(\'|")/', $replacement, $field_container);

        return $field_container;
    }

    public function bootstrapClasses($formHtml)
    {
        if (preg_match("/class='[^']*gform_validation_error[^']*'/", $formHtml)) {
            preg_match_all("/class='(gfield [^']+)'/", $formHtml, $gFields);

            if (!empty($gFields[0])) {
                foreach ($gFields[0] as $gFieldIndex => $gField) {
                    $class = " is-valid";

                    if (strpos($gFields[1][$gFieldIndex], 'gfield_error') !== false) {
                        $class = ' is-invalid';
                    }

                    $formHtml = str_replace($gField, "class='" . $gFields[1][$gFieldIndex] . $class . "'", $formHtml);
                }
            }

        }

        return $formHtml;
    }

    /**
     * @param string $fieldContent
     * @param object $field
     * @param string $value
     * @param int $entryId
     * @param int $formId
     * @return string
     */
    public function fieldBootstrapClasses($fieldContent, $field, $value, $entryId, $formId)
    {
        if (strpos($fieldContent, '<select') !== false) {
            preg_match_all('/<select[^>]+>/', $fieldContent, $selectTags);

            if (!empty($selectTags[0])) {
                foreach ($selectTags[0] as $selectTag) {
                    if (strpos($selectTag, 'class=') !== false) {
                        $fieldContent = str_replace($selectTag, preg_replace("/class='([^']+)'/", "class='$1 custom-select'", $selectTag), $fieldContent);
                    } else {
                        $fieldContent = str_replace($selectTag, str_replace('<select', '<select class="custom-select"', $selectTag), $fieldContent);
                    }
                }
            }
        }

        if (preg_match("/type='(radio|checkbox)'/", $fieldContent)) {
            preg_match_all("/(<input[^>]*type='(radio|checkbox)'[^>]+>)\s*<label[^>]+>(.*)<\/label>/misU", $fieldContent, $radioTags);

            if (!empty($radioTags[0])) {
                foreach ($radioTags[0] as $radioIndex => $radioTag) {
                    $inputField = $radioTag;
                    $inputField = str_replace("<input", "<input class='custom-control-input'", $inputField);
                    $inputField = str_replace("<label", "<label class='custom-control-label'", $inputField);

                    $fieldContent = str_replace($radioTag, '<div class="custom-control custom-' . $radioTags[2][$radioIndex] . '">' . $inputField . '</div>', $fieldContent);
                }
            }

        }

        if (preg_match("/type='file'/", $fieldContent)) {
            preg_match_all("/<input[^>]*type='file'[^>]+>/", $fieldContent, $inputFileTags);

            if (!empty($inputFileTags[0])) {
                foreach ($inputFileTags[0] as $inputFileTag) {
                    $inputFileTagBs = preg_replace("/class='([^']+)'/", "class='$1 custom-file-input'", $inputFileTag);

                    $fieldContent = str_replace($inputFileTag, '<label class="custom-file-label">' . __('Choose file', 'offbeatwp') . '</label>' . $inputFileTagBs, $fieldContent);
                }
            }

        }

        return $fieldContent;
    }

    public static function buttonClassFrontend($button, $form)
    {
        if (isset($form['button']) && isset($form['button']['class']) && !empty($form['button']['class']) ) {
            $button = preg_replace("/class='([\.a-zA-Z_ -]+)'/", "class='$1 btn " . $form['button']['class']. "'", $button);
        }

        return $button;

    }

    public static function inputToButton($button_input, $form)
    {
        preg_match("/<input([^\/>]*)(\s\/)*>/", $button_input, $button_match);

        $button_atts = str_replace("value='" . $form['button']['text'] . "' ", "", $button_match[1]);

        return '<button ' . $button_atts . '>' . $form['button']['text'] . '</button>';
    }

    public function customFields($position)
    {
        if ($position !== 400) {
            return;
        }

        ?>
        <li class="col_xs_setting field_setting">
            <ul>
                <li>
                    <label for="field_col_xs" class="section_label">
                        <?php esc_html_e('Field Size (mobile)', 'offbeatwp');?>
                        <?php gform_tooltip('form_field_col_xs');?>
                    </label>
                    <select id="field_col_xs" onchange="SetFieldProperty('colXs', this.value)">
                        <option value="12">12</option>
                        <option value="11">11</option>
                        <option value="10">10</option>
                        <option value="9">9</option>
                        <option value="8">8</option>
                        <option value="7">7</option>
                        <option value="6">6</option>
                        <option value="5">5</option>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1</option>
                    </select>
                </li>
            </ul>
        </li>

        <li class="col_md_setting field_setting">
            <ul>
                <li>
                    <label for="field_col_md" class="section_label">
                        <?php esc_html_e('Field Size (tablet)', 'offbeatwp');?>
                        <?php gform_tooltip('form_field_col_md');?>
                    </label>
                    <select id="field_col_md" onchange="SetFieldProperty('colMd', this.value)">
                        <option value="">Inherit</option>
                        <option value="12">12</option>
                        <option value="11">11</option>
                        <option value="10">10</option>
                        <option value="9">9</option>
                        <option value="8">8</option>
                        <option value="7">7</option>
                        <option value="6">6</option>
                        <option value="5">5</option>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1</option>
                    </select>
                </li>
            </ul>
        </li>

        <li class="col_lg_setting field_setting">
            <ul>
                <li>
                    <label for="field_col_lg" class="section_label">
                        <?php esc_html_e('Field Size (desktop)', 'offbeatwp');?>
                        <?php gform_tooltip('form_field_col_lg');?>
                    </label>
                    <select id="field_col_lg" onchange="SetFieldProperty('colLg', this.value)">
                        <option value="">Inherit</option>
                        <option value="12">12</option>
                        <option value="11">11</option>
                        <option value="10">10</option>
                        <option value="9">9</option>
                        <option value="8">8</option>
                        <option value="7">7</option>
                        <option value="6">6</option>
                        <option value="5">5</option>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1</option>
                    </select>
                </li>
            </ul>
        </li>
        <?php

    }

    public function customFieldSizes()
    {

        ?>
        <script type="text/javascript">
            jQuery.map(fieldSettings, function (el, i) {
                fieldSettings[i] += ', .col_xs_setting';
                fieldSettings[i] += ', .col_md_setting';
                fieldSettings[i] += ', .col_lg_setting';
            });

            jQuery(document).on('gform_load_field_settings', function (ev, field) {
                jQuery('#field_col_xs').val(field.colXs || '12');
                jQuery('#field_col_md').val(field.colMd || '');
                jQuery('#field_col_lg').val(field.colLg || '');
            });

            // Disable original field size setting
            jQuery(document).ready(function () {
                jQuery('.field_setting.size_setting').remove();
            });
        </script>
        <?php

    }

    public function customFieldTooltips($tooltips)
    {
        $tooltips['form_field_col_xs'] = sprintf(
            '<h6>%s</h6>%s',
            __('Field Size (mobile)', 'offbeatwp'),
            __('Select a form field size from the available options. This will set the width of the field on (most) mobile devices and up. If no field sizes are set for larger devices this setting will be inherited.',
                'offbeatwp')
        );

        $tooltips['form_field_col_md'] = sprintf(
            '<h6>%s</h6>%s',
            __('Field Size (tablet)', 'offbeatwp'),
            __('Select a form field size from the available options. This will set the width of the field on (most) tablet devices and up. If no field sizes are set for larger devices this setting will be inherited.',
                'offbeatwp')
        );

        $tooltips['form_field_col_lg'] = sprintf(
            '<h6>%s</h6>%s',
            __('Field Size (desktop)', 'offbeatwp'),
            __('Select a form field size from the available options. This will set the width of the field on (most) desktop devices and up.',
                'offbeatwp')
        );

        return $tooltips;
    }
}