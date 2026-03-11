jQuery(document).ready(function($) {
    'use strict';

    // Manejo de subida de imagen (isotipo)
    $('#euno_upload_isotype').on('click', function(e) {
        e.preventDefault();
        
        var fileFrame = wp.media({
            title: eunoBrandingMetaBox.title,
            button: {
                text: eunoBrandingMetaBox.button
            },
            multiple: false
        });

        fileFrame.on('select', function() {
            var attachment = fileFrame.state().get('selection').first().toJSON();
            
            // Actualizar la URL en el input hidden
            $('#euno_isotype_url').val(attachment.url);
            
            // Actualizar el texto del input
            $('#euno_isotype_preview').val(attachment.url);
            
            // Mostrar previsualización
            $('.euno-image-preview').html('<img src="' + attachment.url + '" alt="Previsualización">');
            
            // Mostrar botón de eliminar
            $('#euno_remove_isotype').show();
        });

        fileFrame.open();
    });

    // Manejo de eliminación de imagen
    $('#euno_remove_isotype').on('click', function(e) {
        e.preventDefault();
        
        // Limpiar el input
        $('#euno_isotype_url').val('');
        $('#euno_isotype_preview').val('');
        
        // Ocultar previsualización
        $('.euno-image-preview').html('');
        
        // Ocultar botón de eliminar
        $(this).hide();
    });

    // Actualizar el valor del color cuando se selecciona
    $('.euno-color-field input[type="color"]').on('input change', function() {
        var color = $(this).val();
        $(this).siblings('.euno-color-value').text(color);
    });

    // Color picker de WP no muestra el valor, así que inicializamos
    $('.euno-color-field input[type="color"]').each(function() {
        var color = $(this).val();
        $(this).siblings('.euno-color-value').text(color);
    });
});