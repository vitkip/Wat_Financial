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

        // Method — ອະນຸຍາດສະເພາະ methods ທີ່ declared ໃນ controller class ນັ້ນເທົ່ານັ້ນ
        // (ບໍ່ include inherited methods ຈາກ base Controller ທີ່ອາດຖືກ exploit ຜ່ານ URL)
        if (!empty($url[1])) {
            $methodName = $url[1];
            if (method_exists($controller, $methodName)) {
                try {
                    $ref = new ReflectionMethod($controller, $methodName);
                    $declaredIn = $ref->getDeclaringClass()->getName();
                    if ($declaredIn === get_class($controller) && $ref->isPublic()) {
                        $this->method = $methodName;
                    } else {
                        $this->notFound();
                        return;
                    }
                } catch (ReflectionException $e) {
                    $this->notFound();
                    return;
                }
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
