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

// Página de administración del plugin
function ang_admin_page() {
    ?>
    <div class="wrap">
        <h1>Auto News Generator</h1>
        <form method="post" action="">
            <label for="keyword">Palabra clave:</label>
            <input type="text" id="keyword" name="keyword" required><br><br>
            <label for="publish_date">Fecha de publicación:</label>
            <input type="datetime-local" id="publish_date" name="publish_date"><br><br>
            <button type="submit" class="button button-primary">Generar Publicación</button>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['keyword'])) {
            $keyword = sanitize_text_field($_POST['keyword']);
            $publish_date = sanitize_text_field($_POST['publish_date']);
            $post_content = ang_generate_post_content($keyword);
            if ($post_content) {
                ang_create_post($keyword, $post_content, $publish_date);
            }
        }
        ?>
    </div>
    <?php
}

// Función para crear una nueva publicación con fecha programada
function ang_create_post($title, $content, $publish_date = null) {
    $post_data = array(
        'post_title'   => wp_strip_all_tags($title),
        'post_content' => $content,
        'post_status'  => $publish_date ? 'future' : 'publish',
        'post_author'  => get_current_user_id(),
        'post_category' => array(1), // Categoría predeterminada
        'post_date'    => $publish_date ? date('Y-m-d H:i:s', strtotime($publish_date)) : current_time('mysql')
    );

    wp_insert_post($post_data);
}

// Función para obtener una imagen relacionada con la palabra clave
function ang_get_related_image($keyword) {
    // Aquí se integraría la API real para obtener imágenes relacionadas
    // Para la demostración, usamos una imagen predefinida
    $image_url = 'https://via.placeholder.com/800x400?text=' . urlencode($keyword);
    return $image_url;
}

// Modificar la función para crear una nueva publicación para incluir una imagen
function ang_create_post($title, $content, $publish_date = null) {
    $image_url = ang_get_related_image($title);
    $content .= '<br><img src="' . esc_url($image_url) . '" alt="' . esc_attr($title) . '">';
    
    $post_data = array(
        'post_title'   => wp_strip_all_tags($title),
        'post_content' => $content,
        'post_status'  => $publish_date ? 'future' : 'publish',
        'post_author'  => get_current_user_id(),
        'post_category' => array(1), // Categoría predeterminada
        'post_date'    => $publish_date ? date('Y-m-d H:i:s', strtotime($publish_date)) : current_time('mysql')
    );

    wp_insert_post($post_data);
}

// Página de administración del plugin
function ang_admin_page() {
    ?>
    <div class="wrap">
        <h1>Auto News Generator</h1>
        <form method="post" action="">
            <label for="keyword">Palabra clave:</label>
            <input type="text" id="keyword" name="keyword" required><br><br>
            <label for="publish_date">Fecha de publicación:</label>
            <input type="datetime-local" id="publish_date" name="publish_date"><br><br>
            <label for="categories">Categorías:</label>
            <input type="text" id="categories" name="categories"><br><br>
            <label for="tags">Etiquetas:</label>
            <input type="text" id="tags" name="tags"><br><br>
            <button type="submit" class="button button-primary">Generar Publicación</button>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['keyword'])) {
            $keyword = sanitize_text_field($_POST['keyword']);
            $publish_date = sanitize_text_field($_POST['publish_date']);
            $categories = sanitize_text_field($_POST['categories']);
            $tags = sanitize_text_field($_POST['tags']);
            $post_content = ang_generate_post_content($keyword);
            if ($post_content) {
                ang_create_post($keyword, $post_content, $publish_date, $categories, $tags);
            }
        }
        ?>
    </div>
    <?php
}

// Modificar la función para crear una nueva publicación con categorías y etiquetas
function ang_create_post($title, $content, $publish_date = null, $categories = '', $tags = '') {
    $image_url = ang_get_related_image($title);
    $content .= '<br><img src="' . esc_url($image_url) . '" alt="' . esc_attr($title) . '">';
    
    $category_ids = array();
    if ($categories) {
        $category_names = explode(',', $categories);
        foreach ($category_names as $category_name) {
            $category = get_category_by_slug(trim($category_name));
            if ($category) {
                $category_ids[] = $category->term_id;
            }
        }
    }

    $post_data = array(
        'post_title'   => wp_strip_all_tags($title),
        'post_content' => $content,
        'post_status'  => $publish_date ? 'future' : 'publish',
        'post_author'  => get_current_user_id(),
        'post_category' => $category_ids, 
        'post_date'    => $publish_date ? date('Y-m-d H:i:s', strtotime($publish_date)) : current_time('mysql')
    );

    $post_id = wp_insert_post($post_data);

    if ($tags) {
        $tag_names = explode(',', $tags);
        wp_set_post_tags($post_id, $tag_names);
    }
}

// Página de administración del plugin
function ang_admin_page() {
    ?>
    <div class="wrap">
        <h1>Auto News Generator</h1>
        <form method="post" action="">
            <label for="keyword">Palabra clave:</label>
            <input type="text" id="keyword" name="keyword" required><br><br>
            <label for="publish_date">Fecha de publicación:</label>
            <input type="datetime-local" id="publish_date" name="publish_date"><br><br>
            <label for="categories">Categorías:</label>
            <input type="text" id="categories" name="categories"><br><br>
            <label for="tags">Etiquetas:</label>
            <input type="text" id="tags" name="tags"><br><br>
            <label for="style">Estilo del contenido:</label>
            <select id="style" name="style">
                <option value="informative">Informativo</option>
                <option value="casual">Casual</option>
                <option value="formal">Formal</option>
            </select><br><br>
            <button type="submit" class="button button-primary">Generar Publicación</button>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['keyword'])) {
            $keyword = sanitize_text_field($_POST['keyword']);
            $publish_date = sanitize_text_field($_POST['publish_date']);
            $categories = sanitize_text_field($_POST['categories']);
            $tags = sanitize_text_field($_POST['tags']);
            $style = sanitize_text_field($_POST['style']);
            $post_content = ang_generate_post_content($keyword, $style);
            if ($post_content) {
                ang_create_post($keyword, $post_content, $publish_date, $categories, $tags);
            }
        }
        ?>
    </div>
    <?php
}

// Función para generar contenido de la publicación utilizando la API de OpenAI con estilo
function ang_generate_post_content($keyword, $style = 'informative')