<?php
/**
 * Gestor de almacenamiento multi-cliente local.
 * Archivo: class-client-storage-manager.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_Client_Storage_Manager {

    /**
     * Clave para almacenar el cliente activo en la sesiÃ³n o cookie (admin_tenant).
     */
    const COOKIE_NAME = 'euno_admin_active_client';

    public static function init() {
        // Interceptar la ruta de subida
        add_filter( 'upload_dir', array( __CLASS__, 'filter_upload_dir' ) );

        // Etiquetar archivos subidos
        add_action( 'add_attachment', array( __CLASS__, 'tag_attachment_with_client' ) );

        // AÃ±adir UI en la barra de administraciÃ³n (Solo para Admins)
        add_action( 'admin_bar_menu', array( __CLASS__, 'add_admin_bar_switcher' ), 100 );

        // Procesar cambio de cliente desde el switcher
        add_action( 'admin_init', array( __CLASS__, 'process_client_switch' ) );

        // Mostrar el cliente activo en la página de medios
        add_filter( 'manage_media_columns', array( __CLASS__, 'add_client_column' ) );
        add_action( 'manage_media_custom_column', array( __CLASS__, 'render_client_column' ), 10, 2 );

        // Filtrar la biblioteca de medios por cliente (Grid y Lista)
        add_filter( 'ajax_query_attachments_args', array( __CLASS__, 'filter_attachments_query' ) );
        add_action( 'pre_get_posts', array( __CLASS__, 'filter_media_library_list' ) );

        // Eliminar archivos físicos y adjuntos de la DB al borrar un cliente (Grupo Padre)
        add_action( 'before_delete_post', array( __CLASS__, 'delete_client_storage_on_group_delete' ) );
    }

    /**
     * Elimina todos los archivos físicos y de la base de datos asociados a un cliente
     * cuando su grupo (Grupo Padre de LearnDash) es eliminado.
     */
    public static function delete_client_storage_on_group_delete( $post_id ) {
        // Verificar si el post que se está eliminando es un grupo de LearnDash
        if ( get_post_type( $post_id ) !== 'groups' ) {
            return;
        }

        // 1. Obtener todos los adjuntos vinculados a este cliente
        global $wpdb;
        $attachments = $wpdb->get_col( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_euno_client_id' AND meta_value = %d",
            $post_id
        ));

        // 2. Borrar cada adjunto (wp_delete_attachment borra el registro de la DB y el archivo físico)
        if ( ! empty( $attachments ) ) {
            foreach ( $attachments as $att_id ) {
                wp_delete_attachment( $att_id, true ); // true = force delete (evita la papelera)
            }
        }

        // 3. Borrar el directorio físico completo del cliente por si quedaron archivos huérfanos
        $client_slug = get_post_field( 'post_name', $post_id );
        if ( ! empty( $client_slug ) ) {
            // Desactivar temporalmente nuestro filtro para obtener la ruta base real
            remove_filter( 'upload_dir', array( __CLASS__, 'filter_upload_dir' ) );
            $upload_dir = wp_upload_dir();
            add_filter( 'upload_dir', array( __CLASS__, 'filter_upload_dir' ) );
            
            $client_dir = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'clientes' . DIRECTORY_SEPARATOR . $client_slug;
            
            if ( is_dir( $client_dir ) ) {
                self::delete_directory_recursively( $client_dir );
            }
        }
    }

    /**
     * Función auxiliar para borrar un directorio recursivamente.
     */
    private static function delete_directory_recursively( $dir ) {
        if ( ! file_exists( $dir ) ) {
            return true;
        }

        if ( ! is_dir( $dir ) ) {
            return unlink( $dir );
        }

        foreach ( scandir( $dir ) as $item ) {
            if ( $item == '.' || $item == '..' ) {
                continue;
            }

            if ( ! self::delete_directory_recursively( $dir . DIRECTORY_SEPARATOR . $item ) ) {
                return false;
            }
        }

        return rmdir( $dir );
    }

    /**
     * Filtra los adjuntos en la vista de cuadrícula (AJAX) para mostrar solo los del cliente activo.
     */
    public static function filter_attachments_query( $query ) {
        // No aplicar si no tiene permisos de ver medios
        if ( ! current_user_can( 'upload_files' ) ) {
            return $query;
        }

        $client_id = self::get_current_client_id();
        $meta_query = isset( $query['meta_query'] ) ? $query['meta_query'] : array();

        if ( $client_id ) {
            // Mostrar solo los de este cliente
            $meta_query[] = array(
                'key'     => '_euno_client_id',
                'value'   => $client_id,
                'compare' => '='
            );
        } else {
            // Si es Global, no mostrar los que pertenecen a un cliente
            $meta_query[] = array(
                'key'     => '_euno_client_id',
                'compare' => 'NOT EXISTS'
            );
        }

        $query['meta_query'] = $meta_query;
        return $query;
    }

    /**
     * Filtra los adjuntos en la vista de lista (WP_Admin) para mostrar solo los del cliente activo.
     */
    public static function filter_media_library_list( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() || $query->get( 'post_type' ) !== 'attachment' ) {
            return;
        }

        global $pagenow;
        if ( $pagenow !== 'upload.php' ) {
            return;
        }

        $client_id = self::get_current_client_id();
        $meta_query = $query->get( 'meta_query' );
        if ( ! is_array( $meta_query ) ) {
            $meta_query = array();
        }

        if ( $client_id ) {
            $meta_query[] = array(
                'key'     => '_euno_client_id',
                'value'   => $client_id,
                'compare' => '='
            );
        } else {
            $meta_query[] = array(
                'key'     => '_euno_client_id',
                'compare' => 'NOT EXISTS'
            );
        }

        $query->set( 'meta_query', $meta_query );
    }

    /**
     * Determina el ID del cliente (Grupo Padre) actual.
     * Si es Admin, devuelve lo que estÃ© en la cookie.
     * Si es usuario normal, devuelve el ID de su Grupo Padre en LearnDash.
     */
    public static function get_current_client_id() {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $user = wp_get_current_user();
        
        // Si es Admin, leemos el switcher (cookie)
        if ( in_array( 'administrator', (array) $user->roles ) ) {
            if ( isset( $_COOKIE[self::COOKIE_NAME] ) && is_numeric( $_COOKIE[self::COOKIE_NAME] ) ) {
                $client_id = intval( $_COOKIE[self::COOKIE_NAME] );
                // Opcional: Validar que el $client_id realmente existe y es un grupo de LearnDash
                if ( $client_id > 0 && get_post_type( $client_id ) === 'groups' ) {
                    return $client_id;
                }
            }
            return false; // Global
        }

        // Si es usuario normal, buscamos su Grupo Padre
        if ( function_exists( 'learndash_get_users_group_ids' ) ) {
            $user_groups = learndash_get_users_group_ids( $user->ID );
            if ( ! empty( $user_groups ) ) {
                // Iterar para encontrar el Top Level Group (padre = 0)
                foreach ( $user_groups as $group_id ) {
                    $parent_id = wp_get_post_parent_id( $group_id );
                    if ( $parent_id == 0 ) {
                        return $group_id; // Es un grupo padre
                    }
                    // Si el grupo tiene padre, seguimos subiendo hasta la raÃ­z si fuera necesario,
                    // pero asumiendo una estructura de dos niveles, el padre es el cliente.
                    while ( $parent_id > 0 ) {
                        $top_level = $parent_id;
                        $parent_id = wp_get_post_parent_id( $parent_id );
                        if ( $parent_id == 0 ) {
                            return $top_level;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Filtra las rutas de subida para redirigirlas a la carpeta del cliente.
     */
    public static function filter_upload_dir( $uploads ) {
        $client_id = self::get_current_client_id();

        if ( $client_id ) {
            $client_slug = get_post_field( 'post_name', $client_id );
            
            if ( ! empty( $client_slug ) ) {
                $custom_dir = '/clientes/' . $client_slug;

                // Modificamos las rutas
                $uploads['path']    = str_replace( $uploads['subdir'], $custom_dir . $uploads['subdir'], $uploads['path'] );
                $uploads['url']     = str_replace( $uploads['subdir'], $custom_dir . $uploads['subdir'], $uploads['url'] );
                $uploads['subdir']  = $custom_dir . $uploads['subdir'];
                $uploads['basedir'] = $uploads['basedir'] . '/clientes/' . $client_slug;
                $uploads['baseurl'] = $uploads['baseurl'] . '/clientes/' . $client_slug;
            }
        } else {
            // Opcional: Aislar los archivos "Globales" (no asignados a un cliente) en una carpeta especial
            /*
            $custom_dir = '/global';
            $uploads['path']    = str_replace( $uploads['subdir'], $custom_dir . $uploads['subdir'], $uploads['path'] );
            $uploads['url']     = str_replace( $uploads['subdir'], $custom_dir . $uploads['subdir'], $uploads['url'] );
            $uploads['subdir']  = $custom_dir . $uploads['subdir'];
            */
        }

        return $uploads;
    }

    /**
     * Guarda el ID del cliente como metadato del archivo al subirlo.
     */
    public static function tag_attachment_with_client( $post_id ) {
        $client_id = self::get_current_client_id();
        if ( $client_id ) {
            update_post_meta( $post_id, '_euno_client_id', $client_id );
        }
    }

    /**
     * AÃ±ade el Switcher a la Admin Bar.
     */
    public static function add_admin_bar_switcher( $wp_admin_bar ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $active_client_id = self::get_current_client_id();
        $active_client_name = 'Root';

        if ( $active_client_id ) {
            $active_client_name = get_the_title( $active_client_id );
        }

        // Menú principal
        $wp_admin_bar->add_node( array(
            'id'    => 'euno-client-switcher',
            'title' => '<span class="ab-icon dashicons dashicons-groups"></span> Cliente: ' . esc_html( $active_client_name ),
            'href'  => '#',
            'meta'  => array(
                'class' => 'euno-admin-bar-highlight'
            )
        ));

        // Opción Global
        $wp_admin_bar->add_node( array(
            'id'     => 'euno-client-global',
            'parent' => 'euno-client-switcher',
            'title'  => '📂 Root',
            'href'   => esc_url( add_query_arg( array( 'action' => 'switch_client', 'client_id' => 0 ) ) ),
        ));

        // Obtener todos los grupos padre (clientes)
        $args = array(
            'post_type'      => 'groups',
            'posts_per_page' => -1,
            'post_parent'    => 0, // Solo grupos top-level
            'orderby'        => 'title',
            'order'          => 'ASC'
        );

        $clients = get_posts( $args );

        foreach ( $clients as $client ) {
            $is_active = ( $active_client_id == $client->ID ) ? ' (Activo)' : '';
            $wp_admin_bar->add_node( array(
                'id'     => 'euno-client-' . $client->ID,
                'parent' => 'euno-client-switcher',
                'title'  => esc_html( $client->post_title ) . $is_active,
                'href'   => esc_url( add_query_arg( array( 'action' => 'switch_client', 'client_id' => $client->ID ) ) ),
            ));
        }

        // Estilos para destacar
        echo '<style>
            #wpadminbar .euno-admin-bar-highlight > a { color: #00d084 !important; font-weight: bold; }
        </style>';
    }

    /**
     * Procesa la solicitud para cambiar el cliente activo.
     */
    public static function process_client_switch() {
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'switch_client' && isset( $_GET['client_id'] ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                $client_id = intval( $_GET['client_id'] );
                setcookie( self::COOKIE_NAME, $client_id, time() + (86400 * 30), COOKIEPATH, COOKIE_DOMAIN ); // 30 dÃ­as
                
                // Redirigir limpiando la URL
                $redirect_url = remove_query_arg( array( 'action', 'client_id' ) );
                wp_redirect( $redirect_url );
                exit;
            }
        }
    }

    /**
     * AÃ±ade la columna "Cliente" a la tabla de Medios.
     */
    public static function add_client_column( $columns ) {
        $columns['euno_client'] = 'Cliente / Tenant';
        return $columns;
    }

    /**
     * Renderiza el contenido de la columna "Cliente" en la tabla de Medios.
     */
    public static function render_client_column( $column_name, $post_id ) {
        if ( 'euno_client' === $column_name ) {
            $client_id = get_post_meta( $post_id, '_euno_client_id', true );
            if ( $client_id ) {
                echo esc_html( get_the_title( $client_id ) );
            } else {
                echo '<em>Global</em>';
            }
        }
    }

    /**
     * Obtiene el total de almacenamiento usado por un cliente (Sumando el metadato de tamaÃ±o de archivo).
     * Devuelve el valor en Bytes.
     */
    public static function get_client_storage_usage_bytes( $client_id ) {
        global $wpdb;

        // Buscamos todos los adjuntos que pertenecen a este cliente
        $attachments = $wpdb->get_col( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_euno_client_id' AND meta_value = %d",
            $client_id
        ));

        if ( empty( $attachments ) ) {
            return 0;
        }

        $total_bytes = 0;

        // Sumamos el tamaÃ±o de cada uno (leyendo de _wp_attachment_metadata)
        foreach ( $attachments as $att_id ) {
            $meta = wp_get_attachment_metadata( $att_id );
            if ( isset( $meta['filesize'] ) ) {
                $total_bytes += $meta['filesize'];
            } else {
                // Si por alguna razÃ³n no estÃ¡ 'filesize', intentamos leer el archivo real (pesado)
                $file = get_attached_file( $att_id );
                if ( file_exists( $file ) ) {
                    $total_bytes += filesize( $file );
                }
            }
        }

        return $total_bytes;
    }
}
// Fin del gestor de almacenamiento.
