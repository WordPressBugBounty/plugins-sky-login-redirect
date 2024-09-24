<?php
/**
 * Fields
 * PHP version 7
 *
 * @category Fields
 * @package  ZenSettings
 * @author   Utopique <support@utopique.net>
 * @license  SaaS https://utopique.net
 * @link     https://utopique.net
 */

namespace ZenSettings\Fields;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Fields class
 */
class Fields
{
    private $_option_name;

    /**
     * Construct
     *
     * @param string $option_name option_name
     */
    public function __construct($option_name)
    {
        $this->_option_name = $option_name;
    }
    /**
     * Checkmate : display the checkbox dedicated to an option key
     *
     * @param string $key  array key
     * @param array  $args args
     *
     * @return html
     */
    public function checkmate($key, $args=[])
    {
        $options = get_option($this->_option_name);
        $key = esc_attr($key);
        ?>
        <input type="checkbox" id="<?php echo $key; ?>" name="<?php echo $this->_option_name; ?>[<?php echo $key; ?>]" <?php (isset($options[$key]) ? checked(1, $options[$key]) : ''); ?> value="1" />
            <?php
    }

    /**
     * TextInput
     *
     * @param string $key option key
     *
     * @return html
     */
    public function textInput($key)
    {
        $options = get_option($this->_option_name);
        $key = esc_attr($key);
        $value = isset($options[$key]) ? esc_attr($options[$key]) : '';
        ?>
        <input type="text" id="<?php echo $key; ?>" name="<?php echo $this->_option_name; ?>[<?php echo $key; ?>]" value="<?php echo $value; ?>">
        <?php
    }

    /**
     * Numeric Input
     *
     * @param string $key  option key
     * @param array  $args args
     *
     * @return html
     */
    public function numberInput($key, $args)
    {
        $options = get_option($this->_option_name);
        $key = esc_attr($key);
        // <span class="symbol-wrap"><span class="symbol-code">s</span></span>
        if (isset($args['help'])) { ?>
        <span data-text="<?php echo esc_attr($args['help']); ?>" class="tooltip">i</span> 
        <?php } ?>
        <input type="number" id="<?php echo $key; ?>" name="<?php echo $this->_option_name; ?>[<?php echo $key; ?>]" value="<?php echo isset($options[$key]) ? (int)$options[$key] : $args['default']; ?>" size="6" class="symbol" />
        <?php if (isset($args['summary'])) { ?>
        <span class="summary"><?php echo esc_html($args['summary']); ?></span>
            <?php
        }
    }

    /**
     * DropDown / Select
     *
     * @param string $key   option key
     * @param array  $items items
     *
     * @return html
     */
    public function dropDown($key, $items)
    {
        $options = get_option($this->_option_name);
        $key = esc_attr($key);
        echo '<select id="' . $key . '" name="' . $this->_option_name . '[' . $key . ']">';
        foreach ($items as $k => $v) {
            printf(
                '<option value="%1$s" %3$s>%2$s</option>',
                esc_attr($k),
                esc_html($v),
                isset($options[$key]) ? selected($options[$key], esc_attr($k), false) : ''
            );
        }
        echo '</select>';
    }

    /**
     * Multiple Select
     *
     * @param string $key   option key
     * @param array  $items items
     *
     * @return html
     */
    public function selectMultiple($key, $items)
    {
        $options = get_option($this->_option_name);
        $key = esc_attr($key);
        ?>
        <select id="<?php echo $key; ?>" name="<?php echo $this->_option_name; ?>[<?php echo $key; ?>][]" multiple>
        <?php foreach ($items as $k => $v) {
            ?>
        <option value="<?php echo esc_attr($k); ?>" <?php selected(in_array($k, $options[$key] ?? array())); ?>><?php echo esc_html($v); ?></option>
        <?php } ?>
        </select>
        <?php
    }

