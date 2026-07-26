<?php

namespace App\Core;

// Base Controller — provides helper methods shared by all controllers.
abstract class Controller
{

    // render a view
    protected function renderPlain(string $view, array $data = []): void
    {
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "View not found: {$view}";
            return;
        }

        extract($data);
        require $viewPath;
    }

    // send a json response (api style)
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // redirect to another url (automatically prepends BASE_URL for subfolder support)
    protected function redirect(string $url): void
    {
        // if the URL starts with /, prepend the base URL for subfolder installations
        if ($url !== '' && $url[0] === '/') {
            $url = BASE_URL . $url;
        }
        header("Location: {$url}");
        exit;
    }
}