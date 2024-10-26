<?php
namespace App\Controllers;

class ErrorController extends BaseController {
    public function notFound() {
        http_response_code(404);
        $this->render('errors/404', [], '404 - Page Not Found');
    }

    public function unauthorized() {
        http_response_code(403);
        $this->render('errors/403', [], '403 - Unauthorized');
    }

    public function serverError($error = null) {
        http_response_code(500);
        $this->render('errors/500', [
            'error' => $error
        ], '500 - Server Error');
    }
}