    /**
     * TextArea
     *
     * @param string $key   option key
     * @param string $class class
     * @param string $help  help text
     *
     * @return html
     */
    public function textArea($key, $class = '', $help = '')
    {
        $options = get_option($this->_option_name);
        $class = sanitize_html_class($class);
        $help = esc_attr($help);
        ?>
        <?php if (!empty($help)) { ?>
            <span data-text="<?php echo $help; ?>" class="tooltip">i</span> 
        <?php } ?>
        <textarea id="<?php echo esc_attr($key); ?>" 
                name="<?php echo $this->_option_name; ?>[<?php echo esc_attr($key); ?>]" 
                rows="7" 
                cols="50" 
                type="textarea" 
                class="<?php echo $class; ?>"><?php echo isset($options[$key]) ? esc_textarea(trim($options[$key])) : ''; ?></textarea>
        <?php
    }

    /**
     * Renders a color picker field.
     *
     * @param string $key   The unique key for the field.
     * @param string $label The label for the field.
     *
     * @return void
     */
    public function colorField($key, $label)
    {
        $options = get_option($this->_option_name);
        $key = esc_attr($key);
        $value = isset($options[$key]) ? $options[$key] : '';
        ?>
        <div class="color-field">
            <label for="<?php echo $key; ?>"><?php echo esc_html($label); ?></label>
            <input type="text" id="<?php echo $key; ?>" name="<?php echo $this->_option_name; ?>[<?php echo $key; ?>]" value="<?php echo esc_attr($value); ?>" class="colorpicker" />
        </div>
        <?php
    }

    /**
     * File upload field
     *
     * @param string $key The unique key for the field.
     *
     * @return void
     */
    public function fileUploadField($key)
    {
        $options = get_option($this->_option_name);
        $key = esc_attr($key);
        $value = isset($options[$key]) ? esc_url_raw($options[$key]) : '';
        wp_enqueue_media();
        ?>
        <input type="text" id="<?php echo $key; ?>" name="<?php echo $this->_option_name; ?>[<?php echo $key; ?>]" value="<?php echo $value; ?>" class="regular-text" readonly />
        <input type="button" name="upload-btn" id="upload-btn-<?php echo $key; ?>" class="button-secondary" value="<?php esc_attr_e('Upload File', 'flashspeed'); ?>" />

        <?php if (isset($options[$key]) && wp_attachment_is_image($options[$key])) { ?>
            <div class="image-preview">
                <img src="<?php echo esc_url($options[$key]); ?>" alt="<?php esc_attr_e('Thumbnail', 'flashspeed'); ?>" />
                <a href="#" class="remove-image"><?php esc_html_e('Remove Image', 'flashspeed'); ?></a>
            </div>
        <?php } ?>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#upload-btn-<?php echo $key; ?>').click(function(e) {
                    e.preventDefault();
                    var file_frame = wp.media({
                        title: '<?php esc_attr_e('Choose or Upload a File', 'flashspeed'); ?>',
                        button: {
                            text: '<?php esc_attr_e('Use this file', 'flashspeed'); ?>'
                        },
                        multiple: false
                    });
                    file_frame.on('select', function() {
                        var attachment = file_frame.state().get('selection').first().toJSON();
                        $('#<?php echo $key; ?>').val(attachment.url);
                        $('.image-preview').remove();
                        if (wp_attachment_is_image(attachment.id)) {
                            var preview = '<div class="image-preview"><img src="' + attachment.url + '" alt="<?php esc_attr_e('Thumbnail', 'flashspeed'); ?>" /><a href="#" class="remove-image"><?php esc_html_e('Remove Image', 'flashspeed'); ?></a></div>';
                            $('#<?php echo $key; ?>').after(preview);
                        }
                    });
                    file_frame.open();
                });

                $(document).on('click', '.remove-image', function(e) {
                    e.preventDefault();
                    $(this).parent('.image-preview').remove();
                    $('#<?php echo $key; ?>').val('');
                });
            });
        </script>
            <?php
    }
}
