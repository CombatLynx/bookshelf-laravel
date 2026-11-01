<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Library\BorrowBookHandler;
use App\Application\Library\Exception\DuplicateIsbn;
use App\Application\Library\ListBooksHandler;
use App\Application\Library\RegisterBookHandler;
use App\Application\Library\RenewBookHandler;
use App\Application\Library\ReturnBookHandler;
use App\Domain\Library\Exception\InvalidIsbn;
use App\Domain\Library\ReaderId;
use App\Http\Requests\RegisterBookRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request, ListBooksHandler $listBooks): View
    {
        $user = $request->user();

        return view('books.index', [
            'books' => $listBooks->handle($user === null ? null : ReaderId::fromInt((int) $user->id)),
        ]);
    }

    public function store(RegisterBookRequest $request, RegisterBookHandler $handler): RedirectResponse
    {
        try {
            $handler->handle(
                (string) $request->input('title'),
                (string) $request->input('author'),
                (string) $request->input('isbn')
            );
        } catch (InvalidIsbn $exception) {
            return $this->backToForm(['isbn' => $exception->getMessage()]);
        } catch (DuplicateIsbn $exception) {
            return $this->backToForm(['isbn' => $exception->getMessage()]);
        } catch (DomainException $exception) {
            return $this->backToForm(['title' => $exception->getMessage()]);
        }

        return redirect()
            ->route('books.index')
            ->with('status', 'Книга добавлена на полку.');
    }

    public function borrow(string $id, Request $request, BorrowBookHandler $handler): RedirectResponse
    {
        $user = $request->user();

        return $this->changeAvailability($id, function () use ($handler, $id, $user) {
            $handler->handle($id, (string) $user->id, (string) $user->name);
        }, 'Книга выдана на две недели.');
    }

    public function renew(string $id, Request $request, RenewBookHandler $handler): RedirectResponse
    {
        $user = $request->user();

        return $this->changeAvailability($id, function () use ($handler, $id, $user) {
            $handler->handle($id, (string) $user->id);
        }, 'Срок выдачи продлён на две недели.');
    }

    public function returnBook(string $id, Request $request, ReturnBookHandler $handler): RedirectResponse
    {
        $user = $request->user();

        return $this->changeAvailability($id, function () use ($handler, $id, $user) {
            $handler->handle($id, (string) $user->id);
        }, 'Книга возвращена на полку.');
    }

    /**
     * @param callable(): void $action
     */
    private function changeAvailability(string $id, callable $action, string $success): RedirectResponse
    {
        try {
            $action();
        } catch (DomainException $exception) {
            return redirect()
                ->route('books.index')
                ->withErrors(['book' => $exception->getMessage()]);
        }

        return redirect()->route('books.index')->with('status', $success);
    }

    /**
     * @param array<string, string> $errors
     */
    private function backToForm(array $errors): RedirectResponse
    {
        return redirect()
            ->route('books.index')
            ->withInput()
            ->withErrors($errors);
    }
}
