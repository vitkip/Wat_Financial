<?php
/**
 * App — Front Router
 * Parses URL → Controller → Method → Params
 */
class App
{
    protected string $controller = 'DashboardController';
    protected string $method     = 'index';
    protected array  $params     = [];

    public function __construct()
    {
        $url = $this->parseUrl();

        // Controller
        if (!empty($url[0])) {
            $name = ucfirst($url[0]) . 'Controller';
            $file = BASE_PATH . '/app/controllers/' . $name . '.php';
            if (file_exists($file)) {
                require_once $file;
                $this->controller = $name;
            } else {
                $this->notFound();
                return;
            }
            unset($url[0]);
        }

        $controller = new $this->controller();

        // Method
        if (!empty($url[1])) {
            if (method_exists($controller, $url[1])) {
                $this->method = $url[1];
            } else {
                $this->notFound();
                return;
            }
            unset($url[1]);
        }

        // Params
        $this->params = $url ? array_values($url) : [];

        call_user_func_array([$controller, $this->method], $this->params);
    }

    private function parseUrl(): array
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(
                rtrim($_GET['url'], '/'),
                FILTER_SANITIZE_URL
            ));
        }
        return [];
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '<h1>404 — Page Not Found</h1>';
    }
}
