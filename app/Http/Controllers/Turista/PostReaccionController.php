<?php

namespace App\Http\Controllers\Turista;

use App\Enums\EmprendedorPostReaccionTipo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Turista\StorePostReaccionRequest;
use App\Models\EmprendedorPost;
use App\Services\PostReaccionService;
use Illuminate\Http\JsonResponse;

class PostReaccionController extends Controller
{
    public function store(
        StorePostReaccionRequest $request,
        EmprendedorPost $post,
        PostReaccionService $reaccionService,
    ): JsonResponse {
        $tipo = EmprendedorPostReaccionTipo::from($request->validated('tipo'));

        $payload = $reaccionService->alternar($request, $post, $tipo);

        return response()->json($payload);
    }
}
