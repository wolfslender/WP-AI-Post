<?php
/**
 * Plugin Name: Auto News Generator
 * Description: Genera publicaciones automáticas basadas en palabras clave utilizando la API de OpenAI.
 * Version: 1.0
 * Author: Tu Nombre
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Salir si se accede directamente
}

// Registrar el menú de administración
add_action('admin_menu', 'ang_add_admin_menu');
function ang_add_admin_menu() {
    add_menu_page('Auto News Generator', 'Auto News Generator', 'manage_options', 'auto-news-generator', 'ang_admin_page');
}

// Página de administración del plugin
function ang_admin_page() {
    ?>
    <div class="wrap">
        <h1>Auto News Generator</h1>
        <form method="post" action="">
            <label for="keyword">Palabra clave:</label>
            <input type="text" id="keyword" name="keyword" required>
            <button type="submit" class="button button-primary">Generar Publicación</button>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['keyword'])) {
            $keyword = sanitize_text_field($_POST['keyword']);
            $post_content = ang_generate_post_content($keyword);
            if ($post_content) {
                ang_create_post($keyword, $post_content);
            }
        }
        ?>
    </div>
    <?php
}

// Función para generar contenido de la publicación utilizando la API de OpenAI
function ang_generate_post_content($keyword) {
    $api_key = 'TU_API_KEY_DE_OPENAI';
    $endpoint = 'https://api.openai.com/v1/engines/davinci-codex/completions';

    $data = array(
        'prompt' => 'Escribe una noticia sobre ' . $keyword,
        'max_tokens' => 300,
        'temperature' => 0.7,
    );

    $args = array(
        'body' => json_encode($data),
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ),
    );

    $response = wp_remote_post($endpoint, $args);

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    if (isset($result['choices'][0]['text'])) {
        return trim($result['choices'][0]['text']);
    }

    return false;
}

// Función para crear una nueva publicación
function ang_create_post($title, $content) {
    $post_data = array(
        'post_title'   => wp_strip_all_tags($title),
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_author'  => get_current_user_id(),
        'post_category' => array(1) // Categoría predeterminada
    );

    wp_insert_post($post_data);
}
?>
