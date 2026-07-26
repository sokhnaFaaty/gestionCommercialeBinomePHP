<?php
namespace App\Core;

 class Controller {
    // Render a view and pass data to it
    protected function view($view, $data = []) {
        // Extract data to make variables available in the view
        extract($data);
        
        $viewFile = __DIR__ . '/../../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View '{$view}' not found.");
        }
    }

    // Redirect to a specific URL
    protected function redirect($url) {
        header("Location: " . BASE_URL . $url);
        exit;
    }
}
