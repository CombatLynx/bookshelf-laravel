<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Library\BorrowBookHandler;
use App\Application\Library\RenewBookHandler;
use App\Application\Library\ReturnBookHandler;
use App\Domain\Library\Exception\BookNotFound;
use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookLoanController extends Controller
{
    public function borrow(string $id, Request $request, BorrowBookHandler $handler): JsonResponse
    {
        $user = $request->user();

        return $this->run(function () use ($handler, $id, $user) {
            $handler->handle($id, (string) $user->id, (string) $user->name);
        }, 'Книга выдана на две недели.');
    }

    public function renew(string $id, Request $request, RenewBookHandler $handler): JsonResponse
    {
        $user = $request->user();

        return $this->run(function () use ($handler, $id, $user) {
            $handler->handle($id, (string) $user->id);
        }, 'Срок выдачи продлён на две недели.');
    }

    public function returnBook(string $id, Request $request, ReturnBookHandler $handler): JsonResponse
    {
        $user = $request->user();

        return $this->run(function () use ($handler, $id, $user) {
            $handler->handle($id, (string) $user->id);
        }, 'Книга возвращена на полку.');
    }

    /**
     * @param callable(): void $action
     */
    private function run(callable $action, string $success): JsonResponse
    {
        try {
            $action();
        } catch (BookNotFound $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        } catch (DomainException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['message' => $success]);
    }
}
