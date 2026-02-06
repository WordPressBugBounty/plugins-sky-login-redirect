/**
 * Sky Login Redirect - AJAX Select2 Integration
 *
 * @package Sky_Login_Redirect
 */

(function($) {
    'use strict';

    if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
        console.warn('Select2 not loaded, AJAX selects will not work');
        return;
    }

    $(document).ready(function() {

        // Initialize AJAX page selectors
        $('.slr-ajax-page-select').each(function() {
            var $select = $(this);
            var postType = $select.data('post-type') || 'page';
            var currentValue = $select.val();
            var currentText = $select.find('option:selected').text();

            $select.select2({
                ajax: {
                    url: ajaxurl,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            action: 'slr_search_pages',
                            nonce: slrAjax.nonce,
                            search: params.term,
                            page: params.page || 1,
                            post_type: postType
                        };
                    },
                    processResults: function(data) {
                        if (data.success) {
                            return {
                                results: data.data.results,
                                pagination: data.data.pagination
                            };
                        }
                        return { results: [] };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                placeholder: slrAjax.i18n.selectPage || 'Select a page...',
                allowClear: true,
                width: '100%'
            });

            // Preserve current selection
            if (currentValue && currentText) {
                var option = new Option(currentText, currentValue, true, true);
                $select.append(option).trigger('change');
            }
        });

        // Initialize AJAX user selectors
        $('.slr-ajax-user-select').each(function() {
            var $select = $(this);
            var currentValues = $select.val() || [];

            $select.select2({
                ajax: {
                    url: ajaxurl,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            action: 'slr_search_users',
                            nonce: slrAjax.nonce,
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data) {
                        if (data.success) {
                            return {
                                results: data.data.results,
                                pagination: data.data.pagination
                            };
                        }
                        return { results: [] };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                placeholder: slrAjax.i18n.selectUser || 'Select users...',
                allowClear: true,
                multiple: true,
                width: '100%'
            });

            // Preserve current selections
            if (currentValues.length > 0) {
                $select.find('option:selected').each(function() {
                    var option = new Option($(this).text(), $(this).val(), true, true);
                    $select.append(option);
                });
                $select.trigger('change');
            }
        });
    });

})(jQuery);
