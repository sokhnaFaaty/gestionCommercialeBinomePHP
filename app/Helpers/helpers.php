<?php

function dd($test): void {
    echo "<pre>";
    var_dump($test);
    echo "</pre>";
    die();
}

function loadView(string $view, array $datas = [], string $layout = "base") {
    ob_start();
    extract($datas);
    require(ROOT . "views/" . $view . ".php");
    $content = ob_get_clean();
    require(ROOT . "views/layouts/" . $layout . ".layout.php");
}

function path(string $controller, string $action, array $params = []): string {
    $url = WEBROOT . $controller . '/' . $action;
    if ($params) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
}

function redirectTo(string $controller, string $action, array $params = []): void {
    $url = WEBROOT . "$controller/$action";
    if ($params) {
        $url .= '?' . http_build_query($params);
    }
    header('Location:' . $url);
    exit();
}

function isConnected(): bool {
    return isset($_SESSION["user"]);
}

function auth(): void {
    if (!isConnected()) {
        redirectTo("auth", "login");
    }
}

function hasRole(string $role): bool {
    if (!isConnected()) return false;
    return $_SESSION["user"]["role"] === $role;
}
