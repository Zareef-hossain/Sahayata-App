<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base class for all controllers. Gives every controller short helpers
 * for showing a page, sending JSON and redirecting.
 */
abstract class Controller
{
    public function __construct(protected Request $request)
    {
    }

    /** Show a page from app/Views (wrapped in the main layout). */
    protected function view(string $view, array $data = []): void
    {
        echo View::render($view, [
            'flash' => Session::pullFlash(),
            ...Session::pullInput(), // gives the view $old and $errors
            ...$data,
        ]);
    }

    protected function json(array $data = [], string $message = '', int $status = 200): never
    {
        Response::json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    protected function jsonError(string $message, array $errors = [], int $status = 422): never
    {
        Response::json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
    }

    protected function redirect(string $path): never
    {
        Response::redirect($path);
    }

    /** Go back to a form with an error message and the values the user typed. */
    protected function backWithErrors(string $path, array $errors, string $message = 'Please fix the highlighted fields.'): never
    {
        Session::flashInput($this->request->all(), $errors);
        Session::flash('error', $message);
        Response::redirect($path);
    }
}
